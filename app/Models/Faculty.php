<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function tuitionConfigs(): HasMany
    {
        return $this->hasMany(TuitionConfig::class);
    }

    public function adminAssignments(): HasMany
    {
        return $this->hasMany(AdminAssignment::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activeDepartments(): HasMany
    {
        return $this->hasMany(Department::class)->where('is_active', true);
    }
}
