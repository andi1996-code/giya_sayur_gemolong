<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\Widget;

class StockNotificationWidget extends Widget
{
    protected static string $view = 'filament.widgets.stock-notification';
    protected static ?int $sort = -10;
    protected static bool $isLazy = false;

    public function getLowStockCount(): int
    {
        return Product::where('stock', '<=', 5)
            ->where('is_active', true)
            ->count();
    }

    public function getOutOfStockCount(): int
    {
        return Product::where('stock', '=', 0)
            ->where('is_active', true)
            ->count();
    }

    public function getLowStockProducts()
    {
        return Product::where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->with('category')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();
    }

    public function getOutOfStockProducts()
    {
        return Product::where('stock', '=', 0)
            ->where('is_active', true)
            ->with('category')
            ->limit(5)
            ->get();
    }
}
