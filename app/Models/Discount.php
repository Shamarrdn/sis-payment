<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'category',
        'type',
        'value',
        'approving_authority',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'value'     => 'float',
    ];

    /**
     * Calculate discounted amount for a given price.
     */
    public function applyTo(float $amount): float
    {
        return match($this->type) {
            'full'       => 0.0,
            'percentage' => round($amount * (1 - $this->value / 100), 2),
            'partial'    => max(0, $amount - $this->value),
            default      => $amount,
        };
    }

    /**
     * Find the active discount for a student special_category.
     */
    public static function forCategory(?string $category): ?self
    {
        if (!$category) return null;
        return static::where('category', $category)->where('is_active', true)->first();
    }
}
