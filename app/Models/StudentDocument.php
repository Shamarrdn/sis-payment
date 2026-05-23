<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'file_path',
        'status',
        'rejection_reason',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
