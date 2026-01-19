<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DecimalStockTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing products with decimal stock for testing
        \App\Models\Product::where('name', 'LIKE', '%apel%')
            ->orWhere('name', 'LIKE', '%jeruk%')
            ->orWhere('name', 'LIKE', '%pisang%')
            ->update([
                'stock' => 25.5,  // 25.5 kg stock reguler
                'stok_kongsi' => 15.750,  // 15.75 kg stock kongsi
            ]);

        $this->command->info('Updated products with decimal stock for testing (25.5kg regular + 15.75kg kongsi = 41.25kg total)');
    }
}
