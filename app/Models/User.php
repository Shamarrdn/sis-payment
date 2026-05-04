<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'permissions'       => 'array',
        ];
    }

    /**
     * Check if the user has a specific granular permission.
     * Super admin always passes all checks.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'super_admin') return true;
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * All available permissions catalogue.
     */
    public static function availablePermissions(): array
    {
        return [
            'view_reports'      => 'عرض التقارير والإحصائيات',
            'edit_prices'       => 'تعديل أسعار الخدمات',
            'approve_refunds'   => 'اعتماد طلبات الاسترداد',
            'cancel_payments'   => 'إلغاء العمليات المعلقة',
            'manual_cash_entry' => 'إدخال دفع يدوي (Cash)',
            'export_data'       => 'تصدير البيانات (CSV/Excel)',
            'manage_students'   => 'إدارة بيانات الطلاب',
            'manage_settings'   => 'إدارة إعدادات النظام',
            'view_audit_log'    => 'الاطلاع على سجل المراجعة',
        ];
    }
}
