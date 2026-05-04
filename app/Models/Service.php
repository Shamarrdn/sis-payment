<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'category',
        'amount',
        'requires_subject',
        'sub_options',
        'allows_quantity',
        'is_active',
        'faculty_id',       // null = available to all faculties
        'applicable_to',    // all | students | graduates | military
    ];

    protected $casts = [
        'sub_options'     => 'array',
        'requires_subject'=> 'boolean',
        'allows_quantity' => 'boolean',
        'is_active'       => 'boolean',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Check if this service is available to a given user_category.
     */
    public function availableTo(string $userCategory): bool
    {
        return match($this->applicable_to) {
            'all'       => true,
            'students'  => strtolower($userCategory) === 'student',
            'graduates' => strtolower($userCategory) === 'graduate',
            'military'  => strtolower($userCategory) === 'military college',
            default     => true,
        };
    }
}
