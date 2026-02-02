<div class="min-h-screen pt-0 px-2 sm:px-4 lg:px-6" style="font-family: 'Poppins'">
    <div class="max-w-[1600px] mx-auto -mt-6" x-data="{
        showSearchForm: false,
        showOrderSummary: false,
        skipBarcodeFocus: false,
        init() {
            // Watch for any Livewire updates and refocus barcode input (only if search form is not open)
            this.$watch('$wire.order_items', () => {
                setTimeout(() => {
                    if (this.skipBarcodeFocus) {
                        this.skipBarcodeFocus = false;
                        return;
                    }

                    // Jangan fokus ke barcode input jika form pencarian sedang terbuka
                    if (!this.showSearchForm) {
                        const input = document.querySelector('#barcode');
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    }
                }, 150);
            });

            // Watch for search form toggle and auto focus
            this.$watch('showSearchForm', (value) => {
                if (value) {
                    setTimeout(() => {
                        const searchInput = document.querySelector('#search-product');
                        if (searchInput) searchInput.focus();
                    }, 150);
                }
            });

            // Keyboard shortcut: Ctrl+K or Cmd+K to toggle search form
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    this.showSearchForm = !this.showSearchForm;
                    if (!this.showSearchForm) {
                        // Fokus ke barcode input saat form ditutup
                        setTimeout(() => {
                            const barcodeInput = document.querySelector('#barcode');
                            if (barcodeInput) {
                                barcodeInput.focus();
                                barcodeInput.select();
                            }
                        }, 150);
                    }
                }

                if ((e.ctrlKey || e.metaKey) && (e.key === 'm' || e.key === 'M')) {
                    e.preventDefault();
                    this.skipBarcodeFocus = true;
                    const memberInput = document.querySelector('#member-input');
                    if (memberInput) {
                        memberInput.focus();
                        memberInput.select();
                    }
                    setTimeout(() => {
                        this.skipBarcodeFocus = false;
                    }, 400);
                }

                if (e.key === 'End') {
                    e.preventDefault();
                    const cashInput = document.querySelector('#cash-received-input');
                    if (cashInput) {
                        cashInput.focus();
                        cashInput.select();
                    }
                }
            });
        }
    }">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- Main Item Section -->
            <div class="lg:col-span-2">
                <!-- Header Section with Barcode Input -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-3 sm:p-4 lg:p-6 mb-4 lg:mb-6">
                    <!-- Tombol Customer Display dan Search -->
                    <div class="mb-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 sm:gap-3">
                        <div class="flex items-center gap-2">
                            <x-filament::button onclick="window.location='/transactions'"
                                class="px-2 w-12 h-12 bg-primary text-white rounded-lg">
                                <i class="fa fa-chevron-circle-left" style="font-size:24px"></i>
                            </x-filament::button>

                            <x-filament::button id="full-screen"
                                class="px-2 w-12 h-12 bg-primary text-white items-center flex rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6 w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                </svg>
                            </x-filament::button>

                            <x-filament::button id="connect-button"
                                class="px-2 w-12 h-12 bg-blue-500 hover:bg-blue-400 text-white rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6 w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                </svg>
                            </x-filament::button>

                            <button @click="showSearchForm = !showSearchForm"
                                class="px-3 sm:px-4 py-2 sm:py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base"
                                title="Tekan Ctrl+K untuk toggle (Cmd+K di Mac)">
                                <svg class="w-4 sm:w-5 h-4 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Cari Produk
                            </button>
                            <button
                                onclick="window.open('{{ route('pos.customer-display') }}', 'CustomerDisplay', 'width=1400,height=900')"
                                class="px-3 sm:px-4 py-2 sm:py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base">
                                <svg class="w-4 sm:w-5 h-4 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                                Customer Display
                            </button>
                        </div>

                        {{-- CABLE PRINT DISABLED - Always use Bluetooth --}}
                        {{-- @if (!$print_via_bluetooth) --}}
                            {{-- Printer Status Indicator --}}
                            {{-- <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs sm:text-sm {{ $auto_print ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-50 text-gray-600 border border-gray-200' }}"> --}}
                                {{-- ... --}}
                            {{-- </div> --}}
                        {{-- @else --}}
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-xs sm:text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                                    </path>
                                </svg>
                                <span class="font-medium">Bluetooth Print</span>
                            </div>
                        {{-- @endif --}}
                    </div>

                    <!-- Search Form (Hidden by default) -->
                    <div x-show="showSearchForm" x-transition
                        class="mb-6 p-3 sm:p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-2 border-purple-200 dark:border-purple-700">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-base sm:text-lg font-semibold text-purple-800 dark:text-purple-300">Cari Produk</h3>
                            <button @click="showSearchForm = false"
                                class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex gap-2 sm:gap-3 mb-4">
                            <div class="relative flex-1">
                                <svg class="absolute left-3 top-3 w-4 sm:w-5 h-4 sm:h-5 text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="text" id="search-product" wire:model.live="search"
                                    placeholder="Ketik nama, kode, atau barcode produk..."
                                    class="w-full pl-9 sm:pl-10 pr-4 py-2 sm:py-2 rounded-lg border border-purple-300 dark:border-purple-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-800 transition-all duration-200">
                            </div>
                        </div>

                        <!-- Search Results Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3 max-h-[300px] sm:max-h-[400px] overflow-y-auto">
                            @forelse ($products as $product)
                                <button wire:click="addToOrder({{ $product->id }})" @click="showSearchForm = false; document.getElementById('search-product').value = '';"
                                    class="group relative overflow-hidden rounded-lg bg-white dark:bg-gray-700 border-2 border-purple-200 dark:border-purple-700 hover:border-purple-500 dark:hover:border-purple-400 transition-all duration-200 p-2 sm:p-3 text-left">
                                    <!-- Product Image -->
                                    <div class="mb-2 h-16 sm:h-24 overflow-hidden rounded bg-gray-100 dark:bg-gray-600">
                                        @if ($product->image && file_exists(public_path('storage/' . $product->image)))
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                alt="{{ $product->name }}"
                                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-200">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <svg class="w-6 sm:w-8 h-6 sm:h-8" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Product Info -->
                                    <h4 class="text-xs font-semibold text-gray-800 dark:text-white line-clamp-2 mb-1">
                                        {{ $product->name }}
                                    </h4>
                                    @if ($product->barcode)
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-1 font-mono">
                                            {{ $product->barcode }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </p>

                                    <!-- Stock Info -->
                                    <div class="text-xs text-gray-500 dark:text-gray-500 mb-2">
                                        @php
                                            $unitType = $product->getUnitType();
                                            $stockValue = $product->getFormattedTotalStock();

                                            // Tampilkan format desimal lokal untuk satuan timbang
                                            if (strtoupper($unitType) !== 'PCS') {
                                                $stockValue = str_replace('.', ',', $stockValue);
                                            }
                                        @endphp
                                        <span
                                            class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded text-xs">
                                            Stok: {{ $stockValue }}
                                            @if (strtoupper($unitType) !== 'PCS')
                                                <span class="ml-1 text-[10px] font-semibold opacity-80">{{ $unitType }}</span>
                                            @endif
                                        </span>
                                    </div>

                                    <!-- Add Button Indicator -->
                                    <div
                                        class="absolute inset-0 bg-purple-500/0 group-hover:bg-purple-500/10 transition-colors duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <svg class="w-5 sm:w-6 h-5 sm:h-6 text-purple-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </div>
                                </button>
                            @empty
                                <div class="col-span-full text-center py-8">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Produk tidak ditemukan</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        @if ($products->hasPages())
                            <div class="mt-6 flex flex-wrap justify-center items-center gap-2 sm:gap-3 px-2 sm:px-0">
                                {{-- Previous Button --}}
                                @if ($products->onFirstPage())
                                    <button type="button" disabled
                                        class="px-3 sm:px-4 py-2 text-sm sm:text-base bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-600 rounded-lg cursor-not-allowed opacity-60">
                                        <span class="hidden sm:inline">← Sebelumnya</span>
                                        <span class="sm:hidden">←</span>
                                    </button>
                                @else
                                    <button type="button" wire:click="previousPage"
                                        class="px-3 sm:px-4 py-2 text-sm sm:text-base bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg transition-all duration-200 hover:shadow-md">
                                        <span class="hidden sm:inline">← Sebelumnya</span>
                                        <span class="sm:hidden">←</span>
                                    </button>
                                @endif

                                {{-- Page Numbers --}}
                                <div class="flex items-center gap-1 sm:gap-2">
                                    @php
                                        $currentPage = $products->currentPage();
                                        $lastPage = $products->lastPage();
                                        $pageRange = 2; // Tampilkan 2 halaman sebelum dan sesudah

                                        $startPage = max(1, $currentPage - $pageRange);
                                        $endPage = min($lastPage, $currentPage + $pageRange);

                                        // Perluas range jika di awal atau akhir
                                        if ($startPage === 1) {
                                            $endPage = min($lastPage, 5);
                                        }
                                        if ($endPage === $lastPage) {
                                            $startPage = max(1, $lastPage - 4);
                                        }
                                    @endphp

                                    {{-- First page --}}
                                    @if ($startPage > 1)
                                        <button type="button" wire:click="gotoPage(1)"
                                            class="px-2 sm:px-3 py-2 text-sm sm:text-base bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded transition-colors">
                                            1
                                        </button>
                                        @if ($startPage > 2)
                                            <span class="text-gray-400 dark:text-gray-600 px-1">...</span>
                                        @endif
                                    @endif

                                    {{-- Range pages --}}
                                    @for ($i = $startPage; $i <= $endPage; $i++)
                                        @if ($i == $currentPage)
                                            <button type="button" disabled
                                                class="px-2 sm:px-3 py-2 text-sm sm:text-base font-bold bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded cursor-not-allowed shadow-md">
                                                {{ $i }}
                                            </button>
                                        @else
                                            <button type="button" wire:key="products-page-{{ $i }}" wire:click="gotoPage({{ $i }})"
                                                class="px-2 sm:px-3 py-2 text-sm sm:text-base bg-gray-100 dark:bg-gray-700 hover:bg-purple-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded transition-colors hover:text-purple-700 dark:hover:text-purple-300">
                                                {{ $i }}
                                            </button>
                                        @endif
                                    @endfor

                                    {{-- Last page --}}
                                    @if ($endPage < $lastPage)
                                        @if ($endPage < $lastPage - 1)
                                            <span class="text-gray-400 dark:text-gray-600 px-1">...</span>
                                        @endif
                                        <button type="button" wire:click="gotoPage({{ $lastPage }})"
                                            class="px-2 sm:px-3 py-2 text-sm sm:text-base bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded transition-colors">
                                            {{ $lastPage }}
                                        </button>
                                    @endif
                                </div>

                                {{-- Next Button --}}
                                @if ($products->hasMorePages())
                                    <button type="button" wire:click="nextPage"
                                        class="px-3 sm:px-4 py-2 text-sm sm:text-base bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg transition-all duration-200 hover:shadow-md">
                                        <span class="hidden sm:inline">Selanjutnya →</span>
                                        <span class="sm:hidden">→</span>
                                    </button>
                                @else
                                    <button type="button" disabled
                                        class="px-3 sm:px-4 py-2 text-sm sm:text-base bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-600 rounded-lg cursor-not-allowed opacity-60">
                                        <span class="hidden sm:inline">Selanjutnya →</span>
                                        <span class="sm:hidden">→</span>
                                    </button>
                                @endif
                            </div>

                            {{-- Page Info --}}
                            <div class="mt-3 text-center text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}
                            </div>
                        @endif
                    </div>

                    <!-- Barcode Input Controls -->
                    <div class="flex gap-2 sm:gap-3">
                        <div class="relative flex-1">
                            <svg class="absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 w-4 sm:w-5 h-4 sm:h-5 text-gray-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                </path>
                            </svg>
                            <input wire:model.live='barcode' type="text" placeholder="Scan barcode..." autofocus
                                id="barcode" @focus="$el.select()"
                                class="w-full pl-9 sm:pl-12 pr-4 py-2 sm:py-3 border-2 border-gray-200 rounded-xl bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-gray-900 dark:text-white text-sm sm:text-base focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:focus:ring-green-800 transition-all duration-200">
                        </div>
                        <x-filament::button x-data="" x-on:click="$dispatch('toggle-scanner')"
                            class="px-3 sm:px-4 py-2 sm:py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                            <img src="{{ asset('images/qrcode-scan.svg') }}" class="w-5 sm:w-6 h-5 sm:h-6" />
                        </x-filament::button>
                    </div>

                    <!-- Keyboard Shortcut Info -->
                    <div class="mt-2 sm:mt-3 text-xs text-gray-500 dark:text-gray-400">
                        💡 Shortcut:
                        <kbd class="px-1.5 sm:px-2 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300 font-mono text-xs">Ctrl+K</kbd> Cari Produk |
                        <kbd class="px-1.5 sm:px-2 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300 font-mono text-xs">Ctrl+M</kbd> Input Member |
                        <kbd class="px-1.5 sm:px-2 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300 font-mono text-xs">End</kbd> Nominal Bayar |
                        <kbd class="px-1.5 sm:px-2 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300 font-mono text-xs">Enter</kbd> (2x) Bayar
                    </div>

                    {{-- MODAL SCAN CAMERA --}}
                    <livewire:scanner-modal-component>
                </div>

                <!-- Item Display Section -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-3 sm:p-4 lg:p-6 h-full flex flex-col">
                    <h2
                        class="text-lg sm:text-2xl font-extrabold text-gray-900 dark:text-white mb-4 lg:mb-6 border-b pb-2 sm:pb-3 border-gray-100 dark:border-gray-700">
                        Item di Keranjang
                    </h2>

                    @if (count($order_items) === 0)
                        <div class="flex flex-col items-center justify-center py-12 sm:py-20 flex-grow">
                            <img src="{{ asset('images/cart-empty.png') }}" alt="Keranjang Kosong"
                                class="w-24 sm:w-32 h-24 sm:h-32 mb-4 sm:mb-6 opacity-70 dark:opacity-50">
                            <p class="text-gray-500 dark:text-gray-400 font-bold text-base sm:text-xl">Keranjang Kosong</p>
                            <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mt-2 px-2 text-center">Scan produk atau cari untuk
                                memulai transaksi.</p>
                        </div>
                    @else
                        <div class="space-y-2 sm:space-y-4 pr-1 sm:pr-2 flex-grow overflow-y-auto">
                            @php
                                // Sort items by added_at timestamp in descending order (newest first)
                                $sortedItems = collect($order_items)->sortByDesc(function ($item) {
                                    return $item['added_at'] ?? 0;
                                });
                            @endphp
                            @foreach ($sortedItems as $item)
                                <div
                                    class="bg-gray-50 dark:bg-gray-700 rounded-xl p-2 sm:p-4 flex items-start gap-2 sm:gap-4 shadow-sm hover:shadow-lg transition-all duration-300">

                                    <div class="flex-shrink-0">
                                        <img src="{{ asset('storage/' . $item['image_url']) }}"
                                            alt="{{ $item['name'] }}"
                                            class="w-14 sm:w-20 h-14 sm:h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3
                                                class="text-sm sm:text-lg font-semibold text-gray-900 dark:text-white truncate pr-2">
                                                {{ $item['name'] }}
                                            </h3>
                                            <button wire:click="removeItem({{ $item['product_id'] }})"
                                                class="p-1 ml-1 sm:ml-2 bg-red-100 hover:bg-red-200 dark:bg-red-800 dark:hover:bg-red-700 text-red-600 dark:text-red-300 rounded-full transition-colors duration-200 flex-shrink-0"
                                                title="Hapus Item">
                                                <svg class="w-3 sm:w-4 h-3 sm:h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mb-2 sm:mb-3">
                                            @php
                                                $hasWeight =
                                                    isset($item['weight_grams']) &&
                                                    !empty($item['weight_grams']) &&
                                                    $item['weight_grams'] > 0;
                                                $unitText = $hasWeight ? '/kg' : '/pcs';
                                                $quantityDisplay = $hasWeight
                                                    ? $item['weight_grams'] . 'gr'
                                                    : $item['quantity'] . 'x';
                                                $originalPrice = $hasWeight
                                                    ? $item['original_unit_price'] ?? $item['unit_price']
                                                    : $item['original_price'] ?? $item['price'];
                                                $currentPrice = $hasWeight ? $item['unit_price'] : $item['price'];
                                                $isDiscounted =
                                                    isset($item['discount_percentage']) &&
                                                    $item['discount_percentage'] > 0;
                                            @endphp

                                            <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">
                                                @if ($isDiscounted)
                                                    <span class="line-through mr-1 sm:mr-2 text-xs">
                                                        Rp
                                                        {{ number_format($originalPrice, 0, ',', '.') }}{{ $unitText }}
                                                    </span>
                                                    <span class="text-red-600 dark:text-red-400 font-semibold">
                                                        Rp
                                                        {{ number_format($currentPrice, 0, ',', '.') }}{{ $unitText }}
                                                    </span>
                                                    <span
                                                        class="text-xs bg-red-50 text-red-700 dark:bg-red-900 dark:text-red-300 px-1 sm:px-1.5 py-0.5 rounded ml-1 sm:ml-2 font-bold">
                                                        -{{ $item['discount_percentage'] }}%
                                                    </span>
                                                @else
                                                    <span class="text-green-600 dark:text-green-400 font-semibold">
                                                        Rp
                                                        {{ number_format($currentPrice, 0, ',', '.') }}{{ $unitText }}
                                                    </span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Jumlah: **{{ $quantityDisplay }}**
                                            </p>
                                        </div>

                                        <div
                                            class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                            <div class="flex items-center space-x-1">
                                                <button wire:click="decreaseQuantity({{ $item['product_id'] }})"
                                                    class="p-1 bg-red-500 hover:bg-red-600 text-white rounded-full transition-colors duration-200"
                                                    title="Kurangi Kuantitas">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3" d="M20 12H4"></path>
                                                    </svg>
                                                </button>
                                                <span
                                                    class="text-lg font-extrabold text-gray-800 dark:text-white w-6 text-center">
                                                    {{ $item['quantity'] }}
                                                </span>
                                                <button wire:click="increaseQuantity({{ $item['product_id'] }})"
                                                    class="p-1 bg-green-500 hover:bg-green-600 text-white rounded-full transition-colors duration-200"
                                                    title="Tambah Kuantitas">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Subtotal</p>
                                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                                    Rp
                                                    {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 pt-6 border-t-4 border-gray-200 dark:border-gray-700 space-y-4 flex-shrink-0">
                            @php
                                $cartTotal = 0;
                                foreach ($order_items as $item) {
                                    $cartTotal += $item['quantity'] * $item['price'];
                                }
                            @endphp

                            <div class="flex justify-between items-center">
                                <span class="text-lg text-gray-600 dark:text-gray-400 font-medium">Total Item</span>
                                <span class="text-xl font-bold text-gray-800 dark:text-white">
                                    {{ count($order_items) }}
                                </span>
                            </div>

                            <div
                                class="flex justify-between items-center bg-green-50 dark:bg-gray-700/50 p-3 rounded-xl">
                                <span class="text-xl font-bold text-green-700 dark:text-green-400">GRAND TOTAL</span>
                                <span class="text-4xl font-extrabold text-green-800 dark:text-green-300">
                                    Rp {{ number_format($cartTotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cart Section -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-3 sm:p-4 lg:p-6 sticky top-0 lg:top-0">
                    <!-- Header with Reset Button -->
                    <div class="flex items-center justify-between mb-4 lg:mb-6">
                        <h2 class="text-base sm:text-lg lg:text-xl font-bold text-gray-800 dark:text-white">Ringkasan Pesanan</h2>
                        <button wire:click="resetOrder"
                            class="px-2 sm:px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors duration-200">
                            Reset
                        </button>
                    </div>

                    <!-- Member Section (Always Visible - Outside scrollable area) -->
                    <div class="space-y-3 sm:space-y-4 mb-4">
                        @include('livewire.partials.member-section')
                    </div>

                    <form wire:submit="checkout">

                        @if (count($order_items) === 0)
                            <!-- Empty State -->
                            <div class="flex flex-col items-center justify-center py-8 sm:py-12 mt-6">
                                <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-300 dark:text-gray-600 mb-2 sm:mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400 font-medium text-center text-sm sm:text-base">Keranjang Kosong</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 sm:mt-2 text-center">Scan atau cari produk untuk memulai</p>
                            </div>
                        @else
                            <!-- Calculation Summary -->
                            <div
                                class="bg-gradient-to-b from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10 rounded-lg p-3 sm:p-4 space-y-2 sm:space-y-3 mb-4 lg:mb-6">
                                <!-- Total Items -->
                                <div class="flex justify-between items-center">
                                    <span class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Total Item</span>
                                    <span
                                        class="text-base sm:text-lg font-bold text-gray-800 dark:text-white">{{ count($order_items) }}</span>
                                </div>

                                <!-- Subtotal -->
                                @php
                                    $cartTotal = 0;
                                    foreach ($order_items as $item) {
                                        $cartTotal += $item['quantity'] * $item['price'];
                                    }
                                @endphp
                                <div class="flex justify-between items-center">
                                    <span class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Subtotal</span>
                                    <span class="text-base sm:text-lg font-semibold text-gray-800 dark:text-white">
                                        Rp {{ number_format($cartTotal, 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Promo Discount (if any) -->
                                @if ($promo_discount > 0)
                                    <div class="border-t border-blue-200 dark:border-blue-800 pt-2 sm:pt-3">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Diskon Promo</span>
                                            <span class="text-xs sm:text-sm font-semibold text-red-600 dark:text-red-400">
                                                -Rp {{ number_format($promo_discount, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        <!-- Applied Promos List -->
                                        <div
                                            class="space-y-1 mb-2 sm:mb-3 bg-white dark:bg-gray-700/50 rounded p-2 max-h-[80px] overflow-y-auto">
                                            @foreach ($applied_promos as $promo)
                                                <div class="text-xs">
                                                    <p class="font-semibold text-green-700 dark:text-green-400">
                                                        {{ $promo['promo_name'] }}</p>
                                                    <p class="text-green-600 dark:text-green-500">
                                                        {{ $promo['description'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Total Price -->
                                <div class="border-t border-blue-200 dark:border-blue-800 pt-2 sm:pt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm sm:text-base font-bold text-gray-800 dark:text-white">Total Bayar</span>
                                        <span class="text-xl sm:text-2xl font-bold text-blue-600 dark:text-blue-400">
                                            Rp {{ number_format($total_price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="border-t-2 border-gray-200 dark:border-gray-700 my-4 lg:my-6"></div>

                            <!-- Checkout Form Section (Visible only when items exist) -->
                            <div class="space-y-3 sm:space-y-4 max-h-[300px] sm:max-h-none overflow-y-auto sm:overflow-y-visible pb-3 sm:pb-0">

                                <!-- Customer Name -->
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-white mb-1 sm:mb-2">
                                        Nama Customer
                                    </label>
                                    <input type="text" wire:model="name"
                                        class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Masukkan nama customer">
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-white mb-1 sm:mb-2">
                                        Metode Pembayaran
                                    </label>
                                    <select wire:model.live="payment_method_id"
                                        class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        required>
                                        <option value="">Pilih metode pembayaran</option>
                                        @foreach ($payment_methods as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method_id')
                                        <span class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Change Display -->
                                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-3 sm:p-4 text-white">
                                    <div class="text-xs sm:text-sm opacity-90">Kembalian</div>
                                    <div class="text-lg sm:text-2xl font-bold transition-all duration-300">
                                        Rp {{ number_format($change, 0, ',', '.') }}
                                    </div>
                                </div>

                                <!-- Cash Received (Only for Cash Payment) -->
                                @if ($is_cash)
                                    <div>
                                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-white mb-1 sm:mb-2">
                                            Nominal Bayar
                                        </label>
                                        <div class="relative mb-2 sm:mb-3">
                                            <div class="absolute left-3 sm:left-4 inset-y-0 flex items-center pointer-events-none">
                                                <span class="text-gray-500 dark:text-gray-400 font-medium text-sm leading-none">Rp</span>
                                            </div>
                                            <input type="text" wire:model.live="cash_received"
                                                id="cash-received-input"
                                                x-data="{
                                                    formatCurrency(value) {
                                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                                                    }
                                                }"
                                                x-on:input="
                                                    let raw = $event.target.value.replace(/\./g, '');
                                                    if (!isNaN(raw)) {
                                                        $event.target.value = formatCurrency(raw);
                                                    }
                                                "
                                                class="w-full pl-12 sm:pl-14 pr-3 sm:pr-4 py-2 sm:py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 text-sm sm:text-lg font-medium"
                                                placeholder="0" required autocomplete="off">
                                            @error('cash_received')
                                                <span class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Quick Cash Buttons -->
                                        <div class="grid grid-cols-2 gap-1 sm:gap-2 mb-3 sm:mb-4">
                                            @php
                                                $baseAmount = (int) $total_price;
                                                $quickAmounts = [
                                                    ceil($baseAmount / 50000) * 50000,
                                                    ceil($baseAmount / 100000) * 100000,
                                                    ceil($baseAmount / 100000) * 100000 + 50000,
                                                    ceil($baseAmount / 500000) * 500000,
                                                ];
                                                $quickAmounts = array_unique($quickAmounts);
                                                sort($quickAmounts);
                                            @endphp
                                            @foreach ($quickAmounts as $amount)
                                                <button type="button"
                                                    wire:click="$set('cash_received', '{{ number_format($amount, 0, '', '.') }}')"
                                                    class="py-1 sm:py-2 px-2 sm:px-3 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs font-medium text-gray-700 dark:text-white transition-colors duration-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                                                    {{ number_format($amount, 0, ',', '.') }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <!-- Non-Cash Payment Info -->
                                    <div class="rounded-xl p-2 sm:p-3 bg-amber-50 dark:bg-amber-900/20">
                                        <div class="flex items-center space-x-2 text-xs sm:text-sm">
                                            <svg class="w-4 sm:w-5 h-4 sm:h-5 text-amber-600 dark:text-amber-400 flex-shrink-0"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m-1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            <span class="text-amber-700 dark:text-amber-500">
                                                Pembayaran akan diproses sesuai nominal
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Promo Display -->
                                @if ($promo_discount > 0)
                                    <div
                                        class="p-2 sm:p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-700">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-3 sm:w-4 h-3 sm:h-4 text-green-600 dark:text-green-400 flex-shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M4.633 12.67a2 2 0 11-1.732 2.732M15.753 11.618a4 4 0 00-3.452-3.452" />
                                            </svg>
                                            <h3 class="font-semibold text-xs sm:text-sm text-green-800 dark:text-green-300">Promo Aktif</h3>
                                        </div>
                                        <div
                                            class="space-y-1 mb-2 bg-white dark:bg-gray-700/50 rounded p-2 max-h-[100px] overflow-y-auto">
                                            @foreach ($applied_promos as $promo)
                                                <div class="text-xs">
                                                    <p class="text-green-700 dark:text-green-400 font-medium">
                                                        {{ $promo['promo_name'] }}</p>
                                                    <p class="text-green-600 dark:text-green-500">
                                                        {{ $promo['description'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div
                                            class="border-t border-green-300 dark:border-green-700 pt-2 flex justify-between items-center">
                                            <span
                                                class="font-semibold text-xs sm:text-sm text-green-800 dark:text-green-300">Total Diskon</span>
                                            <span class="text-xs sm:text-sm font-bold text-green-600 dark:text-green-400">-Rp
                                                {{ number_format($promo_discount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2 sm:gap-3 mt-4 lg:mt-6">
                                <button type="button" wire:click="resetOrder"
                                    class="flex-1 py-2 sm:py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-medium text-sm sm:text-base rounded-xl transition-all duration-200 transform hover:scale-[0.98]">
                                    Batal
                                </button>
                                <button type="submit" id="btn-bayar" @if ($is_cash && ($change < 0 || empty($cash_received))) disabled @endif
                                    x-data="{ enterPressed: false }"
                                    x-on:keydown.enter.window="
                                        if (!$event.ctrlKey && !$event.metaKey && !$event.altKey && !$event.shiftKey) {
                                            const activeEl = document.activeElement;
                                            const isInputField = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');

                                            // Skip jika sedang di input field (kecuali cash-received-input)
                                            if (isInputField && activeEl.id !== 'cash-received-input') {
                                                return;
                                            }

                                            // Jika sedang di cash-received-input, fokus ke tombol bayar
                                            if (activeEl.id === 'cash-received-input') {
                                                $event.preventDefault();
                                                $el.focus();
                                                enterPressed = false;
                                                return;
                                            }

                                            // Jika tombol bayar sudah fokus, submit form
                                            if (document.activeElement === $el && enterPressed) {
                                                // Enter kedua - eksekusi pembayaran
                                                if (!$el.disabled) {
                                                    $el.click();
                                                }
                                                enterPressed = false;
                                            } else {
                                                // Enter pertama - fokus ke tombol bayar
                                                $event.preventDefault();
                                                $el.focus();
                                                enterPressed = true;
                                            }
                                        }
                                    "
                                    x-on:blur="enterPressed = false"
                                    class="flex-1 py-2 sm:py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium text-sm sm:text-base rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 focus:ring-4 focus:ring-green-300 focus:outline-none">
                                    <svg class="w-4 sm:w-5 h-4 sm:h-5 inline-block mr-1 sm:mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                    Bayar
                                </button>
                            </div>
                            </div>
                        @endif
                    </form>
            </div>
        </div>
    </div>

    <!-- Weight Modal -->
    @if ($showWeightModal)
        <div wire:ignore.self
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4"
            x-data x-show="true">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md transform scale-95 animate-modal-appear">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Masukkan Berat (gram)</h3>
                    <input type="number" wire:model.live="weight_gram" wire:keydown.enter="confirmWeight" min="0" placeholder="Contoh: 250"
                        id="weight-input"
                        x-init="$nextTick(() => $el.focus())"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200" />
                    <!-- Quick Weight Buttons -->
                    <div class="flex gap-2 mt-2">
                        <button type="button" wire:click="$set('weight_gram', 1000)"
                            class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg">1000g</button>
                        <button type="button" wire:click="$set('weight_gram', 2000)"
                            class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg">2000g</button>
                        <button type="button" wire:click="$set('weight_gram', 3000)"
                            class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg">3000g</button>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showWeightModal', false)"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-200">Batal</button>
                        <button type="button" wire:click="confirmWeight"
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl transition-all duration-200">OK</button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <!-- Payment Confirmation Modal -->
    @if ($showConfirmationModal)
        <div wire:ignore.self
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md transform animate-modal-appear">
                <div class="p-6">
                    <!-- Success Icon Animation -->
                    <div class="mx-auto w-20 h-20 mb-4">
                        <svg class="w-full h-full text-green-500 animate-success-check" fill="none"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"
                                class="animate-circle-draw" />
                            <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="animate-check-draw" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2 text-center">
                        Pembayaran Berhasil!
                    </h3>

                    <p class="text-gray-600 dark:text-white text-center mb-6">
                        Transaksi telah berhasil diproses
                    </p>

                    <!-- Transaction Summary -->
                    <div class="bg-gray-50 dark:bg-gray-600 rounded-xl p-4 mb-6">
                        <div class="space-y-2 text-sm">
                            @if ($orderToPrint)
                                @php
                                    $order = \App\Models\Transaction::find($orderToPrint);
                                @endphp
                                @if ($order)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">No. Transaksi</span>
                                        <span
                                            class="font-medium text-gray-800 dark:text-white">{{ $order->transaction_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Bayar</span>
                                        <span class="font-medium text-gray-800 dark:text-white">Rp
                                            {{ number_format($order->cash_received, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($order->change > 0)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Kembalian</span>
                                            <span class="font-medium text-green-600">Rp
                                                {{ number_format($order->change, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Print Options -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-white mb-2">
                            Cetak Struk?
                        </h4>

                        <div class="grid grid-cols-2 gap-3">
                            {{-- CABLE PRINT DISABLED - Always use Bluetooth --}}
                            {{-- @if (!$print_via_bluetooth) --}}
                                {{-- <button wire:click="printLocalKabel" ...> --}}
                                {{-- ... --}}
                                {{-- </button> --}}
                            {{-- @else --}}
                                <button wire:click="printBluetooth"
                                    class="flex items-center justify-center space-x-2 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-[0.98]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                                        </path>
                                    </svg>
                                    <span>Cetak Struk</span>
                                </button>
                            {{-- @endif --}}

                            <button wire:click="$set('showConfirmationModal', false)"
                                class="flex items-center justify-center space-x-2 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-[0.98]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Lewati</span>
                            </button>
                        </div>
                    </div>

                    <!-- Auto-close countdown -->
                    <div class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400" x-data="paymentSuccessTimer()">
                        Modal akan tertutup dalam <span x-text="seconds"></span> detik
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Animation Styles -->
        <style>
            @keyframes circle-draw {
                to {
                    stroke-dashoffset: 0;
                }
            }

            @keyframes check-draw {
                to {
                    stroke-dashoffset: 0;
                }
            }

            .animate-circle-draw {
                stroke-dasharray: 62.83;
                stroke-dashoffset: 62.83;
                animation: circle-draw 0.6s ease-out forwards;
            }

            .animate-check-draw {
                stroke-dasharray: 24;
                stroke-dashoffset: 24;
                animation: check-draw 0.3s ease-out 0.6s forwards;
            }

            .animate-success-check {
                animation: scale-up 0.3s ease-out 0.9s forwards;
                transform: scale(0);
            }

            @keyframes scale-up {
                to {
                    transform: scale(1);
                }
            }
        </style>
    @endif
</div>
