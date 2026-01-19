<?php

namespace Database\Seeders;

use App\Models\Promo;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus promo lama
        Promo::truncate();

        $products = Product::where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->command->info('Tidak ada produk aktif. Silakan tambah produk terlebih dahulu.');
            return;
        }

        // Promo tipe 1: Beli produk X dapat diskon
        foreach ($products->take(3) as $product) {
            $triggerUnit = ($product->price_per_kg > 0) ? 'kg' : 'qty';
            $triggerQuantity = ($product->price_per_kg > 0) ? 1 : 2;

            Promo::create([
                'name' => 'Tebus Murah ' . $product->name,
                'description' => 'Beli ' . $triggerQuantity . ' ' . $triggerUnit . ' ' . $product->name . ' dapat diskon 10%',
                'type' => 'buy_x_get_discount',
                'trigger_product_id' => $product->id,
                'trigger_quantity' => $triggerQuantity,
                'trigger_unit' => $triggerUnit,
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'apply_to_product_id' => $product->id,
                'max_discount_per_transaction' => null,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]);

            $this->command->info("✓ Promo 'Tebus Murah {$product->name}' ($triggerUnit) berhasil dibuat");
        }

        // Promo tipe 2: Minimum purchase (produk bebas, tidak perlu beli produk spesifik)
        if ($products->count() >= 2) {
            // Promo: Belanja 50rb dapat tebus produk kedua harga spesial
            $freeProduct1 = $products->get(1);
            Promo::create([
                'name' => 'Belanja 50rb Tebus ' . $freeProduct1->name,
                'description' => 'Belanja minimal Rp 50.000 dapat tebus ' . $freeProduct1->name . ' seharga Rp 15.000',
                'type' => 'buy_x_get_discount',
                'trigger_product_id' => null, // Tidak ada produk spesifik yang harus dibeli
                'trigger_quantity' => 1,
                'trigger_unit' => 'qty',
                'minimum_purchase' => 50000,
                'discount_type' => 'price',
                'discount_value' => 15000,
                'apply_to_product_id' => null,
                'free_product_id' => $freeProduct1->id,
                'free_quantity' => 1,
                'max_discount_per_transaction' => null,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]);
            $this->command->info("✓ Promo 'Belanja 50rb Tebus {$freeProduct1->name}' berhasil dibuat");

            // Promo: Belanja 100rb dapat tebus produk ketiga harga spesial
            if ($products->count() >= 3) {
                $freeProduct2 = $products->get(2);
                Promo::create([
                    'name' => 'Belanja 100rb Tebus ' . $freeProduct2->name,
                    'description' => 'Belanja minimal Rp 100.000 dapat tebus ' . $freeProduct2->name . ' seharga Rp 20.000',
                    'type' => 'buy_x_get_discount',
                    'trigger_product_id' => null, // Tidak ada produk spesifik yang harus dibeli
                    'trigger_quantity' => 1,
                    'trigger_unit' => 'qty',
                    'minimum_purchase' => 100000,
                    'discount_type' => 'price',
                    'discount_value' => 20000,
                    'apply_to_product_id' => null,
                    'free_product_id' => $freeProduct2->id,
                    'free_quantity' => 1,
                    'max_discount_per_transaction' => null,
                    'is_active' => true,
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                ]);
                $this->command->info("✓ Promo 'Belanja 100rb Tebus {$freeProduct2->name}' berhasil dibuat");
            }

            // Promo: Belanja 150rb diskon 15%
            Promo::create([
                'name' => 'Belanja 150rb Diskon 15%',
                'description' => 'Belanja minimal Rp 150.000 dapat diskon 15% untuk semua produk',
                'type' => 'buy_x_get_discount',
                'trigger_product_id' => null, // Tidak ada produk spesifik yang harus dibeli
                'trigger_quantity' => 1,
                'trigger_unit' => 'qty',
                'minimum_purchase' => 150000,
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'apply_to_product_id' => null,
                'free_product_id' => null,
                'free_quantity' => null,
                'max_discount_per_transaction' => null,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]);
            $this->command->info("✓ Promo 'Belanja 150rb Diskon 15%' berhasil dibuat");
        }

        $this->command->info("\n✅ Semua promo berhasil dibuat!");
    }
}
