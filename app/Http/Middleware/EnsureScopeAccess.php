<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureScopeAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Super Admin has global access
        if (!$user || $user->role === 'super_admin') {
            return $next($request);
        }

        $assignment = $user->assignment;

        // 2. If no assignment, user has global access (default behavior for legacy or non-scoped users)
        if (!$assignment) {
            return $next($request);
        }

        // 3. Check bound models in parameters
        $parameters = $request->route()->parameters();

        // Check Student Access
        if (isset($parameters['student']) && $parameters['student'] instanceof \App\Models\Student) {
            $student = $parameters['student'];
            if (!$this->isWithinScope($assignment, $student->faculty_id, $student->department_id)) {
                return $this->forbidden();
            }
        }

        // Check Payment Access
        if (isset($parameters['payment']) && $parameters['payment'] instanceof \App\Models\Payment) {
            $payment = $parameters['payment'];
            $student = $payment->student;
            if ($student && !$this->isWithinScope($assignment, $student->faculty_id, $student->department_id)) {
                return $this->forbidden();
            }
        }

        // Check Faculty Access
        if (isset($parameters['faculty']) && $parameters['faculty'] instanceof \App\Models\Faculty) {
            $faculty = $parameters['faculty'];
            if ($assignment->faculty_id && $assignment->faculty_id != $faculty->id) {
                return $this->forbidden();
            }
        }

        // Check Department Access (often nested or standalone)
        if (isset($parameters['department']) && $parameters['department'] instanceof \App\Models\Department) {
            $dept = $parameters['department'];
            if (!$this->isWithinScope($assignment, $dept->faculty_id, $dept->id)) {
                return $this->forbidden();
            }
        }

        return $next($request);
    }

    protected function isWithinScope($assignment, $facultyId, $deptId)
    {
        // If assigned to a faculty, must match
        if ($assignment->faculty_id && $assignment->faculty_id != $facultyId) {
            return false;
        }

        // If assigned to a department, must match
        if ($assignment->department_id && $assignment->department_id != $deptId) {
            return false;
        }

        return true;
    }

    protected function forbidden()
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Unauthorised scope access.'], 403);
        }
        return abort(403, 'ليس لديك صلاحية الوصول لهذه البيانات (خارج نطاق كليتك).');
    }
}
