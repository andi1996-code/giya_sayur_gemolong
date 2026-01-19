<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockAlert extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Ambil produk dengan stok <= 5 (batas stok menipis)
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->get();

        $outOfStockProducts = Product::where('stock', '=', 0)
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Stok Menipis', $lowStockProducts->count())
                ->description('Produk dengan stok ≤ 5')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color($lowStockProducts->count() > 0 ? 'warning' : 'success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->url(route('filament.admin.resources.inventories.index')),

            Stat::make('Stok Habis', $outOfStockProducts)
                ->description('Produk dengan stok 0')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStockProducts > 0 ? 'danger' : 'success')
                ->chart([2, 1, 3, 2, 1, 0, 1, 2])
                ->url(route('filament.admin.resources.inventories.index')),

            Stat::make('Total Produk Aktif', Product::where('is_active', true)->count())
                ->description('Semua produk aktif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
