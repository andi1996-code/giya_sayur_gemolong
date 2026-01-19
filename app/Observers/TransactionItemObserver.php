<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\TransactionItem;

class TransactionItemObserver
{
    /**
     * Handle the TransactionItem "created" event.
     */
    public function created(TransactionItem $TransactionItem): void
    {
        $product = Product::find($TransactionItem->product_id);
        $quantityToDeduct = $this->calculateActualQuantity($TransactionItem, $product);
        $stockType = $this->deductStock($product, $quantityToDeduct);

        // Simpan tipe stok yang digunakan - use updateQuietly to avoid infinite loop
        $TransactionItem->updateQuietly(['stock_type' => $stockType]);
    }    /**
     * Handle the TransactionItem "updated" event.
     */
    public function updated(TransactionItem $TransactionItem): void
    {
        $product = Product::find($TransactionItem->product_id);

        // Calculate actual quantities for comparison
        $originalQuantity = $this->calculateActualQuantityFromOriginal($TransactionItem, $product);
        $newQuantity = $this->calculateActualQuantity($TransactionItem, $product);

        if ($originalQuantity != $newQuantity) {
            // Return original quantity to stock using stored stock type
            $stockType = $TransactionItem->getOriginal('stock_type') ?? 'regular';
            $this->returnStock($product, $originalQuantity, $stockType);

            // Deduct new quantity from stock
            $newStockType = $this->deductStock($product, $newQuantity);
            // Use updateQuietly to avoid infinite loop
            $TransactionItem->updateQuietly(['stock_type' => $newStockType]);
        }
    }

    /**
     * Handle the TransactionItem "deleted" event.
     */
    public function deleted(TransactionItem $TransactionItem): void
    {
        $product = Product::find($TransactionItem->product_id);
        $quantityToReturn = $this->calculateActualQuantity($TransactionItem, $product);
        $stockType = $TransactionItem->stock_type ?? 'regular';
        $this->returnStock($product, $quantityToReturn, $stockType);
    }

    /**
     * Deduct stock - return tipe stok yang digunakan
     */
    private function deductStock(Product $product, float $quantity): string
    {
        // Simple approach: if kongsi stock exists, deduct from kongsi, otherwise from regular
        if ($product->stok_kongsi >= $quantity) {
            // Cukup dari stok kongsi
            $product->decrement('stok_kongsi', $quantity);
            return 'kongsi';
        } elseif ($product->stok_kongsi > 0) {
            // Sebagian dari kongsi, sisanya dari reguler
            $fromKongsi = $product->stok_kongsi;
            $fromRegular = $quantity - $fromKongsi;

            $product->decrement('stok_kongsi', $fromKongsi);
            $product->decrement('stock', $fromRegular);
            // Kembalikan tipe yang dominan
            return 'kongsi';
        } else {
            // Ambil dari stok reguler saja
            $product->decrement('stock', $quantity);
            return 'regular';
        }
    }

    /**
     * Return stock - kembalikan sesuai tipe stok yang digunakan
     */
    private function returnStock(Product $product, float $quantity, string $stockType = 'regular'): void
    {
        // Kembalikan ke tipe stok yang sama
        if ($stockType === 'kongsi') {
            $product->increment('stok_kongsi', $quantity);
        } else {
            $product->increment('stock', $quantity);
        }
    }

    /**
     * Calculate actual quantity to deduct from stock based on unit conversion
     *
     * @param TransactionItem $transactionItem
     * @param Product $product
     * @return float
     */
    private function calculateActualQuantity(TransactionItem $transactionItem, Product $product): float
    {
        // If transaction item has weight (sold by weight), use weight directly (already in kg)
        if ($transactionItem->weight) {
            return (float) $transactionItem->weight;
        }

        // For regular quantity-based items, use quantity directly
        return (float) $transactionItem->quantity;
    }

    /**
     * Calculate original quantity from dirty attributes for update operations
     *
     * @param TransactionItem $transactionItem
     * @param Product $product
     * @return float
     */
    private function calculateActualQuantityFromOriginal(TransactionItem $transactionItem, Product $product): float
    {
        // Get original weight or quantity
        $originalWeight = $transactionItem->getOriginal('weight');
        $originalQuantity = $transactionItem->getOriginal('quantity');

        // If original transaction had weight, use weight directly (already in kg)
        if ($originalWeight) {
            return (float) $originalWeight;
        }

        // For regular quantity-based items, use original quantity
        return (float) $originalQuantity;
    }
}
