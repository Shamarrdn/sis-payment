<?php

namespace App\Services;

use App\Models\LoginActivity;
use Illuminate\Http\Request;

class LoginActivityLogger
{
    public static function log(Request $request, string $guard, bool $success, ?int $userId = null, ?int $studentId = null, ?string $identifier = null): void
    {
        LoginActivity::create([
            'guard'        => $guard,
            'user_id'      => $userId,
            'student_id'   => $studentId,
            'email_or_id'  => $identifier,
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'success'      => $success,
        ]);
    }
}
