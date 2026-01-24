<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateBarcodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:duplicate-barcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus barcode duplikat, keep hanya yang pertama (oldest)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Mencari barcode yang duplikat...');

        // Find all products dengan barcode duplikat
        $duplicates = DB::select("
            SELECT barcode, COUNT(*) as count
            FROM products
            WHERE barcode IS NOT NULL AND barcode != ''
            GROUP BY barcode
            HAVING COUNT(*) > 1
            ORDER BY barcode
        ");

        if (empty($duplicates)) {
            $this->info('✅ Tidak ada barcode yang duplikat!');
            return 0;
        }

        $this->table(
            ['Barcode', 'Jumlah Duplikat'],
            array_map(fn($d) => [$d->barcode, $d->count], $duplicates)
        );

        // Confirm action
        if (!$this->confirm('Lanjutkan hapus barcode duplikat? (Keep only yang pertama/oldest)', true)) {
            $this->info('Dibatalkan.');
            return 0;
        }

        $totalRemoved = 0;

        foreach ($duplicates as $dup) {
            $barcode = $dup->barcode;

            // Get all products dengan barcode ini, ordered by created_at (keep yang pertama)
            $products = DB::select("
                SELECT id, name, created_at
                FROM products
                WHERE barcode = ?
                ORDER BY created_at ASC
            ", [$barcode]);

            // Keep yang pertama (index 0), hapus yang lainnya
            for ($i = 1; $i < count($products); $i++) {
                $productId = $products[$i]->id;
                $productName = $products[$i]->name;

                // Set barcode ke NULL untuk produk duplikat
                DB::table('products')
                    ->where('id', $productId)
                    ->update(['barcode' => null]);

                $this->line("  ❌ <fg=yellow>ID {$productId}</> - {$productName} (barcode dihapus)");
                $totalRemoved++;
            }

            $keptProduct = $products[0];
            $this->line("  ✅ <fg=green>ID {$keptProduct->id}</> - {$keptProduct->name} (barcode dipertahankan)");
            $this->line('');
        }

        $this->info("✨ Selesai! Hapus {$totalRemoved} barcode duplikat.");
        return 0;
    }
}
