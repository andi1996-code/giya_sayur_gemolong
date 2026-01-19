<?php

namespace App\Helpers;

use App\Models\Member;
use App\Models\PointSetting;

class PointHelper
{
    /**
     * Calculate points earned from transaction amount
     */
    public static function calculateEarnedPoints(float $amount, ?Member $member = null): int
    {
        $pointSettings = PointSetting::first();

        if (!$pointSettings) {
            return 0;
        }

        $tier = $member ? $member->tier : 'bronze';

        return $pointSettings->calculatePoints($amount, $tier);
    }

    /**
     * Calculate discount from points redemption
     */
    public static function calculatePointsDiscount(int $points): float
    {
        $pointSettings = PointSetting::first();

        if (!$pointSettings) {
            return 0;
        }

        return $pointSettings->calculateDiscount($points);
    }

    /**
     * Validate if member can redeem points
     */
    public static function canRedeemPoints(Member $member, int $points): array
    {
        $pointSettings = PointSetting::first();

        if (!$pointSettings) {
            return [
                'can_redeem' => false,
                'message' => 'Pengaturan poin tidak ditemukan'
            ];
        }

        // Check minimum points
        if ($points < $pointSettings->min_points_redeem) {
            return [
                'can_redeem' => false,
                'message' => "Minimal penukaran poin adalah {$pointSettings->min_points_redeem} poin"
            ];
        }

        // Check member balance
        if ($member->total_points < $points) {
            return [
                'can_redeem' => false,
                'message' => "Poin tidak mencukupi. Saldo poin: {$member->total_points}"
            ];
        }

        return [
            'can_redeem' => true,
            'message' => 'Poin dapat ditukar',
            'discount_amount' => self::calculatePointsDiscount($points)
        ];
    }

    /**
     * Process transaction points (earn and redeem)
     */
    public static function processTransactionPoints(
        Member $member,
        float $totalAmount,
        int $pointsToRedeem = 0,
        ?int $transactionId = null
    ): array {
        $result = [
            'points_earned' => 0,
            'points_redeemed' => 0,
            'points_discount' => 0,
            'final_amount' => $totalAmount,
        ];

        $pointSettings = PointSetting::first();
        if (!$pointSettings) {
            return $result;
        }

        // Process points redemption first
        if ($pointsToRedeem > 0) {
            $validation = self::canRedeemPoints($member, $pointsToRedeem);

            if ($validation['can_redeem']) {
                $discount = self::calculatePointsDiscount($pointsToRedeem);

                // Discount cannot exceed total amount
                $discount = min($discount, $totalAmount);

                $result['points_redeemed'] = $pointsToRedeem;
                $result['points_discount'] = $discount;
                $result['final_amount'] = $totalAmount - $discount;

                // Redeem points
                $member->redeemPoints(
                    $pointsToRedeem,
                    $transactionId,
                    "Penukaran {$pointsToRedeem} poin (diskon Rp " . number_format($discount, 0, ',', '.') . ")"
                );
            }
        }

        // Calculate and add earned points from final amount
        $pointsEarned = self::calculateEarnedPoints($result['final_amount'], $member);

        if ($pointsEarned > 0) {
            $result['points_earned'] = $pointsEarned;

            // Add points to member
            $member->addPoints(
                $pointsEarned,
                $transactionId,
                $result['final_amount'],
                "Poin dari transaksi sebesar Rp " . number_format($result['final_amount'], 0, ',', '.')
            );
        }

        // Update member statistics
        $member->total_spent += $result['final_amount'];
        $member->total_transactions += 1;
        $member->last_transaction_date = now();
        $member->save();

        // Check and update tier
        $member->updateTier();

        return $result;
    }

    /**
     * Get points info for display
     */
    public static function getPointsInfo(?Member $member = null): array
    {
        $pointSettings = PointSetting::first();

        if (!$pointSettings) {
            return [
                'enabled' => false,
            ];
        }

        $info = [
            'enabled' => true,
            'point_per_amount' => $pointSettings->point_per_amount,
            'points_earned' => $pointSettings->points_earned,
            'point_value' => $pointSettings->point_value,
            'min_points_redeem' => $pointSettings->min_points_redeem,
            'point_expiry_days' => $pointSettings->point_expiry_days,
        ];

        if ($member) {
            $info['member'] = [
                'code' => $member->member_code,
                'name' => $member->name,
                'tier' => $member->tier,
                'tier_name' => $member->tier_name,
                'total_points' => $member->total_points,
                'multiplier' => $member->getTierMultiplier(),
            ];
        }

        return $info;
    }

    /**
     * Expire old points
     */
    public static function expireOldPoints(): int
    {
        $expiredCount = 0;
        $pointSettings = PointSetting::first();

        if (!$pointSettings) {
            return $expiredCount;
        }

        // Get all expired points that haven't been processed
        $expiredPointTransactions = \App\Models\PointTransaction::where('type', 'earned')
            ->where('expired_at', '<=', now())
            ->whereDoesntHave('member', function ($query) {
                // Make sure member still has the points
                $query->where('total_points', '>', 0);
            })
            ->get();

        foreach ($expiredPointTransactions as $pointTransaction) {
            $member = $pointTransaction->member;

            if ($member && $member->total_points > 0) {
                $pointsToExpire = min($pointTransaction->points, $member->total_points);

                $balanceBefore = $member->total_points;
                $member->total_points -= $pointsToExpire;
                $balanceAfter = $member->total_points;
                $member->save();

                // Create expired transaction record
                $member->pointTransactions()->create([
                    'type' => 'expired',
                    'points' => -$pointsToExpire,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => 'Poin kadaluarsa',
                ]);

                $expiredCount++;
            }
        }

        return $expiredCount;
    }
}
