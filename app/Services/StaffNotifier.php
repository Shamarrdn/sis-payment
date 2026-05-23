<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;

class StaffNotifier
{
    public static function notifyRoles(array $roles, Notification $notification): void
    {
        User::whereIn('role', $roles)
            ->where('is_active', true)
            ->each(fn (User $user) => $user->notify($notification));
    }
}
