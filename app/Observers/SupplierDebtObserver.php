<?php

namespace App\Observers;

use App\Models\SupplierDebt;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class SupplierDebtObserver
{
    /**
     * Handle the SupplierDebt "created" event.
     */
    public function created(SupplierDebt $supplierDebt): void
    {
        $this->updateInventoryStock($supplierDebt, 'create');
    }

    /**
     * Handle the SupplierDebt "updated" event.
     */
    public function updated(SupplierDebt $supplierDebt): void
    {
        // Jika produk atau kuantitas berubah, update stok
        if ($supplierDebt->wasChanged(['product_id', 'quantity', 'transaction_type'])) {
            $this->handleStockUpdate($supplierDebt);
        }
    }

    /**
     * Handle the SupplierDebt "deleted" event.
     */
    public function deleted(SupplierDebt $supplierDebt): void
    {
        $this->updateInventoryStock($supplierDebt, 'delete');
    }

    /**
     * Handle the SupplierDebt "restored" event.
     */
    public function restored(SupplierDebt $supplierDebt): void
    {
        $this->updateInventoryStock($supplierDebt, 'create');
    }

    /**
     * Handle the SupplierDebt "force deleted" event.
     */
    public function forceDeleted(SupplierDebt $supplierDebt): void
    {
        $this->updateInventoryStock($supplierDebt, 'delete');
    }

    /**
     * Update inventory stock based on supplier debt transaction
     */
    private function updateInventoryStock(SupplierDebt $supplierDebt, string $action): void
    {
        if (!$supplierDebt->product_id || !$supplierDebt->quantity) {
            return;
        }

        $product = $supplierDebt->product;
        if (!$product) {
            return;
        }

        // Hitung perubahan stok
        $stockChange = 0;

        switch ($action) {
            case 'create':
            case 'restore':
                if ($supplierDebt->transaction_type === 'piutang') {
                    // Piutang dibuat = kita beri barang ke supplier = stok berkurang
                    $stockChange = -$supplierDebt->quantity;
                } elseif ($supplierDebt->transaction_type === 'hutang') {
                    // Hutang dibuat = supplier beri barang ke kita = stok bertambah
                    $stockChange = $supplierDebt->quantity;
                }
                break;

            case 'delete':
                if ($supplierDebt->transaction_type === 'piutang') {
                    // Piutang dihapus = barang kembali = stok bertambah
                    $stockChange = $supplierDebt->quantity;
                } elseif ($supplierDebt->transaction_type === 'hutang') {
                    // Hutang dihapus = barang dikembalikan ke supplier = stok berkurang
                    $stockChange = -$supplierDebt->quantity;
                }
                break;
        }

        if ($stockChange == 0) {
            return;
        }

        // Update stok berdasarkan jenis stok
        $oldRegularStock = $product->stock ?? 0;
        $oldKongsiStock = $product->stok_kongsi ?? 0;

        if ($supplierDebt->stock_type === 'kongsi') {
            // Update stok kongsi (titipan)
            $product->stok_kongsi = max(0, ($product->stok_kongsi ?? 0) + $stockChange);
            $newKongsiStock = $product->stok_kongsi;
            $stockTypeLabel = 'Stok Kongsi';
        } else {
            // Update stok reguler
            $product->stock = max(0, ($product->stock ?? 0) + $stockChange);
            $newRegularStock = $product->stock;
            $stockTypeLabel = 'Stok Reguler';
        }

        $product->save();

        // Kirim notifikasi
        $this->sendStockNotification($supplierDebt, $oldRegularStock, $oldKongsiStock, $product->stock ?? 0, $product->stok_kongsi ?? 0, $stockChange, $action, $stockTypeLabel);

        // Log aktivitas
        Log::info("Stock updated for product {$supplierDebt->product_id}: {$stockChange} units ({$stockTypeLabel})", [
            'supplier_debt_id' => $supplierDebt->id,
            'action' => $action,
            'stock_type' => $supplierDebt->stock_type ?? 'regular',
            'old_regular_stock' => $oldRegularStock,
            'new_regular_stock' => $product->stock ?? 0,
            'old_kongsi_stock' => $oldKongsiStock,
            'new_kongsi_stock' => $product->stok_kongsi ?? 0,
        ]);
    }

    /**
     * Handle stock update when supplier debt is modified
     */
    private function handleStockUpdate(SupplierDebt $supplierDebt): void
    {
        $original = $supplierDebt->getOriginal();

        // Jika ada perubahan yang mempengaruhi stok, kembalikan stok berdasarkan data original terlebih dahulu
        if ($supplierDebt->wasChanged(['product_id', 'quantity', 'transaction_type', 'stock_type'])) {
            // Kembalikan stok berdasarkan data original
            $this->revertOriginalStock($original);

            // Apply stok berdasarkan data terbaru
            $this->updateInventoryStock($supplierDebt, 'create');
        }
    }

    /**
     * Revert stock changes based on original data
     */
    private function revertOriginalStock(array $original): void
    {
        if (!$original['product_id'] || !$original['quantity']) {
            return;
        }

        $product = \App\Models\Product::find($original['product_id']);
        if (!$product) {
            return;
        }

        // Hitung stok yang harus dikembalikan
        $stockChange = 0;

        if ($original['transaction_type'] === 'piutang') {
            // Piutang original = stok harus dikembalikan (kebalikan dari pengurangan)
            $stockChange = $original['quantity'];
        } elseif ($original['transaction_type'] === 'hutang') {
            // Hutang original = stok harus dikurangi (kebalikan dari penambahan)
            $stockChange = -$original['quantity'];
        }

        if ($stockChange == 0) {
            return;
        }

        // Update stok berdasarkan jenis stok original
        $stockType = $original['stock_type'] ?? 'regular';

        if ($stockType === 'kongsi') {
            $product->stok_kongsi = max(0, ($product->stok_kongsi ?? 0) + $stockChange);
        } else {
            $product->stock = max(0, ($product->stock ?? 0) + $stockChange);
        }

        $product->save();
    }

    /**
     * Send notification about stock changes
     */
    private function sendStockNotification(SupplierDebt $supplierDebt, int $oldRegularStock, int $oldKongsiStock, int $newRegularStock, int $newKongsiStock, float $stockChange, string $action, string $stockTypeLabel): void
    {
        $productName = $supplierDebt->product->name ?? 'Produk';
        $changeText = $stockChange > 0 ? 'bertambah' : 'berkurang';
        $changeAmount = abs($stockChange);
        $transactionTypeLabel = $supplierDebt->transaction_type === 'hutang' ? 'Hutang' : 'Piutang';

        $title = match ($action) {
            'create' => "Stok Produk Diperbarui - {$transactionTypeLabel} Dibuat",
            'delete' => "Stok Produk Diperbarui - {$transactionTypeLabel} Dihapus",
            'restore' => "Stok Produk Diperbarui - {$transactionTypeLabel} Dipulihkan",
            default => 'Stok Produk Diperbarui',
        };

        $totalStock = $newRegularStock + $newKongsiStock;
        $body = "{$stockTypeLabel} {$productName} {$changeText} {$changeAmount} {$supplierDebt->unit}.\n" .
                "Stok Reguler: {$newRegularStock} {$supplierDebt->unit}\n" .
                "Stok Kongsi: {$newKongsiStock} {$supplierDebt->unit}\n" .
                "Total Stok: {$totalStock} {$supplierDebt->unit}";

        Notification::make()
            ->title($title)
            ->body($body)
            ->icon($stockChange > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
            ->color($stockChange > 0 ? 'success' : 'warning')
            ->send();
    }
}
