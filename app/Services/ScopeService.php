<?php

namespace App\Services;

use App\Models\User;
use App\Models\AdminAssignment;
use Illuminate\Database\Eloquent\Builder;

class ScopeService
{
    /**
     * Returns the faculty IDs the given user is allowed to see.
     * Returns null for super_admin (= all faculties).
     *
     * @return int[]|null
     */
    public static function allowedFacultyIds(User $user): ?array
    {
        // Super admins have no restriction
        if ($user->role === 'super_admin') return null;

        $assignment = AdminAssignment::where('user_id', $user->id)->first();

        if (!$assignment) {
            // No assignment = global access (default behavior for legacy/non-scoped users)
            return null;
        }

        if ($assignment->faculty_id === null) {
            // assigned to all faculties explicitly
            return null;
        }

        return [$assignment->faculty_id];
    }

    /**
     * Returns the department IDs the given user is allowed to see.
     * Returns null for no restriction.
     *
     * @return int[]|null
     */
    public static function allowedDepartmentIds(User $user): ?array
    {
        if ($user->role === 'super_admin') return null;

        $assignment = AdminAssignment::where('user_id', $user->id)->first();

        if (!$assignment || $assignment->department_id === null) {
            return null; // all departments within allowed faculties
        }

        return [$assignment->department_id];
    }

    /**
     * Apply faculty/department scope to a Student query.
     */
    public static function scopeStudents(Builder $query, User $user): Builder
    {
        $facultyIds    = self::allowedFacultyIds($user);
        $departmentIds = self::allowedDepartmentIds($user);

        $table = $query->getModel()->getTable();
        if ($facultyIds !== null) {
            if (empty($facultyIds)) {
                return $query->whereRaw('1 = 0'); // no access
            }
            $query->whereIn($table . '.faculty_id', $facultyIds);
        }

        if ($departmentIds !== null) {
            $query->whereIn($table . '.department_id', $departmentIds);
        }

        return $query;
    }

    /**
     * Apply faculty/department scope to a Payment query.
     */
    public static function scopePayments(Builder $query, User $user): Builder
    {
        $facultyIds    = self::allowedFacultyIds($user);
        $departmentIds = self::allowedDepartmentIds($user);

        $table = $query->getModel()->getTable();
        if ($facultyIds !== null) {
            if (empty($facultyIds)) {
                return $query->whereRaw('1 = 0');
            }
            $query->whereIn($table . '.faculty_id', $facultyIds);
        }

        if ($departmentIds !== null) {
            $query->whereIn($table . '.department_id', $departmentIds);
        }

        return $query;
    }

    /**
     * Returns a human-readable scope label for a user.
     */
    public static function scopeLabel(User $user): string
    {
        if ($user->role === 'super_admin') return 'كل الكليات';

        $assignment = AdminAssignment::with(['faculty', 'department'])
            ->where('user_id', $user->id)
            ->first();

        if (!$assignment) return 'غير محدد';
        return $assignment->scopeLabel();
    }

    /**
     * Get the user's assignment (or null if super_admin).
     */
    public static function assignment(User $user): ?AdminAssignment
    {
        if ($user->role === 'super_admin') return null;
        return AdminAssignment::with(['faculty', 'department'])
            ->where('user_id', $user->id)
            ->first();
    }
}
