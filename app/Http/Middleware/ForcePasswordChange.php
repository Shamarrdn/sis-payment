<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        $user = $request->user($guard);

        if ($user && $user->must_change_password) {
            $changeRoute = $guard === 'student'
                ? 'student.password.change'
                : 'employee.password.change';

            if (!$request->routeIs($changeRoute) && !$request->routeIs('*.logout')) {
                return redirect()->route($changeRoute)
                    ->with('warning', 'يجب تغيير كلمة المرور قبل المتابعة.');
            }
        }

        return $next($request);
    }
}
