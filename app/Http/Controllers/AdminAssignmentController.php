<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\AdminAssignment;
use App\Services\AuditLoggerService;

class AdminAssignmentController extends Controller
{
    /**
     * Update or create an admin assignment for a user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'faculty_id'    => 'nullable|exists:faculties,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $old = AdminAssignment::where('user_id', $user->id)->first()?->toArray();

        // null faculty_id = all faculties
        AdminAssignment::updateOrCreate(
            ['user_id' => $user->id],
            [
                'faculty_id'    => $request->faculty_id ?: null,
                'department_id' => $request->department_id ?: null,
            ]
        );

        $new = AdminAssignment::where('user_id', $user->id)->first()->toArray();
        AuditLoggerService::log('Update Admin Assignment', $user, $old, $new);

        return back()->with('success', 'تم تحديث نطاق عمل الموظف: ' . $user->name);
    }

    /**
     * Remove scope restriction (grant all-faculty access).
     */
    public function clear(User $user)
    {
        $old = AdminAssignment::where('user_id', $user->id)->first()?->toArray();
        AdminAssignment::where('user_id', $user->id)->delete();
        AuditLoggerService::log('Clear Admin Assignment', $user, $old, null);

        return back()->with('success', 'تم منح الموظف صلاحية الوصول لكل الكليات.');
    }
}
