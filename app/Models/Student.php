<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'name',
        'national_id',
        'reference_number',
        'academic_year',
        'program',
        'phone',
        'email',
        'address',
        'college',
        'faculty_id',
        'department_id',
        'user_category',
        'special_category',
        'username',
        'password',
        'status',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function sensitiveDataRequests(): HasMany
    {
        return $this->hasMany(SensitiveDataRequest::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class);
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(StudentNote::class)->latest();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Display name for faculty — prefers the related model, falls back to the legacy 'college' string.
     */
    public function facultyName(): string
    {
        return $this->faculty?->name ?? $this->college ?? '—';
    }

    /**
     * Display name for department — prefers the related model, falls back to 'program' string.
     */
    public function departmentName(): string
    {
        return $this->department?->name ?? $this->program ?? '—';
    }

    /**
     * Calculate profile completion percentage.
     */
    public function completionPercentage(): int
    {
        $percentage = 0;

        if (!empty($this->phone)) $percentage += 15;
        if (!empty($this->email)) $percentage += 15;
        if (!empty($this->address)) $percentage += 15;

        // Check documents status
        $docs = $this->documents;
        $hasNationalId = $docs->where('type', 'national_id')->where('status', '!=', 'rejected')->isNotEmpty();
        $hasBirthCert = $docs->where('type', 'birth_certificate')->where('status', '!=', 'rejected')->isNotEmpty();
        $hasPhoto = $docs->where('type', 'personal_photo')->where('status', '!=', 'rejected')->isNotEmpty();

        if ($hasNationalId) $percentage += 20;
        if ($hasBirthCert) $percentage += 15;
        if ($hasPhoto) $percentage += 20;

        return $percentage;
    }

    /**
     * Get checklist of missing required items.
     */
    public function missingChecklist(): array
    {
        $checklist = [];

        if (empty($this->phone)) {
            $checklist[] = ['key' => 'phone', 'label' => 'رقم الهاتف المحمول', 'type' => 'field'];
        }
        if (empty($this->email)) {
            $checklist[] = ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'field'];
        }
        if (empty($this->address)) {
            $checklist[] = ['key' => 'address', 'label' => 'العنوان السكني الحالي', 'type' => 'field'];
        }

        $docs = $this->documents;
        $nationalIdDoc = $docs->where('type', 'national_id')->first();
        if (!$nationalIdDoc || $nationalIdDoc->status === 'rejected') {
            $checklist[] = [
                'key' => 'document_national_id',
                'label' => 'صورة بطاقة الرقم القومي',
                'type' => 'document',
                'status' => $nationalIdDoc ? $nationalIdDoc->status : 'missing',
                'reason' => $nationalIdDoc ? $nationalIdDoc->rejection_reason : null
            ];
        }

        $birthCertDoc = $docs->where('type', 'birth_certificate')->first();
        if (!$birthCertDoc || $birthCertDoc->status === 'rejected') {
            $checklist[] = [
                'key' => 'document_birth_certificate',
                'label' => 'صورة شهادة الميلاد',
                'type' => 'document',
                'status' => $birthCertDoc ? $birthCertDoc->status : 'missing',
                'reason' => $birthCertDoc ? $birthCertDoc->rejection_reason : null
            ];
        }

        $photoDoc = $docs->where('type', 'personal_photo')->first();
        if (!$photoDoc || $photoDoc->status === 'rejected') {
            $checklist[] = [
                'key' => 'document_personal_photo',
                'label' => 'الصورة الشخصية الحديثة',
                'type' => 'document',
                'status' => $photoDoc ? $photoDoc->status : 'missing',
                'reason' => $photoDoc ? $photoDoc->rejection_reason : null
            ];
        }

        return $checklist;
    }

    /**
     * Determine financial status based on tuition payment.
     */
    public function financialStatus(): string
    {
        $full = Service::where('name', 'like', '%دفع كامل%')->where('is_active', true)->first();
        $inst1 = Service::where('name', 'like', '%القسط الأول%')->where('is_active', true)->first();
        $inst2 = Service::where('name', 'like', '%القسط الثاني%')->where('is_active', true)->first();

        $paidFull = $full ? $this->payments()->where('service_id', $full->id)->where('status', 'paid')->exists() : false;
        $paidInst1 = $inst1 ? $this->payments()->where('service_id', $inst1->id)->where('status', 'paid')->exists() : false;
        $paidInst2 = $inst2 ? $this->payments()->where('service_id', $inst2->id)->where('status', 'paid')->exists() : false;

        if ($paidFull || ($paidInst1 && $paidInst2)) {
            return 'paid';
        }
        return 'outstanding';
    }
}
