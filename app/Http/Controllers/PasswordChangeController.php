<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function showEmployeeForm()
    {
        return view('auth.change-password', ['guard' => 'web']);
    }

    public function showStudentForm()
    {
        return view('auth.change-password', ['guard' => 'student']);
    }

    public function updateEmployee(Request $request)
    {
        return $this->update($request, 'web');
    }

    public function updateStudent(Request $request)
    {
        return $this->update($request, 'student');
    }

    private function update(Request $request, string $guard)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::guard($guard)->user();
        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->save();

        $redirect = $guard === 'student'
            ? route('student.dashboard')
            : match ($user->role) {
                'financial_affairs' => route('affairs.financial.index'),
                'student_affairs', 'graduate_affairs' => route('affairs.student.index'),
                default => route('admin.dashboard'),
            };

        return redirect($redirect)->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }
}
