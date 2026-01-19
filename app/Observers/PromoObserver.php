<?php

namespace App\Observers;

use App\Models\Promo;
use Illuminate\Support\Facades\Cache;

class PromoObserver
{
    public function created(Promo $promo): void
    {
        Cache::forget('active_promos');
    }

    public function updated(Promo $promo): void
    {
        Cache::forget('active_promos');
    }

    public function deleted(Promo $promo): void
    {
        Cache::forget('active_promos');
    }

    public function restored(Promo $promo): void
    {
        Cache::forget('active_promos');
    }

    public function forceDeleted(Promo $promo): void
    {
        Cache::forget('active_promos');
    }
}
