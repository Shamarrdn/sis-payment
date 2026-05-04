<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLoggerService
{
    /**
     * Log an action to the audit logs.
     *
     * @param string $action The action description (e.g. 'Update Service')
     * @param mixed $model The model being operated on (optional)
     * @param array $oldValues Previous values of the model before update (optional)
     * @param array $newValues New values of the model after update (optional)
     * @return void
     */
    public static function log(string $action, $model = null, array $oldValues = null, array $newValues = null)
    {
        $userId = null;

        // Try to get user via the 'web' guard, otherwise fallback to null.
        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
