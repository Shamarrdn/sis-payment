<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAssignment extends Model
{
    protected $fillable = ['user_id', 'faculty_id', 'department_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Human-readable scope label.
     */
    public function scopeLabel(): string
    {
        if (!$this->faculty_id) return 'كل الكليات';
        $label = $this->faculty->name ?? 'كلية';
        if ($this->department_id) {
            $label .= ' / ' . ($this->department->name ?? 'قسم');
        }
        return $label;
    }
}
