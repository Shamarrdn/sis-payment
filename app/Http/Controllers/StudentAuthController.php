<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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
            return redirect()->intended('/student/dashboard');
        }

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
