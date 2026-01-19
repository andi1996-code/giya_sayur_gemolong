<?php

namespace App\Observers;

use App\Models\InventoryItem;
use Illuminate\Support\Facades\Log;

class InventoryItemObserver
{
    public function created(InventoryItem $item)
    {
        $type = $item->inventory->type;
        $source = $item->inventory->source;

        if ($type === 'in') {
            // Tambah stok
            $item->product->increment('stock', $item->quantity);
        } elseif ($type === 'out') {
            // Kurangi stok
            $item->product->decrement('stock', $item->quantity);
        } elseif ($type === 'shrinkage') {
            // Produk menyusut: kurangi stok
            Log::info("Shrinkage: Mengurangi stok produk {$item->product->id} sebesar {$item->quantity}");
            $item->product->decrement('stock', $item->quantity);
        } elseif ($type === 'adjustment') {
            // Penyesuaian langsung (stock opname)
            $item->product->update(['stock' => $item->quantity]);
        }
    }

    public function updated(InventoryItem $item)
    {
        $type = $item->inventory->type;
        $originalQty = $item->getOriginal('quantity');
        $newQty = $item->quantity;

        $product = $item->product;

        if ($type === 'in') {
            $product->increment('stock', $newQty - $originalQty);
        } elseif ($type === 'out') {
            $product->decrement('stock', $newQty - $originalQty);
        } elseif ($type === 'shrinkage') {
            // Produk menyusut: sesuaikan stok berdasarkan selisih
            $difference = $newQty - $originalQty;
            if ($difference > 0) {
                // penurunan stok
                $product->decrement('stock', $difference);
                Log::info("Shrinkage updated: Mengurangi stok produk {$product->id} dengan selisih {$difference}");
            } elseif ($difference < 0) {
                // jika quantity ditekan, kembalikan stok
                $product->increment('stock', abs($difference));
                Log::info("Shrinkage updated: Mengembalikan stok produk {$product->id} dengan selisih " . abs($difference));
            }
        } elseif ($type === 'adjustment') {
            $product->update(['stock' => $newQty]);
        }
    }

    public function deleted(InventoryItem $item)
    {
        $type = $item->inventory->type;

        if ($type === 'in') {
            $item->product->decrement('stock', $item->quantity);
        } elseif ($type === 'out') {
            $item->product->increment('stock', $item->quantity);
        } elseif ($type === 'shrinkage') {
            // Produk menyusut dibatalkan: kembalikan stok
            $item->product->increment('stock', $item->quantity);
            Log::info("Shrinkage deleted: Mengembalikan stok produk {$item->product->id} sebesar {$item->quantity}");
        } elseif ($type === 'adjustment') {
            // Tidak bisa dikembalikan otomatis karena tidak tahu stok sebelumnya
            // Bisa log saja
            Log::warning("Item adjustment dihapus: Tidak dapat mengembalikan stok secara akurat.");
        }
    }


}
