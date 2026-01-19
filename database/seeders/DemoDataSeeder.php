<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai seeder data demo...');

        // 1. Produk sayur dan buah (5 produk)
        $this->command->info('📦 Seeding produk sayur dan buah...');
        $this->call(ProductSayurBuahSeeder::class);

        // 2. Piutang supplier (3 data: lunas, sebagian lunas, belum lunas)
        $this->command->info('💰 Seeding piutang supplier...');
        $this->call(SupplierDebtSeeder::class);

        // 3. Cash flow (3 data: masuk dan keluar)
        $this->command->info('💸 Seeding cash flow...');
        $this->call(CashFlowSeeder::class);

        $this->command->info('✅ Seeder demo data berhasil!');
        $this->command->info('📋 Yang sudah dibuat:');
        $this->command->info('   - 5 produk sayur & buah dengan stok decimal');
        $this->command->info('   - 3 piutang supplier (berbagai status)');
        $this->command->info('   - 3 cash flow (masuk & keluar)');
    }
}
