<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoginActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class StudentAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'national_id'      => ['required', 'string'],
            'reference_number' => ['required', 'string'],
        ]);

        // Rate Limiting: max 5 attempts per IP per minute
        $throttleKey = 'student-login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'national_id' => "لقد تجاوزت الحد المسموح به من محاولات الدخول. يرجى الانتظار {$seconds} ثانية ثم المحاولة مجدداً.",
            ])->onlyInput('national_id');
        }

        $student = \App\Models\Student::where('national_id', $credentials['national_id'])
            ->where('reference_number', $credentials['reference_number'])
            ->first();

        if ($student) {
            RateLimiter::clear($throttleKey);
            Auth::guard('student')->login($student);
            $request->session()->regenerate();

            $student->update(['last_login_at' => now()]);
            LoginActivityLogger::log($request, 'student', true, null, $student->id, $student->national_id);

            if ($student->must_change_password && $student->password) {
                return redirect()->route('student.password.change');
            }

            return redirect()->intended(route('student.dashboard'));
        }

        LoginActivityLogger::log($request, 'student', false, null, null, $credentials['national_id']);
        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'national_id' => 'الرقم القومي أو الرقم المرجعي غير صحيح. تبقى لك ' . (5 - RateLimiter::attempts($throttleKey)) . ' محاولات.',
        ])->onlyInput('national_id');
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
