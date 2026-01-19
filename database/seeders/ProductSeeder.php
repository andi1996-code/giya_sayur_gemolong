<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('products')->insert([
            // Sayuran
            [
                'category_id' => 2, 'name' => 'Bayam Segar 250gr', 'stock' => 0, 'cost_price' => 3000, 'price' => 5000, 'sku' => 'SV-BYM-250', 'barcode' => '8990001110001', 'description' => 'Sayur bayam segar lokal.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 2, 'name' => 'Wortel Segar 250gr', 'stock' => 0, 'cost_price' => 4000, 'price' => 7000, 'sku' => 'SV-WRT-250', 'barcode' => '8990001110002', 'description' => 'Sayur wortel segar.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            // Buah-buahan
            [
                'category_id' => 1, 'name' => 'Apel Merah 1kg', 'stock' => 0, 'cost_price' => 15000, 'price' => 20000, 'sku' => 'BR-APL-1KG', 'barcode' => '8990001110003', 'description' => 'Buah apel merah manis.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 1, 'name' => 'Pisang Raja 1kg', 'stock' => 0, 'cost_price' => 12000, 'price' => 18000, 'sku' => 'BR-PIS-1KG', 'barcode' => '8990001110004', 'description' => 'Buah pisang raja segar.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 1, 'name' => 'Jeruk Manis 1kg', 'stock' => 0, 'cost_price' => 10000, 'price' => 15000, 'sku' => 'BR-JRK-1KG', 'barcode' => '8990001110005', 'description' => 'Buah jeruk manis segar.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }
}
