<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'points_required',
        'stock',
        'is_active',
        'max_redeem_per_member',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Reward redemptions
     */
    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Check if reward is available for redemption
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Check if member can redeem this reward
     */
    public function canBeRedeemedBy(Member $member): array
    {
        // Cek apakah reward aktif
        if (!$this->is_active) {
            return [
                'can_redeem' => false,
                'reason' => 'Hadiah ini sedang tidak tersedia',
            ];
        }

        // Cek stok
        if ($this->stock <= 0) {
            return [
                'can_redeem' => false,
                'reason' => 'Stok hadiah habis',
            ];
        }

        // Cek poin member cukup
        if ($member->total_points < $this->points_required) {
            return [
                'can_redeem' => false,
                'reason' => 'Poin Anda tidak mencukupi',
                'points_needed' => $this->points_required - $member->total_points,
            ];
        }

        // Cek limit per member (jika ada)
        if ($this->max_redeem_per_member !== null) {
            $redeemCount = RewardRedemption::where('member_id', $member->id)
                ->where('reward_item_id', $this->id)
                ->where('status', 'completed')
                ->count();

            if ($redeemCount >= $this->max_redeem_per_member) {
                return [
                    'can_redeem' => false,
                    'reason' => 'Anda sudah mencapai batas maksimal penukaran hadiah ini',
                ];
            }
        }

        return [
            'can_redeem' => true,
        ];
    }

    /**
     * Get member's redeem count for this reward
     */
    public function getMemberRedeemCount(Member $member): int
    {
        return RewardRedemption::where('member_id', $member->id)
            ->where('reward_item_id', $this->id)
            ->where('status', 'completed')
            ->count();
    }
}
