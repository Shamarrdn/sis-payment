<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'service_id',
        'faculty_id',
        'department_id',
        'faculty_snapshot',
        'department_snapshot',
        'amount',
        'quantity',
        'total_amount',
        'reference_number',
        'notes',
        'payment_method',
        'academic_year',
        'program',
        'user_category',
        'payment_date',
        'status',
        'refund_status',
        'refund_reason',
        'refunded_amount',
        'fulfillment_status',
        'request_fields',
        'started_at',
        'completed_at',
        'fulfillment_cancelled_at',
        'rating',
        'rating_comment',
        'rated_at',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'request_fields' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'fulfillment_cancelled_at' => 'datetime',
        'rated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PaymentStatusHistory::class)->orderBy('created_at');
    }

    public function canBeCancelledByStudent(): bool
    {
        return $this->fulfillment_status === 'awaiting_processing'
            && !$this->started_at
            && $this->status === 'paid';
    }

    public function canBeRated(): bool
    {
        return $this->fulfillment_status === 'completed' && !$this->rating;
    }
}
