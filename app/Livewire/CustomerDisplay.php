<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use App\Models\Setting;

#[Layout('layouts.blank')]
class CustomerDisplay extends Component
{
    public $order_items = [];
    public $total_price = 0;
    public $promo_discount = 0;
    public $final_total = 0;
    public $transaction_name = 'Umum';
    public $lastTransaction = null;
    public $showReceipt = false;
    public $receiptData = null;

    // Store info
    public $storeName;
    public $storeAddress;
    public $storeLogo;
    public $customerDisplayImage;

    #[On('public')]
    public function mount()
    {
        $this->loadStoreInfo();
        $this->loadCartData();
    }

    public function render()
    {
        return view('livewire.customer-display');
    }

    // Load store information from settings
    public function loadStoreInfo()
    {
        $setting = Setting::first();
        if ($setting) {
            $this->storeName = $setting->name ?? 'Nama Toko';
            $this->storeAddress = $setting->address ?? '';
            $this->storeLogo = $setting->logo ? asset('storage/' . $setting->logo) : null;
            $this->customerDisplayImage = $setting->customer_display_image ? asset('storage/' . $setting->customer_display_image) : null;
        } else {
            $this->storeName = 'Nama Toko';
            $this->storeAddress = '';
            $this->storeLogo = null;
            $this->customerDisplayImage = null;
        }
    }

    // Polling setiap 1 detik untuk update data
    #[On('public')]
    public function loadCartData()
    {
        // Mengambil data dari session POS
        if (session()->has('orderItems')) {
            $this->order_items = session('orderItems');
        } else {
            $this->order_items = [];
        }

        if (session()->has('totalPrice')) {
            $this->total_price = session('totalPrice');
        } else {
            $this->total_price = 0;
        }

        if (session()->has('promoDiscount')) {
            $this->promo_discount = session('promoDiscount');
        } else {
            $this->promo_discount = 0;
        }

        if (session()->has('transactionName')) {
            $this->transaction_name = session('transactionName');
        } else {
            $this->transaction_name = 'Umum';
        }

        $this->final_total = $this->total_price - $this->promo_discount;

        // Check apakah ada transaksi baru yang selesai
        if (session()->has('completedTransaction')) {
            $this->receiptData = session('completedTransaction');
            $this->showReceipt = true;

            // Hapus dari session agar tidak tampil terus-menerus
            session()->forget('completedTransaction');

            // Schedule auto hide setelah 5 detik menggunakan Livewire delay
            $this->dispatch('scheduleHideReceipt');
        }
    }

    #[On('hideReceiptAfterDelay')]
    public function hideReceiptAfter()
    {
        // Method ini dipanggil setelah 5 detik
        $this->hideReceipt();
    }

    public function hideReceipt()
    {
        $this->showReceipt = false;
        $this->receiptData = null;
    }
}
