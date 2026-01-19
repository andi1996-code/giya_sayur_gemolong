<?php

namespace App\Providers;

use App\Models\Report;
use App\Models\Promo;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Transaction;
use App\Models\InventoryItem;
use App\Models\TransactionItem;
use App\Models\SupplierDebt;
use Filament\Support\Assets\Js;
use App\Observers\ReportObserver;
use App\Observers\PromoObserver;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;
use App\Observers\InventoryObserver;
use App\Observers\TransactionObserver;
use App\Observers\SupplierDebtObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\InventoryItemObserver;
use App\Observers\TransactionItemObserver;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inventory::observe(InventoryObserver::class);
        InventoryItem::observe(InventoryItemObserver::class);
        TransactionItem::observe(TransactionItemObserver::class);
        Transaction::observe(TransactionObserver::class);
        Category::observe(CategoryObserver::class);
        Product::observe(ProductObserver::class);
        Report::observe(ReportObserver::class);
        SupplierDebt::observe(SupplierDebtObserver::class);
        Promo::observe(PromoObserver::class);

        FilamentAsset::register([
            Js::make('usb-print-service', asset('js/usb-print-service.js')),
            Js::make('printer-thermal', asset('js/printer-thermal.js'))
        ]);

        // Register render hook untuk popup sukses reward redemption
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render("@include('filament.partials.reward-redemption-success-popup')")
        );


    }
}
