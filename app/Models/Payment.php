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
    ];

    protected $casts = [
        'payment_date' => 'datetime',
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
}
