<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();

        if (!$user) {
            $this->command->error('Tidak ada user untuk seeder cash flow');
            return;
        }

        $cashFlows = [
            [
                'type' => 'income',
                'amount' => 5000000,
                'source' => 'Modal',
                'notes' => 'Modal awal toko sayur dan buah',
                'date' => now()->subDays(30),
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ],
            [
                'type' => 'expense',
                'amount' => 1500000,
                'source' => 'Pembelian Stok',
                'notes' => 'Pembelian stok sayuran dari supplier',
                'date' => now()->subDays(7),
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'type' => 'income',
                'amount' => 2850000,
                'source' => 'Penjualan',
                'notes' => 'Penjualan sayur dan buah minggu ini',
                'date' => now()->subDays(1),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        foreach ($cashFlows as $cashFlow) {
            \App\Models\CashFlow::create($cashFlow);
        }

        $this->command->info('✅ Berhasil membuat 3 data cash flow (masuk dan keluar)!');
    }
}
