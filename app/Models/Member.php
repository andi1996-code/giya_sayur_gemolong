<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_code',
        'name',
        'email',
        'phone',
        'address',
        'birth_date',
        'gender',
        'tier',
        'total_points',
        'lifetime_points',
        'total_spent',
        'total_transactions',
        'registered_date',
        'last_transaction_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'registered_date' => 'date',
        'last_transaction_date' => 'date',
        'total_spent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($member) {
            if (empty($member->member_code)) {
                $member->member_code = self::generateMemberCode();
            }
            if (empty($member->registered_date)) {
                $member->registered_date = now();
            }
        });
    }

    /**
     * Generate unique member code
     */
    public static function generateMemberCode(): string
    {
        $lastMember = self::withTrashed()->orderBy('id', 'desc')->first();
        $number = $lastMember ? $lastMember->id + 1 : 1;
        return 'MBR' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get tier display name
     */
    public function getTierNameAttribute(): string
    {
        return ucfirst($this->tier);
    }

    /**
     * Get tier badge color
     */
    public function getTierColorAttribute(): string
    {
        return match($this->tier) {
            'bronze' => 'warning',
            'silver' => 'gray',
            'gold' => 'success',
            'platinum' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Relationships
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * Add points to member
     */
    public function addPoints(int $points, ?int $transactionId = null, ?float $transactionAmount = null, ?string $description = null): void
    {
        $pointSettings = PointSetting::first();
        // Ensure point_expiry_days is integer for Carbon
        $expiryDays = (int) ($pointSettings->point_expiry_days ?? 365);
        $expiryDate = now()->addDays($expiryDays);

        $balanceBefore = $this->total_points;
        $this->total_points += $points;
        $this->lifetime_points += $points;
        $balanceAfter = $this->total_points;
        $this->save();

        $this->pointTransactions()->create([
            'transaction_id' => $transactionId,
            'type' => 'earned',
            'points' => $points,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'transaction_amount' => $transactionAmount,
            'description' => $description ?? 'Poin dari transaksi',
            'expired_at' => $expiryDate,
        ]);
    }

    /**
     * Redeem points from member
     */
    public function redeemPoints(int $points, ?int $transactionId = null, ?string $description = null): bool
    {
        if ($this->total_points < $points) {
            return false;
        }

        $balanceBefore = $this->total_points;
        $this->total_points -= $points;
        $balanceAfter = $this->total_points;
        $this->save();

        $this->pointTransactions()->create([
            'transaction_id' => $transactionId,
            'type' => 'redeemed',
            'points' => -$points,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description ?? 'Penukaran poin',
        ]);

        return true;
    }

    /**
     * Deduct points from member (alias for redeemPoints)
     */
    public function deductPoints(int $points, ?int $transactionId = null, ?string $description = null): bool
    {
        return $this->redeemPoints($points, $transactionId, $description);
    }

    /**
     * Update member tier based on total spent
     */
    public function updateTier(): void
    {
        $pointSettings = PointSetting::first();

        if (!$pointSettings || !$pointSettings->auto_tier_upgrade) {
            return;
        }

        $oldTier = $this->tier;

        if ($this->total_spent >= $pointSettings->platinum_min_spent) {
            $this->tier = 'platinum';
        } elseif ($this->total_spent >= $pointSettings->gold_min_spent) {
            $this->tier = 'gold';
        } elseif ($this->total_spent >= $pointSettings->silver_min_spent) {
            $this->tier = 'silver';
        } else {
            $this->tier = 'bronze';
        }

        if ($oldTier !== $this->tier) {
            $this->save();
        }
    }

    /**
     * Get tier multiplier
     */
    public function getTierMultiplier(): float
    {
        $pointSettings = PointSetting::first();

        if (!$pointSettings) {
            return 1.0;
        }

        return match($this->tier) {
            'bronze' => $pointSettings->bronze_multiplier,
            'silver' => $pointSettings->silver_multiplier,
            'gold' => $pointSettings->gold_multiplier,
            'platinum' => $pointSettings->platinum_multiplier,
            default => 1.0,
        };
    }
}
