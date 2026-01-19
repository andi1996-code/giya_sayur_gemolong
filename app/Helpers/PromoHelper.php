<?php

namespace App\Helpers;

use App\Models\Promo;
use App\Models\Product;
use Illuminate\Support\Collection;

class PromoHelper
{
    /**
     * Calculate applicable promos for order items
     */
    public static function calculatePromos(array $orderItems): array
    {
        $promos = Promo::where('is_active', true)->get();
        $appliedPromos = [];
        $totalDiscount = 0;
        $processedPromos = [];

        // Hitung total belanja (untuk promo minimum purchase)
        $totalPurchase = 0;
        foreach ($orderItems as $item) {
            $totalPurchase += ($item['quantity'] ?? 1) * ($item['price'] ?? 0);
        }

        // Tipe 1: Promo berdasarkan produk spesifik dan quantity
        foreach ($orderItems as $item) {
            $product = Product::find($item['product_id'] ?? null);
            if (!$product) {
                continue;
            }

            foreach ($promos as $promo) {
                // Skip jika sudah ter-process atau bukan tipe produk spesifik
                if (isset($processedPromos[$promo->id]) || $promo->minimum_purchase) {
                    continue;
                }

                // Check if this product triggers the promo
                if ($promo->trigger_product_id === $product->id) {
                    $quantity = ($promo->trigger_unit === 'kg')
                        ? ($item['weight_kg'] ?? $item['weight'] ?? 0)
                        : ($item['quantity'] ?? 0);

                    // Use float comparison with small epsilon for precision issues
                    if (floatval($quantity) < floatval($promo->trigger_quantity) - 0.001) {
                        continue;
                    }

                    $discountProductId = $promo->apply_to_product_id ?? $promo->trigger_product_id;
                    $discountProduct = Product::find($discountProductId);
                    if (!$discountProduct) continue;

                    if ($promo->trigger_unit === 'kg') {
                        $unitPrice = $discountProduct->getFinalPricePerKg() ?? $discountProduct->price_per_kg;
                        $totalPrice = $unitPrice * $quantity;
                    } else {
                        $unitPrice = $discountProduct->getFinalPrice();
                        $totalPrice = $unitPrice * $quantity;
                    }

                    $discount = $promo->calculateDiscount($quantity, (int)$totalPrice);

                    if ($discount > 0) {
                        $appliedPromos[] = [
                            'promo_id' => $promo->id,
                            'promo_name' => $promo->name,
                            'product_id' => $discountProductId,
                            'product_name' => $discountProduct->name,
                            'discount_amount' => $discount,
                            'description' => $promo->getDiscountDescription(),
                        ];
                        $totalDiscount += $discount;
                        $processedPromos[$promo->id] = true;
                    }
                }
            }
        }

        // Tipe 2: Promo berdasarkan minimum total belanja (berlaku sekali saja, produk bebas)
        foreach ($promos as $promo) {
            // Untuk promo minimum purchase, tidak perlu check trigger_product_id
            // Cukup check apakah total belanja >= minimum_purchase
            if ($promo->minimum_purchase && !$promo->trigger_product_id && $totalPurchase >= $promo->minimum_purchase && !isset($processedPromos[$promo->id])) {
                $freeProduct = Product::find($promo->free_product_id);
                if (!$freeProduct) continue;

                $freeQuantity = $promo->free_quantity ?? 1;

                if ($promo->trigger_unit === 'kg') {
                    $unitPrice = $freeProduct->getFinalPricePerKg() ?? $freeProduct->price_per_kg;
                    $totalPrice = $unitPrice * $freeQuantity;
                } else {
                    $unitPrice = $freeProduct->getFinalPrice();
                    $totalPrice = $unitPrice * $freeQuantity;
                }

                $discount = $promo->calculateDiscount($freeQuantity, (int)$totalPrice);

                if ($discount > 0) {
                    $appliedPromos[] = [
                        'promo_id' => $promo->id,
                        'promo_name' => $promo->name,
                        'product_id' => $promo->free_product_id,
                        'product_name' => $freeProduct->name,
                        'discount_amount' => $discount,
                        'description' => $promo->getDiscountDescription(),
                    ];
                    $totalDiscount += $discount;
                    $processedPromos[$promo->id] = true;
                }
            }
        }

        return [
            'promos' => $appliedPromos,
            'total_discount' => $totalDiscount,
        ];
    }    /**
     * Get active promos for display
     */
    public static function getActivePromos(): Collection
    {
        return Promo::where('is_active', true)
            ->where(function ($query) {
                $now = now();
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) {
                $now = now();
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->get();
    }

    /**
     * Get promo description for display
     */
    public static function getPromoDescription(Promo $promo): string
    {
        return $promo->getDiscountDescription();
    }

    /**
     * Check if promo is eligible for order items
     */
    public static function isPromoEligible(Promo $promo, array $orderItems): bool
    {
        foreach ($orderItems as $item) {
            if ($item['product_id'] === $promo->trigger_product_id) {
                $quantity = ($promo->trigger_unit === 'kg')
                    ? ($item['weight_kg'] ?? $item['weight'] ?? 0)
                    : ($item['quantity'] ?? 0);

                // Use float comparison with small epsilon for precision issues
                return floatval($quantity) >= floatval($promo->trigger_quantity) - 0.001;
            }
        }
        return false;
    }
}
