<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-between items-center">
            <x-filament::button
                wire:click="clearSettings"
                type="button"
                color="gray"
                outlined
            >
                Reset ke Global
            </x-filament::button>

            <x-filament::button type="submit">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>

    {{-- Script untuk clear localStorage cache --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('clearPrinterCache', () => {
                // Clear printer name cache dari localStorage
                localStorage.removeItem('usb_print_printer_name');
                console.log('🔄 Printer cache cleared');
            });
        });
    </script>
</x-filament-panels::page>
