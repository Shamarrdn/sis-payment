<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'faculty_id',
        'department_id',
        'academic_year',
        'expires_at',
        'is_published',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeVisibleToStudent(Builder $query, Student $student): Builder
    {
        return $query->published()->where(function ($q) use ($student) {
            $q->where(function ($general) {
                $general->whereNull('faculty_id')
                    ->whereNull('department_id')
                    ->whereNull('academic_year');
            })->orWhere(function ($targeted) use ($student) {
                $targeted->where(function ($q) use ($student) {
                    $q->whereNull('faculty_id')->orWhere('faculty_id', $student->faculty_id);
                })->where(function ($q) use ($student) {
                    $q->whereNull('department_id')->orWhere('department_id', $student->department_id);
                })->where(function ($q) use ($student) {
                    $q->whereNull('academic_year')->orWhere('academic_year', $student->academic_year);
                })->where(function ($q) {
                    $q->whereNotNull('faculty_id')
                        ->orWhereNotNull('department_id')
                        ->orWhereNotNull('academic_year');
                });
            });
        });
    }

    public function isGeneral(): bool
    {
        return !$this->faculty_id && !$this->department_id && !$this->academic_year;
    }
}
