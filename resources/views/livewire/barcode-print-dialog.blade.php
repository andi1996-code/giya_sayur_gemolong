<div>
    <!-- Barcode Format Selection Modal -->
    <x-dialog-modal wire:model="showDialog" maxWidth="sm">
        <x-slot name="title">
            Pilih Format Barcode
        </x-slot>

        <x-slot name="content">
            <div class="space-y-3">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Pilih ukuran kertas untuk mencetak barcode:
                </p>

                <div class="grid gap-3">
                    <!-- A4 Option -->
                    <button wire:click="printBarcodes('a4')"
                        class="w-full p-4 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all text-left">
                        <div class="font-semibold text-gray-800 dark:text-white">📄 A4 Portrait</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            210 × 297 mm | 4 label per halaman
                        </div>
                    </button>

                    <!-- Label 33x15 Option -->
                    <button wire:click="printBarcodes('label_33x15')"
                        class="w-full p-4 border-2 border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all text-left">
                        <div class="font-semibold text-gray-800 dark:text-white">🏷️ Label Produk</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            33 × 15 mm | Untuk stiker produk
                        </div>
                    </button>

                    <!-- Label 60x30 Option -->
                    <button wire:click="printBarcodes('label_60x30')"
                        class="w-full p-4 border-2 border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all text-left">
                        <div class="font-semibold text-gray-800 dark:text-white">🏪 Label Rak</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            60 × 30 mm | Untuk label harga rak
                        </div>
                    </button>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('showDialog')">
                Batal
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
