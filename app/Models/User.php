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
        'is_active',
        'must_change_password',
        'last_login_at',
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
            'permissions'          => 'array',
            'must_change_password' => 'boolean',
            'last_login_at'        => 'datetime',
        ];
    }

    /**
     * Check if the user has a specific granular permission.
     * Super admin always passes all checks.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        if ($this->role === 'financial_affairs') {
            $defaults = [
                'view_reports',
                'edit_prices',
                'approve_refunds',
                'cancel_payments',
                'manual_cash_entry',
                'export_data',
                'view_audit_log',
            ];
            if (in_array($permission, $defaults, true)) {
                return true;
            }
        }

        if ($this->role === 'admin') {
            $defaults = [
                'view_reports',
                'approve_refunds',
                'cancel_payments',
                'manual_cash_entry',
                'export_data',
                'view_audit_log',
                'manage_settings',
            ];
            if (in_array($permission, $defaults, true)) {
                return true;
            }
        }

        return in_array($permission, $this->permissions ?? [], true);
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

    /**
     * Get the admin assignment / scope for this user.
     */
    public function assignment()
    {
        return $this->hasOne(AdminAssignment::class);
    }
}
