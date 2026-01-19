<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SupplierDebt;
use App\Models\User;

class SupplierDebtSeeder extends Seeder
{
    /**
     * Run the database migrations.
     */
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            return;
        }

        // Ambil produk untuk referensi
        $bayam = \App\Models\Product::where('name', 'LIKE', '%bayam%')->first(); // Stok reguler
        $kangkung = \App\Models\Product::where('name', 'LIKE', '%kangkung%')->first(); // Stok kongsi
        $apel = \App\Models\Product::where('name', 'LIKE', '%apel%')->first(); // Stok kongsi

        // Jika tidak ada produk, buat produk dummy
        if (!$bayam) {
            $kategori = \App\Models\Category::first() ?? \App\Models\Category::create(['name' => 'Umum']);
            $bayam = \App\Models\Product::create([
                'category_id' => $kategori->id,
                'name' => 'Bayam Test',
                'stock' => 0,
                'stok_kongsi' => 0,
                'cost_price' => 8000,
                'price_per_kg' => 12000,
                'is_active' => true,
            ]);
        }        $supplierDebts = [
            // Hutang untuk produk STOK REGULER (Bayam - milik sendiri)
            [
                'supplier_name' => 'CV. Sayur Segar',
                'supplier_phone' => '081234567890',
                'supplier_address' => 'Jl. Pasar Induk No. 15, Jakarta Timur',
                'transaction_type' => 'hutang',
                'amount' => 2500000,
                'paid_amount' => 1000000,
                'remaining_amount' => 1500000,
                'product_id' => $bayam->id,
                'quantity' => 23.750,
                'unit' => 'kg',
                'stock_type' => 'regular',
                'description' => 'Pembelian bayam segar untuk stok milik sendiri',
                'transaction_date' => now()->subDays(7),
                'due_date' => now()->addDays(23),
                'status' => 'sebagian_lunas',
                'notes' => 'Cicilan pertama sudah dibayar Rp 1.000.000',
                'user_id' => $user->id,
            ],
            // Piutang untuk produk STOK KONGSI (Kangkung - titipan)
            [
                'supplier_name' => 'PT. Fresh Vegetables',
                'supplier_phone' => '081987654321',
                'supplier_address' => 'Jl. Raya Bogor Km. 25, Bogor',
                'transaction_type' => 'piutang',
                'amount' => 800000,
                'paid_amount' => 0,
                'remaining_amount' => 800000,
                'product_id' => $kangkung ? $kangkung->id : $bayam->id,
                'quantity' => 25.500,
                'unit' => 'kg',
                'stock_type' => 'kongsi',
                'description' => 'Penjualan kangkung organik sistem konsinyasi',
                'transaction_date' => now()->subDays(3),
                'due_date' => now()->addDays(12),
                'status' => 'belum_lunas',
                'notes' => 'Belum ada pembayaran dari supplier',
                'user_id' => $user->id,
            ],
            // Hutang untuk produk STOK KONGSI (Apel - titipan) - LUNAS
            [
                'supplier_name' => 'Petani Apel Malang',
                'supplier_phone' => '081555666777',
                'supplier_address' => 'Desa Batu, Malang',
                'transaction_type' => 'hutang',
                'amount' => 1200000,
                'paid_amount' => 1200000,
                'remaining_amount' => 0,
                'product_id' => $apel ? $apel->id : $bayam->id,
                'quantity' => 18.250,
                'unit' => 'kg',
                'stock_type' => 'kongsi',
                'description' => 'Konsinyasi apel malang dari petani',
                'transaction_date' => now()->subDays(15),
                'due_date' => now()->subDays(1),
                'status' => 'lunas',
                'notes' => 'Lunas dibayar tunai tanggal ' . now()->subDays(14)->format('d M Y'),
                'user_id' => $user->id,
            ],
        ];

        foreach ($supplierDebts as $debt) {
            SupplierDebt::create($debt);
        }
    }
}
