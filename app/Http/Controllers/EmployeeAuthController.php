<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoginActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class EmployeeAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Rate Limiting: max 5 attempts per IP per minute
        $throttleKey = 'employee-login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "تم تجاوز الحد المسموح به من محاولات الدخول. يرجى الانتظار {$seconds} ثانية.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'هذا الحساب تم تعطيله. يرجى مراجعة مدير النظام.',
                ])->onlyInput('email');
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user->update(['last_login_at' => now()]);
            LoginActivityLogger::log($request, 'web', true, $user->id, null, $user->email);

            if ($user->must_change_password) {
                return redirect()->route('employee.password.change');
            }

            $role = $user->role;

            return match(true) {
                in_array($role, ['super_admin', 'admin']) => redirect()->route('admin.dashboard'),
                $role === 'financial_affairs'             => redirect()->route('affairs.financial.index'),
                $role === 'graduate_affairs'              => redirect()->route('affairs.student.index'),
                $role === 'student_affairs'               => redirect()->route('affairs.student.index'),
                default                                   => redirect('/'),
            };
        }

        LoginActivityLogger::log($request, 'web', false, null, null, $credentials['email'] ?? null);
        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
