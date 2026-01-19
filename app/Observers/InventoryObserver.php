<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\CashFlow;

class InventoryObserver
{
    public function creating(Inventory $inventory): void
    {
        // Generate nomor referensi berbentuk INV-YYYYMMDD-001, INV-YYYYMMDD-002, dst
        // Contoh: INV-20221201-01, INV-20221201-02
        $today = now()->format('Ymd');
        $countToday = Inventory::whereDate('created_at', today())
            ->count() + 1;

        $inventory->reference_number = 'INV-' . $today . '-' . str_pad($countToday, 2, '0', STR_PAD_LEFT);
    }

    public function created(Inventory $inventory): void
    {
        // Contoh: jika type 'in' dan source adalah purchase_stock, catat pengeluaran ke CashFlow
        if ($inventory->type === 'in' && $inventory->source === 'purchase_stock') {
            $notes = 'Otomatis dari penambahan stok Inventory dengan Nomor Referensi: ' . $inventory->reference_number;

            // Tambahkan notes dari inventory jika ada
            if (!empty($inventory->notes)) {
                $notes .= ' - ' . $inventory->notes;
            }

            CashFlow::create([
                'date' => now(),
                'type' => 'expense',
                'source' => 'purchase_stock',
                'amount' => $inventory->total,
                'notes' => $notes,
            ]);
        }

    }


    public function updated(Inventory $inventory): void
    {
        // Perbarui CashFlow terkait jika total atau notes berubah
        if ($inventory->isDirty('total') || $inventory->isDirty('notes')) {
            $updateData = [];

            if ($inventory->isDirty('total')) {
                $updateData['amount'] = $inventory->total;
            }

            if ($inventory->isDirty('notes')) {
                $updateData['notes'] = 'Otomatis dari penambahan stok Inventory dengan Nomor Referensi: ' . $inventory->reference_number . ' - ' . $inventory->notes;
            }

            if (!empty($updateData)) {
                CashFlow::where('notes', 'like', "%Nomor Referensi: {$inventory->reference_number}%")
                    ->update($updateData);
            }
        }
    }


    public function deleted(Inventory $inventory): void
    {
        // Misalnya hapus CashFlow terkait
        CashFlow::where('notes', 'like', "%Nomor Referensi: {$inventory->reference_number}%")->delete();

        if ($inventory->type === 'in') {
            foreach ($inventory->inventoryItems as $item) {
                $product = $item->product;
                $product->stock -= $item->quantity;
                $product->save();
            }
        } elseif ($inventory->type === 'out') {
            foreach ($inventory->inventoryItems as $item) {
                $product = $item->product;
                $product->stock += $item->quantity;
                $product->save();
            }
        }
    }

}
