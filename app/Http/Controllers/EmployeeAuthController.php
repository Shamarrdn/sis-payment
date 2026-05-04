<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $role = Auth::user()->role;

            return match(true) {
                in_array($role, ['super_admin', 'admin']) => redirect()->route('admin.dashboard'),
                $role === 'financial_affairs'             => redirect()->route('affairs.financial.index'),
                $role === 'graduate_affairs'              => redirect()->route('affairs.student.index'),
                $role === 'student_affairs'               => redirect()->route('affairs.student.index'),
                default                                   => redirect('/'),
            };
        }

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
