<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensitiveDataRequest extends Model
{
    protected $fillable = [
        'student_id',
        'requested_data',
        'status',
        'reviewed_by',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_data' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
