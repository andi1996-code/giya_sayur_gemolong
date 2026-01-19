<style>
    @keyframes blink-red {
        0%, 50% {
            opacity: 1;
            background-color: #ef4444;
            box-shadow: 0 0 8px #ef4444;
        }
        51%, 100% {
            opacity: 0.4;
            background-color: #dc2626;
            box-shadow: 0 0 4px #dc2626;
        }
    }
    .blink-red {
        animation: blink-red 1.2s infinite;
        background-color: #ef4444 !important;
        border: 2px solid #ffffff !important;
    }
</style>

<div class="fi-wi-stock-notification">
    @php
        $lowStockCount = $this->getLowStockCount();
        $outOfStockCount = $this->getOutOfStockCount();
        $totalAlerts = $lowStockCount + $outOfStockCount;
    @endphp

    @if($totalAlerts > 0)
        <div class="fixed z-50" style="top: 56px; right: 16px;" x-data="{ open: false }">
            <!-- Notification Bell Icon -->
            <button
                @click="open = !open"
                class="relative p-2 bg-white dark:bg-gray-800 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>

                <!-- Badge -->
                @if($totalAlerts > 0)
                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full blink-red shadow-lg" style="background-color: #ef4444 !important; border: 2px solid white;"></span>
                @endif
            </button>

            <!-- Dropdown Panel -->
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                @click.away="open = false"
                class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                style="display: none;"
            >
                <!-- Header -->
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Peringatan Stok
                        </h3>
                        <span class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 text-xs px-2 py-1 rounded-full">
                            {{ $totalAlerts }} alert{{ $totalAlerts > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="max-h-96 overflow-y-auto">
                    @if($outOfStockCount > 0)
                        <div class="p-4 border-b border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-full">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-red-900 dark:text-red-200">Stok Habis</h4>
                                    <p class="text-sm text-red-700 dark:text-red-300">{{ $outOfStockCount }} produk kehabisan stok</p>
                                </div>
                            </div>

                            @foreach($this->getOutOfStockProducts() as $product)
                                <div class="flex items-center gap-3 py-2">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-8 h-8 rounded object-cover">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->category->name ?? 'No Category' }}</p>
                                    </div>
                                    <span class="text-xs bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-2 py-1 rounded">
                                        Habis
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($lowStockCount > 0)
                        <div class="p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-yellow-900 dark:text-yellow-200">Stok Menipis</h4>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">{{ $lowStockCount }} produk dengan stok ≤ 5</p>
                                </div>
                            </div>

                            @foreach($this->getLowStockProducts() as $product)
                                <div class="flex items-center gap-3 py-2">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-8 h-8 rounded object-cover">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->category->name ?? 'No Category' }}</p>
                                    </div>
                                    <span class="text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded">
                                        {{ $product->stock }} unit
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-t border-gray-200 dark:border-gray-600">
                    <a href="{{ route('filament.admin.resources.inventories.index') }}"
                       class="block w-full text-center bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                        Kelola Inventori
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
