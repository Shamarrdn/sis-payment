<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionConfig extends Model
{
    protected $fillable = [
        'faculty_id',
        'department_id',
        'academic_year',
        'user_category',
        'tuition_amount',
        'extra_fee',
        'effective_from',
        'effective_to',
        'is_active',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'tuition_amount' => 'float',
        'extra_fee'      => 'float',
        'is_active'      => 'boolean',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Total amount = tuition + extra_fee.
     */
    public function totalAmount(): float
    {
        return $this->tuition_amount + $this->extra_fee;
    }

    /**
     * Human-readable label for this config.
     */
    public function label(): string
    {
        $parts = [];
        if ($this->faculty)          $parts[] = $this->faculty->name;
        if ($this->department)       $parts[] = $this->department->name;
        if ($this->academic_year)    $parts[] = $this->academic_year;
        if ($this->user_category)    $parts[] = $this->user_category;
        return implode(' / ', $parts) ?: 'الإعداد الافتراضي';
    }
}
