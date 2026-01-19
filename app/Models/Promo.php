<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type', // 'buy_x_get_discount' or 'bundle'
        'trigger_product_id', // produk yang harus dibeli
        'trigger_quantity', // jumlah yang harus dibeli
        'trigger_unit', // 'qty' or 'kg' untuk unit pembelian
        'minimum_purchase', // minimum total belanja
        'discount_type', // 'percentage' atau 'fixed' atau 'price'
        'discount_value', // nilai diskon
        'apply_to_product_id', // produk yang dapat diskon (bisa null jika sama dengan trigger)
        'free_product_id', // produk yang dapat diskon untuk promo minimum purchase
        'free_quantity', // kuantitas produk gratis
        'max_discount_per_transaction', // max diskon per transaksi (null = unlimited)
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'trigger_quantity' => 'decimal:3',
        'discount_value' => 'integer',
        'minimum_purchase' => 'integer',
        'free_quantity' => 'decimal:3',
        'max_discount_per_transaction' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function triggerProduct()
    {
        return $this->belongsTo(Product::class, 'trigger_product_id');
    }

    public function applyToProduct()
    {
        return $this->belongsTo(Product::class, 'apply_to_product_id');
    }

    public function freeProduct()
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }

    /**
     * Check if promo is currently active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->isBefore($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->isAfter($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount for given quantity
     */
    public function calculateDiscount(float $quantity, int $price): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        // Check if quantity meets trigger requirement (with floating point tolerance)
        if (floatval($quantity) < floatval($this->trigger_quantity) - 0.001) {
            return 0;
        }

        // Calculate how many times trigger is met
        $multiplier = floor($quantity / $this->trigger_quantity);

        $discount = 0;
        switch ($this->discount_type) {
            case 'percentage':
                $discount = (int)(($price * $this->discount_value) / 100);
                break;
            case 'fixed':
                $discount = (int)$this->discount_value;
                break;
            case 'price':
                // Harga khusus - return selisih antara harga normal dan harga promo
                $discount = $price - (int)$this->discount_value;
                break;
        }

        // Apply multiplier jika ada
        $totalDiscount = (int)($discount * $multiplier);

        // Check max discount per transaction
        if ($this->max_discount_per_transaction && $totalDiscount > $this->max_discount_per_transaction) {
            $totalDiscount = $this->max_discount_per_transaction;
        }

        return $totalDiscount;
    }

    /**
     * Get discount description
     */
    public function getDiscountDescription(): string
    {
        $triggerProduct = $this->triggerProduct->name ?? 'Produk';

        // Format trigger_quantity untuk display (hapus trailing zeros)
        $quantity = $this->trigger_quantity;
        if (is_numeric($quantity)) {
            $quantity = floatval($quantity);
            // Hapus trailing zeros dan titik jika desimal
            $quantity = rtrim(rtrim((string)$quantity, '0'), '.');
        }

        $description = "Beli {$quantity} {$this->trigger_unit} {$triggerProduct}";

        switch ($this->discount_type) {
            case 'percentage':
                $description .= " - Diskon {$this->discount_value}%";
                break;
            case 'fixed':
                $description .= " - Diskon Rp " . number_format($this->discount_value, 0, ',', '.');
                break;
            case 'price':
                $description .= " - Harga spesial Rp " . number_format($this->discount_value, 0, ',', '.');
                break;
        }

        return $description;
    }
}
