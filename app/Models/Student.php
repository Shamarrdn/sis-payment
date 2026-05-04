<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'national_id',
        'reference_number',
        'academic_year',
        'program',
        'phone',
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
}
