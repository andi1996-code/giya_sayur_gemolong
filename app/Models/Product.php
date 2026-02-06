<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'stock', 'stok_kongsi', 'cost_price', 'price', 'price_per_kg',
        'image', 'barcode', 'sku', 'description', 'is_active',
        'discount_percentage', 'discount_active', 'discount_start_date', 'discount_end_date',
        'is_reward', 'points_required', 'max_redeem_per_member',
    ];

    protected $casts = [
        'stock' => 'decimal:3',
        'stok_kongsi' => 'decimal:3',
        'cost_price' => 'integer',
        'price' => 'integer',
        'price_per_kg' => 'integer',
        'is_active' => 'boolean',
        'discount_percentage' => 'decimal:2',
        'discount_active' => 'boolean',
        'discount_start_date' => 'datetime',
        'discount_end_date' => 'datetime',
        'is_reward' => 'boolean',
        'points_required' => 'integer',
        'max_redeem_per_member' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Check if discount is currently active
     */
    public function hasActiveDiscount(): bool
    {
        if (!$this->discount_active || $this->discount_percentage <= 0) {
            return false;
        }

        $now = now();

        // If no date range is set, discount is always active when enabled
        if (!$this->discount_start_date && !$this->discount_end_date) {
            return true;
        }

        // Check if current time is within discount period
        $startDate = $this->discount_start_date ?: $now->copy()->subYear();
        $endDate = $this->discount_end_date ?: $now->copy()->addYear();

        return $now->between($startDate, $endDate);
    }

    /**
     * Get discounted price
     */
    public function getDiscountedPrice(): int
    {
        if (!$this->hasActiveDiscount() || !$this->price) {
            return (int) ($this->price ?? 0);
        }

        $discount = ($this->price * $this->discount_percentage) / 100;
        return (int) ($this->price - $discount);
    }

    /**
     * Get discounted price per kg
     */
    public function getDiscountedPricePerKg(): ?int
    {
        if (!$this->price_per_kg || !$this->hasActiveDiscount()) {
            return $this->price_per_kg;
        }

        $discount = ($this->price_per_kg * $this->discount_percentage) / 100;
        return (int) ($this->price_per_kg - $discount);
    }

    /**
     * Get final price (with discount applied if active)
     */
    public function getFinalPrice(): int
    {
        // Prioritaskan price, jika tidak ada gunakan price_per_kg
        $basePrice = $this->price ?? $this->price_per_kg ?? 0;

        if (!$this->hasActiveDiscount() || !$basePrice) {
            return (int) $basePrice;
        }

        $discount = ($basePrice * $this->discount_percentage) / 100;
        return (int) ($basePrice - $discount);
    }

    /**
     * Get final price per kg (with discount applied if active)
     */
    public function getFinalPricePerKg(): ?int
    {
        return $this->getDiscountedPricePerKg();
    }

    /**
     * Get total available stock (regular stock + consortium stock)
     */
    public function getTotalAvailableStock(): float
    {
        return (float)(($this->stock ?? 0) + ($this->stok_kongsi ?? 0));
    }

    /**
     * Check if consortium stock should be used first for transactions
     */
    public function shouldUseKongsiFirst(): bool
    {
        return ($this->stok_kongsi ?? 0) > 0;
    }

        /**
     * Get formatted stock display
     */
    public function getFormattedStock(): string
    {
        $regularStock = $this->stock ?? 0;
        $kongsiStock = $this->stok_kongsi ?? 0;
        $totalStock = $regularStock + $kongsiStock;

        // Format dengan 1 desimal tapi hilangkan trailing zeros
        $formatStock = function($value) {
            return rtrim(rtrim(number_format($value, 1, ',', '.'), '0'), ',');
        };

        return $formatStock($totalStock) . ' kg (R:' . $formatStock($regularStock) . ' K:' . $formatStock($kongsiStock) . ')';
    }

    /**
     * Get simple formatted total stock (for POS display)
     */
    public function getFormattedTotalStock(): string
    {
        $totalStock = $this->getTotalAvailableStock();

        // Format dengan 1 desimal tapi hilangkan trailing zeros
        return rtrim(rtrim(number_format($totalStock, 1, '.', ''), '0'), '.');
    }

    /**
     * Format harga dengan titik ribuan
     */
    public static function formatPrice($price): string
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    /**
     * Tentukan satuan berdasarkan jenis harga yang digunakan
     */
    public function getUnitType(): string
    {
        // Jika hanya menggunakan price (satuan/pcs), maka PCS
        if ($this->price > 0 && ($this->price_per_kg === null || $this->price_per_kg == 0)) {
            return 'PCS';
        }
        // Jika hanya menggunakan price_per_kg atau keduanya ada, gunakan kg
        return 'kg';
    }

    /**
     * Relasi ke reward redemptions
     */
    public function rewardRedemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Check jika member sudah mencapai limit redeem untuk produk ini
     */
    public function canBeRedeemedBy($memberId): bool
    {
        if (!$this->is_reward || !$this->is_active) {
            return false;
        }

        // Cek stok
        if ($this->stock <= 0) {
            return false;
        }

        // Cek limit per member (jika ada)
        if ($this->max_redeem_per_member !== null) {
            $redeemedCount = RewardRedemption::where('member_id', $memberId)
                ->where('product_id', $this->id)
                ->where('status', 'completed')
                ->sum('quantity');

            if ($redeemedCount >= $this->max_redeem_per_member) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get remaining redeem quota untuk member tertentu
     */
    public function getRemainingQuotaFor($memberId): ?int
    {
        if (!$this->is_reward || $this->max_redeem_per_member === null) {
            return null; // Unlimited
        }

        $redeemedCount = RewardRedemption::where('member_id', $memberId)
            ->where('product_id', $this->id)
            ->where('status', 'completed')
            ->sum('quantity');

        return max(0, $this->max_redeem_per_member - $redeemedCount);
    }
}
