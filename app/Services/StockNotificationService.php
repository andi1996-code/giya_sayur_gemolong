<?php

namespace App\Services;

use App\Models\Product;
use Filament\Notifications\Notification;

class StockNotificationService
{
    public static function checkLowStock(): void
    {
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->get();

        $outOfStockProducts = Product::where('stock', '=', 0)
            ->where('is_active', true)
            ->get();

        if ($lowStockProducts->count() > 0) {
            Notification::make()
                ->warning()
                ->title('Peringatan Stok Menipis!')
                ->body($lowStockProducts->count() . ' produk memiliki stok ≤ 5 unit')
                ->icon('heroicon-o-exclamation-triangle')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->button()
                        ->label('Lihat Detail')
                        ->url(route('filament.admin.resources.products.index', [
                            'tableFilters' => [
                                'stock' => [
                                    'max' => 5
                                ]
                            ]
                        ])),
                ])
                ->persistent()
                ->send();
        }

        if ($outOfStockProducts->count() > 0) {
            Notification::make()
                ->danger()
                ->title('Peringatan Stok Habis!')
                ->body($outOfStockProducts->count() . ' produk kehabisan stok')
                ->icon('heroicon-o-x-circle')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('restock')
                        ->button()
                        ->label('Restok Sekarang')
                        ->url(route('filament.admin.resources.products.index')),
                ])
                ->persistent()
                ->send();
        }
    }

    public static function getLowStockBadgeCount(): int
    {
        return Product::where('stock', '<=', 5)
            ->where('is_active', true)
            ->count();
    }
}
