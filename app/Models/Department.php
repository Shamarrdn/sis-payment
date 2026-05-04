<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['faculty_id', 'name', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function tuitionConfigs(): HasMany
    {
        return $this->hasMany(TuitionConfig::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
