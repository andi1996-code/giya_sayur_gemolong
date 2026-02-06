<?php

namespace App\Livewire;

use Filament\Forms;
use App\Models\User;
use App\Models\Product;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Category;
use Filament\Forms\Form;
use App\Models\Transaction;
use Livewire\WithPagination;
use App\Models\PaymentMethod;
use App\Models\TransactionItem;
use App\Helpers\TransactionHelper;
use App\Helpers\PromoHelper;
use App\Helpers\PointHelper;
use App\Models\Member;
use App\Services\DirectPrintService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class Pos extends Component
{
    use WithPagination {
        gotoPage as protected traitGotoPage;
        previousPage as protected traitPreviousPage;
        nextPage as protected traitNextPage;
    }

    public int | string $perPage = 10;
    public $categories;
    public $selectedCategory;
    public $search = '';
    public $print_via_bluetooth = false;
    public $barcode = '';
    public $name = 'Umum';
    public $payment_method_id;
    public $payment_methods;
    public $order_items = [];
    public $total_price = 0;
    public $promo_discount = 0;
    public $applied_promos = [];
    public $cash_received = '';
    public $change = 0;
    public $showConfirmationModal = false;
    public $showCheckoutModal = false;
    public $orderToPrint = null;
    public $is_cash = true;
    public $selected_payment_method = null;
    public bool $showWeightModal = false;
    public $weight_gram = '';
    public ?int $weightProductId = null;
    public $auto_print = false;

    // Member & Points properties
    public $member_code = '';
    public $selected_member = null;
    public $points_to_redeem = 0;
    public $points_discount = 0;
    public $showMemberModal = false;

    protected $listeners = [
        'scanResult' => 'handleScanResult',
        'memberSelected' => 'handleMemberSelected',
    ];

    public function mount()
    {
        $settings = Setting::first();
        $user = Auth::user();

        // Prioritas setting printer: user > global setting
        $this->print_via_bluetooth = $user->getEffectivePrintViaBluetooth();
        $this->auto_print = $user->getEffectiveAutoPrint();

        // Mengambil data kategori dan menambahkan data 'Semua' sebagai pilihan pertama
        $this->categories = collect([['id' => null, 'name' => 'Semua']])->merge(Category::all());

        // Jika session 'orderItems' ada, maka ambil data nya dan simpan ke dalam property $order_items
        // Session 'orderItems' digunakan untuk menyimpan data order sementara sebelum di checkout
        if (session()->has('orderItems')) {
            $orderItems = session('orderItems');
            // Ensure all items have required timestamp for sorting
            foreach ($orderItems as &$item) {
                if (!isset($item['added_at'])) {
                    $item['added_at'] = microtime(true);
                }
            }
            $this->order_items = $orderItems;
            $this->calculateTotal();
        }

        $this->payment_methods = PaymentMethod::all();

        // Set default payment method to "Tunai" (Cash)
        $cashMethod = PaymentMethod::where('name', 'Tunai')
            ->orWhere('name', 'Cash')
            ->orWhere('name', 'TUNAI')
            ->first();

        if ($cashMethod) {
            $this->payment_method_id = $cashMethod->id;
            $this->is_cash = true;
        }
    }

    public function render()
    {
        return view('livewire.pos', [
            'products' => Product::where(function($query) {
                    // Product dengan stok reguler atau stok kongsi yang > 0
                    $query->where('stock', '>', 0)
                          ->orWhere('stok_kongsi', '>', 0);
                })
                ->where('is_active', 1)
                ->when($this->selectedCategory !== null, function ($query) {
                    return $query->where('category_id', $this->selectedCategory);
                })
                ->where(function ($query) {
                    return $query->where('name', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('sku', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('barcode', 'LIKE', '%' . $this->search . '%');
                })
                ->paginate($this->perPage)
        ]);
    }


    public function updatedPaymentMethodId($value)
    {
        if ($value) {
            // Recalculate total terlebih dahulu untuk memastikan promo discount terhitung
            $this->calculateTotal();

            $paymentMethod = PaymentMethod::find($value);
            $this->selected_payment_method = $paymentMethod;
            $this->is_cash = $paymentMethod->is_cash ?? false;

            if (!$this->is_cash) {
                $this->cash_received = $this->total_price;
                $this->change = 0;
            } else {
                $this->calculateChange();
            }
        }
    }

    public function updatedCashReceived($value)
    {
        if ($this->is_cash) {
            // Remove thousand separator dots before calculation
            $this->cash_received = $value;
            $this->calculateChange();
        }
    }

    public function calculateChange()
    {
        // Remove thousand separator dots and convert to number
        $cleanValue = str_replace('.', '', $this->cash_received);
        $cashReceived = floatval($cleanValue);
        $totalPrice = floatval($this->total_price);

        if ($cashReceived >= $totalPrice) {
            $this->change = $cashReceived - $totalPrice;
        } else {
            $this->change = 0;
        }
    }

    // Helper method to get numeric value from formatted input
    public function getCashReceivedNumeric()
    {
        return floatval(str_replace('.', '', $this->cash_received));
    }

    public function updatedBarcode($barcode)
    {
        $product = Product::where('barcode', $barcode)
            ->where('is_active', true)->first();

        if ($product) {
            $this->addToOrder($product->id);
        } else {
            Notification::make()
                ->title('Product not found ' . $barcode)
                ->danger()
                ->send();
            // Focus back to barcode input even if product not found
            $this->dispatch('focus-barcode');
        }

        // Reset barcode
        $this->barcode = '';
    }

    public function handleScanResult($decodedText)
    {
        $product = Product::where('barcode', $decodedText)
            ->where('is_active', true)->first();

        if ($product) {
            $this->addToOrder($product->id);
        } else {
            Notification::make()
                ->title('Product not found ' . $decodedText)
                ->danger()
                ->send();
            // Focus back to barcode input even if product not found
            $this->dispatch('focus-barcode');
        }

        // Reset barcode
        $this->barcode = '';
    }

    public function setCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
        // Reset pagination page when category changes to ensure correct results
        $this->resetPage();
    }

    /**
     * Reset pagination to page 1 when search term changes
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Reset pagination to page 1 when selected category changes (for direct property updates)
     */
    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function addToOrder($productId)
    {
        $product = Product::find($productId);

        if ($product) {
            // If product sold by weight, prompt for weight input
            if (!empty($product->price_per_kg)) {
                $this->weightProductId = $product->id;
                $this->showWeightModal = true;
                return;
            }
            // Cari apakah item sudah ada di dalam order
            $existingItemKey = array_search($productId, array_column($this->order_items, 'product_id'));

            // Jika item sudah ada, tambahkan 1 quantity
            if ($existingItemKey !== false) {
                $totalAvailableStock = $product->getTotalAvailableStock();
                if ($this->order_items[$existingItemKey]['quantity'] >= $totalAvailableStock) {
                    Notification::make()
                        ->title('Stok barang tidak mencukupi')
                        ->body("Stok tersedia: {$product->getFormattedStock()}")
                        ->danger()
                        ->send();
                    return;
                } else {
                    $this->order_items[$existingItemKey]['quantity']++;
                }
            }
            // Jika item belum ada, tambahkan item baru ke dalam order
            else {
                // Get final price (with discount if active)
                $finalPrice = $product->getFinalPrice();

                $this->order_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $finalPrice, // Use discounted price
                    'original_price' => $product->price, // Store original price for reference
                    'discount_percentage' => $product->hasActiveDiscount() ? $product->discount_percentage : 0,
                    'cost_price' => $product->cost_price,
                    'total_profit' => $finalPrice - $product->cost_price,
                    'image_url' => $product->image,
                    'quantity' => 1,
                    'added_at' => microtime(true),
                ];
            }

            // Simpan perubahan order ke session
            session()->put('orderItems', $this->order_items);

            // Recalculate total
            $this->calculateTotal();

            // Recalculate change if cash payment
            if ($this->is_cash && !empty($this->cash_received)) {
                $this->calculateChange();
            }

            // Focus back to barcode input
            $this->dispatch('focus-barcode');
        }
    }

    public function confirmWeight()
    {
        // Find product and calculate price by weight
        $product = Product::find($this->weightProductId);
        $grams = floatval($this->weight_gram);
        $kilograms = $grams / 1000;

        // Check if stock is sufficient
        $totalAvailableStock = $product->getTotalAvailableStock();
        if ($kilograms > $totalAvailableStock) {
            Notification::make()
                ->title('Stok barang tidak mencukupi')
                ->body("Stok tersedia: {$product->getFormattedStock()}, Anda minta: " . number_format($kilograms, 3, ',', '.') . " kg")
                ->danger()
                ->send();
            return;
        }

        // Use discounted price per kg if available
        $unitPrice = $product->getFinalPricePerKg() ?? $product->price_per_kg;
        $calculatedPrice = round($unitPrice * $kilograms);

        // Calculate proportional cost price based on weight
        $costPricePerKg = $product->cost_price ?? 0;
        $proportionalCostPrice = round($costPricePerKg * $kilograms);

        // Add item to order with quantity 1, price based on weight
        $this->order_items[] = [
            'product_id' => $product->id,
            'name' => $product->name . " ({$grams}gr)",
            'price' => $calculatedPrice,
            'original_price' => round($product->price_per_kg * $kilograms), // Store original price for reference
            'discount_percentage' => $product->hasActiveDiscount() ? $product->discount_percentage : 0,
            'cost_price' => $proportionalCostPrice, // Use proportional cost price
            'total_profit' => $calculatedPrice - $proportionalCostPrice, // Calculate profit based on proportional cost
            'image_url' => $product->image,
            'quantity' => 1,
            'weight_grams' => $grams, // Store weight for display
            'weight_kg' => $kilograms, // Store weight in kg for stock calculation
            'unit_price' => $unitPrice, // Store discounted unit price for display
            'original_unit_price' => $product->price_per_kg, // Store original unit price
            'added_at' => microtime(true),
        ];
        // Reset modal state and recalculate
        $this->showWeightModal = false;
        $this->weight_gram = '';
        session()->put('orderItems', $this->order_items);
        $this->calculateTotal();
        if ($this->is_cash && !empty($this->cash_received)) {
            $this->calculateChange();
        }
    }

    public function loadOrderItems($orderItems)
    {
        // Ensure all items have required timestamp for sorting
        foreach ($orderItems as &$item) {
            if (!isset($item['added_at'])) {
                $item['added_at'] = microtime(true);
            }
        }
        $this->order_items = $orderItems;
        session()->put('orderItems', $orderItems);
    }

    public function increaseQuantity($product_id)
    {
        $product = Product::find($product_id);

        if (!$product) {
            Notification::make()
                ->title('Produk tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        // Loop setiap item yang ada di cart
        foreach ($this->order_items as $key => $item) {
            // Jika item yang sedang di-loop sama dengan item yang ingin di tambah
            if ($item['product_id'] == $product_id) {
                // Jika quantity item ditambah 1 masih kurang dari atau sama dengan total stok tersedia maka tambah 1 quantity
                $totalAvailableStock = $product->getTotalAvailableStock();
                if ($item['quantity'] + 1 <= $totalAvailableStock) {
                    $this->order_items[$key]['quantity']++;
                }
                // Jika quantity item yang ingin di tambah lebih besar dari total stok tersedia maka tampilkan notifikasi
                else {
                    Notification::make()
                        ->title('Stok barang tidak mencukupi')
                        ->body("Stok tersedia: {$product->getFormattedStock()}")
                        ->danger()
                        ->send();
                }
                // Berhenti loop karena item yang ingin di tambah sudah di temukan
                break;
            }
        }

        session()->put('orderItems', $this->order_items);

        // Recalculate total and change
        $this->calculateTotal();
        if ($this->is_cash && !empty($this->cash_received)) {
            $this->calculateChange();
        }
    }

    public function decreaseQuantity($product_id)
    {
        // Loop setiap item yang ada di cart
        foreach ($this->order_items as $key => $item) {
            // Jika item yang sedang di-loop sama dengan item yang ingin di kurangi
            if ($item['product_id'] == $product_id) {
                // Jika quantity item lebih dari 1 maka kurangi 1 quantity
                if ($this->order_items[$key]['quantity'] > 1) {
                    $this->order_items[$key]['quantity']--;
                }
                // Jika quantity item 1 maka hapus item dari cart
                else {
                    unset($this->order_items[$key]);
                    $this->order_items = array_values($this->order_items);
                }
                break;
            }
        }

        // Simpan perubahan cart ke session
        session()->put('orderItems', $this->order_items);

        // Recalculate total and change
        $this->calculateTotal();
        if ($this->is_cash && !empty($this->cash_received)) {
            $this->calculateChange();
        }
    }

    /**
     * Remove an item entirely from the cart.
     */
    public function removeItem($product_id)
    {
        foreach ($this->order_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($this->order_items[$key]);
                $this->order_items = array_values($this->order_items);
                break;
            }
        }
        session()->put('orderItems', $this->order_items);
        $this->calculateTotal();
        if ($this->is_cash && !empty($this->cash_received)) {
            $this->calculateChange();
        }
    }

    public function calculateTotal()
    {
        // Inisialisasi total harga
        $total = 0;

        // Loop setiap item yang ada di cart
        foreach ($this->order_items as $item) {
            // Tambahkan harga setiap item ke total
            $total += $item['quantity'] * $item['price'];
        }

        // Calculate promo discounts
        $promoResult = PromoHelper::calculatePromos($this->order_items);
        $this->applied_promos = $promoResult['promos'];
        $this->promo_discount = $promoResult['total_discount'];

        // Calculate points discount if member selected and points redeemed
        $this->points_discount = 0;
        if ($this->selected_member && $this->points_to_redeem > 0) {
            $validation = PointHelper::canRedeemPoints($this->selected_member, $this->points_to_redeem);
            if ($validation['can_redeem']) {
                $this->points_discount = $validation['discount_amount'];
            } else {
                $this->points_to_redeem = 0;
                Notification::make()
                    ->title('Poin tidak dapat ditukar')
                    ->body($validation['message'])
                    ->warning()
                    ->send();
            }
        }

        // Simpan total harga setelah diskon
        $this->total_price = max(0, $total - $this->promo_discount - $this->points_discount);

        // Simpan ke session untuk customer display
        session()->put('totalPrice', $total);
        session()->put('promoDiscount', $this->promo_discount);
        session()->put('pointsDiscount', $this->points_discount);
        session()->put('transactionName', $this->name);

        // Return total harga
        return $this->total_price;
    }

    public function resetOrder()
    {
        // Hapus semua session terkait
        session()->forget(['orderItems', 'name', 'payment_method_id', 'totalPrice', 'promoDiscount', 'pointsDiscount', 'transactionName']);

        // Reset variabel Livewire
        $this->order_items = [];
        $this->total_price = 0;
        $this->cash_received = '';
        $this->change = 0;
        $this->selected_payment_method = null;
        $this->name = 'Umum'; // Reset nama pelanggan ke default

        // Reset member & points
        $this->member_code = '';
        $this->selected_member = null;
        $this->points_to_redeem = 0;
        $this->points_discount = 0;

        // Set default payment method back to "Tunai" (Cash)
        $cashMethod = PaymentMethod::where('name', 'Tunai')
            ->orWhere('name', 'Cash')
            ->orWhere('name', 'TUNAI')
            ->first();

        if ($cashMethod) {
            $this->payment_method_id = $cashMethod->id;
            $this->is_cash = true;
        }
    }

    public function formatNumber($value)
    {
        return number_format($value, 0, ',', '.');
    }

    // Member & Points Methods
    public function searchMember()
    {
        if (empty($this->member_code)) {
            Notification::make()
                ->title('Masukkan kode member atau nomor telepon')
                ->warning()
                ->send();
            return;
        }

        $member = Member::where('is_active', true)
            ->where(function($query) {
                $query->where('member_code', $this->member_code)
                      ->orWhere('phone', $this->member_code);
            })
            ->first();

        if ($member) {
            $this->selected_member = $member;
            $this->name = $member->name;

            Notification::make()
                ->title('Member ditemukan')
                ->body("{$member->name} - {$member->tier_name} ({$member->total_points} poin)")
                ->success()
                ->send();

            $this->calculateTotal();
        } else {
            Notification::make()
                ->title('Member tidak ditemukan')
                ->body('Kode member atau nomor telepon tidak terdaftar')
                ->danger()
                ->send();
        }
    }

    public function clearMember()
    {
        $this->selected_member = null;
        $this->member_code = '';
        $this->name = 'Umum';
        $this->points_to_redeem = 0;
        $this->points_discount = 0;

        $this->calculateTotal();

        Notification::make()
            ->title('Member dihapus')
            ->success()
            ->send();
    }

    public function updatedPointsToRedeem($value)
    {
        if ($this->selected_member) {
            $this->calculateTotal();
        }
    }

    public function getMaxPointsRedeemProperty()
    {
        if (!$this->selected_member) {
            return 0;
        }

        $pointSettings = \App\Models\PointSetting::first();
        if (!$pointSettings || !isset($pointSettings->point_value) || (int)$pointSettings->point_value <= 0) {
            return 0;
        }

        // Maximum points that can be used (based on total price)
        $maxPointsBasedOnPrice = floor(($this->total_price + $this->points_discount) / (int)$pointSettings->point_value);

        // Maximum points member has
        $maxPointsAvailable = $this->selected_member->total_points;

        return min($maxPointsBasedOnPrice, $maxPointsAvailable);
    }

    public function useMaxPoints()
    {
        $this->points_to_redeem = $this->maxPointsRedeem;
        $this->calculateTotal();
    }

    public function handleMemberSelected($memberId)
    {
        $member = Member::find($memberId);
        if ($member) {
            $this->selected_member = $member;
            $this->member_code = $member->member_code;
            $this->name = $member->name;
            $this->showMemberModal = false;

            $this->calculateTotal();
        }
    }

    public function checkout()
    {
        // Convert formatted cash_received to numeric for validation
        $cashReceivedNumeric = $this->getCashReceivedNumeric();

        // Custom validation messages
        $messages = [
            'payment_method_id.required' => 'Metode pembayaran harus dipilih',
            'cash_received.required' => 'Nominal bayar harus diisi',
            'cash_received_numeric.min' => 'Nominal bayar kurang dari total belanja'
        ];

        // Base validation
        $this->validate([
            'name' => 'string|max:255',
            'payment_method_id' => 'required'
        ], $messages);

        // Additional validation for cash payment
        if ($this->is_cash) {
            if (empty($this->cash_received)) {
                $this->addError('cash_received', 'Nominal bayar harus diisi');
                return;
            }

            if ($cashReceivedNumeric < $this->total_price) {
                $this->addError('cash_received', 'Nominal bayar kurang dari total belanja');
                return;
            }
        }

        $payment_method_id_temp = $this->payment_method_id;
        $cash_received_value = $this->is_cash ? $cashReceivedNumeric : $this->total_price;

        // Sync session dari memory sebelum validasi, agar tidak terjadi desinkronisasi
        if (!empty($this->order_items)) {
            session()->put('orderItems', $this->order_items);
        }

        // Gunakan $this->order_items sebagai source of truth utama
        if (empty($this->order_items)) {
            Notification::make()
                ->title('Keranjang kosong')
                ->danger()
                ->send();

            $this->showCheckoutModal = false;
        } else {
            $order = Transaction::create([
                'payment_method_id' => $payment_method_id_temp,
                'member_id' => $this->selected_member ? $this->selected_member->id : null,
                'transaction_number' => TransactionHelper::generateUniqueTrxId(),
                'name' => $this->name,
                'total' => $this->total_price,
                'cash_received' => $cash_received_value,
                'change' => $this->change,
                'promo_discount' => $this->promo_discount,
                'points_earned' => 0,
                'points_redeemed' => 0,
                'points_discount' => 0,
            ]);

            foreach ($this->order_items as $item) {
                TransactionItem::create([
                    'transaction_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'total_profit' => $item['total_profit'] * $item['quantity'],
                    'weight' => isset($item['weight_kg']) ? $item['weight_kg'] : (isset($item['weight_grams']) ? $item['weight_grams'] / 1000 : null),
                ]);
            }

            // Process member points if applicable
            if ($this->selected_member) {
                $subtotal = $this->total_price + $this->promo_discount;
                $pointResult = PointHelper::processTransactionPoints(
                    $this->selected_member,
                    $subtotal,
                    $this->points_to_redeem,
                    $order->id
                );

                // Update transaction with points info
                $order->update([
                    'points_earned' => $pointResult['points_earned'],
                    'points_redeemed' => $pointResult['points_redeemed'],
                    'points_discount' => $pointResult['points_discount'],
                ]);
            }

            // Simpan ID order untuk cetak
            $this->orderToPrint = $order->id;

            // Siapkan data struk untuk customer display
            $receiptData = [
                'transaction_number' => $order->transaction_number,
                'date' => $order->created_at->format('d-m-Y H:i:s'),
                'items' => $this->order_items,
                'subtotal' => $this->total_price + $this->promo_discount,
                'promo_discount' => $this->promo_discount,
                'total' => $this->total_price,
                'cash_received' => $cash_received_value,
                'change' => $this->change,
            ];

            // Simpan ke session untuk customer display
            session()->put('completedTransaction', $receiptData);

            // Auto print based on settings
            $this->showConfirmationModal = false;
            $this->showCheckoutModal = false;

            // Choose print method based on settings
            if ($this->print_via_bluetooth) {
                // Print via Bluetooth
                $this->printBluetooth();
                Notification::make()
                    ->title('Transaksi berhasil & struk dicetak via Bluetooth')
                    ->success()
                    ->duration(2000)
                    ->send();
            } else {
                // Print via USB Print Service
                $this->printUSB();
                Notification::make()
                    ->title('Transaksi berhasil & struk dicetak via USB')
                    ->success()
                    ->duration(2000)
                    ->send();
            }


            $this->resetOrder();
        }
    }

    // ⚠️ DISABLED - Cable printing has errors, use Bluetooth only
    // public function printLocalKabel()
    // {
    //     $directPrint = app(DirectPrintService::class);
    //     $directPrint->print($this->orderToPrint);
    //     $this->showConfirmationModal = false;
    //     $this->orderToPrint = null;
    // }

    public function printBluetooth()
    {
        $order = Transaction::with(['paymentMethod', 'transactionItems.product', 'member'])->findOrFail($this->orderToPrint);
        $items = $order->transactionItems;

        // Refresh member data to get updated points
        if ($order->member) {
            $order->member->refresh();
        }

        $this->dispatch(
            'doPrintReceipt',
            store: Setting::first(),
            order: $order,
            items: $items,
            date: $order->created_at->format('d-m-Y H:i:s'),
            cashier: Auth::user(),
            member: $order->member // Include member data if exists
        );

        // Don't reset here, will be reset after checkout
        // $this->showConfirmationModal = false;
        // $this->orderToPrint = null;
    }

    /**
     * Print receipt using USB Print Service (Cable/USB Printer)
     * Sends print job to Python Flask server which handles ESC/POS printing
     */
    public function printUSB()
    {
        $order = Transaction::with(['paymentMethod', 'transactionItems.product', 'member'])->findOrFail($this->orderToPrint);
        $items = $order->transactionItems;
        $settings = Setting::first();

        // Refresh member data to get updated points
        if ($order->member) {
            $order->member->refresh();
        }

        // Prepare receipt data (LENGKAP sama seperti Bluetooth)
        $receiptData = [
            'store' => [
                'name' => $settings->name,
                'address' => $settings->address,
                'phone' => $settings->phone,
                'website' => $settings->website ?? '',
                'receipt_footer_line1' => $settings->receipt_footer_line1 ?? '',
                'receipt_footer_line2' => $settings->receipt_footer_line2 ?? '',
                'receipt_footer_line3' => $settings->receipt_footer_line3 ?? '',
                'receipt_footer_note' => $settings->receipt_footer_note ?? '',
                'show_footer_thank_you' => $settings->show_footer_thank_you ?? true,
            ],
            'order' => [
                'transaction_number' => $order->transaction_number,
                'payment_method' => [
                    'name' => $order->paymentMethod->name,
                ],
                'subtotal' => $order->subtotal,
                'promo_discount' => $order->promo_discount ?? 0,
                'points_discount' => $order->points_discount ?? 0,
                'total' => $order->total,
                'cash_received' => $order->cash_received ?? $order->total,
                'change' => $order->change ?? 0,
                'points_earned' => $order->points_earned ?? 0,
                'points_redeemed' => $order->points_redeemed ?? 0,
            ],
            'items' => $items->map(function ($item) {
                return [
                    'product' => [
                        'name' => $item->product ? $item->product->name : $item->product_name,
                    ],
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'weight' => $item->weight ?? 0,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ];
            })->toArray(),
            'date' => $order->created_at->format('d-m-Y H:i:s'),
            'cashier' => [
                'name' => Auth::user()->name,
            ],
            'member' => $order->member ? [
                'name' => $order->member->name,
                'member_code' => $order->member->member_code,
                'tier' => $order->member->tier,
                'points' => $order->member->points ?? 0,
                'total_points' => $order->member->total_points ?? 0,
            ] : null,
            // Printer name: prioritas user > global setting
            'printerName' => Auth::user()->getEffectivePrinterName(),
        ];

        // Dispatch event to USB Print Service
        $this->dispatch('doPrintReceiptUSB', $receiptData);

        // Don't reset here, will be reset after checkout
        // $this->showConfirmationModal = false;
        // $this->orderToPrint = null;
    }

    // --- Temporary debug overrides for pagination ---
    // These will show a Filament notification when pagination methods are invoked.
    // Remove these after debugging.
    public function previousPage($pageName = 'page')
    {
        // Call original WithPagination implementation
        $this->traitPreviousPage($pageName);
        // Visual and browser-side debug events
        Notification::make()->title('Pagination: previousPage called')->success()->send();
        $this->dispatch('pagination-called', method: 'previousPage', pageName: $pageName);
    }

    public function nextPage($pageName = 'page')
    {
        $this->traitNextPage($pageName);
        Notification::make()->title('Pagination: nextPage called')->success()->send();
        $this->dispatch('pagination-called', method: 'nextPage', pageName: $pageName);
    }

    public function gotoPage($page, $pageName = 'page')
    {
        $this->traitGotoPage($page, $pageName);
        Notification::make()->title("Pagination: gotoPage({$page}) called")->success()->send();
        $this->dispatch('pagination-called', method: 'gotoPage', page: $page, pageName: $pageName);
    }
}

