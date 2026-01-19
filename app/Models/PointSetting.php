<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'point_per_amount',
        'points_earned',
        'point_value',
        'min_points_redeem',
        'point_expiry_days',
        'auto_tier_upgrade',
        'bronze_min_spent',
        'silver_min_spent',
        'gold_min_spent',
        'platinum_min_spent',
        'bronze_multiplier',
        'silver_multiplier',
        'gold_multiplier',
        'platinum_multiplier',
    ];

    protected $casts = [
        'point_per_amount' => 'decimal:2',
        'point_value' => 'decimal:2',
        'bronze_min_spent' => 'decimal:2',
        'silver_min_spent' => 'decimal:2',
        'gold_min_spent' => 'decimal:2',
        'platinum_min_spent' => 'decimal:2',
        'bronze_multiplier' => 'decimal:2',
        'silver_multiplier' => 'decimal:2',
        'gold_multiplier' => 'decimal:2',
        'platinum_multiplier' => 'decimal:2',
        'auto_tier_upgrade' => 'boolean',
    ];

    /**
     * Calculate points earned from transaction amount
     */
    public function calculatePoints(float $amount, string $tier = 'bronze'): int
    {
        // Base points calculation
        $basePoints = floor($amount / $this->point_per_amount) * $this->points_earned;

        // Apply tier multiplier
        $multiplier = match($tier) {
            'bronze' => $this->bronze_multiplier,
            'silver' => $this->silver_multiplier,
            'gold' => $this->gold_multiplier,
            'platinum' => $this->platinum_multiplier,
            default => 1.0,
        };

        return (int) floor($basePoints * $multiplier);
    }

    /**
     * Calculate discount amount from points
     */
    public function calculateDiscount(int $points): float
    {
        return $points * $this->point_value;
    }

    /**
     * Check if points can be redeemed
     */
    public function canRedeem(int $points): bool
    {
        return $points >= $this->min_points_redeem;
    }
}
