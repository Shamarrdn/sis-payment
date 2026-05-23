<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\ScopeService;
use Illuminate\Support\Facades\Auth;

class StudentCardController extends Controller
{
    private function authorizeStudent(Student $student): void
    {
        if (Auth::guard('student')->check()) {
            if (Auth::guard('student')->id() !== $student->id) {
                abort(403);
            }
            return;
        }

        $user = Auth::user();
        $scoped = ScopeService::scopeStudents(Student::where('id', $student->id), $user);
        if (!$scoped->exists()) {
            abort(403);
        }
    }

    public function card(Student $student)
    {
        $this->authorizeStudent($student);
        $student->load(['faculty', 'department']);

        return view('students.card', compact('student'));
    }

    public function print(Student $student)
    {
        $this->authorizeStudent($student);
        $student->load(['faculty', 'department']);

        return view('students.print-profile', compact('student'));
    }

    public function qr(Student $student)
    {
        $this->authorizeStudent($student);

        return view('students.qr', compact('student'));
    }
}
