<x-filament-widgets::widget>
    <x-filament::section heading="Informasi Toko">
        @php
            $setting = $this->getSetting();
        @endphp

        @if($setting)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-building-storefront class="w-5 h-5 text-primary-600" />
                        <span class="font-medium">{{ $setting->name ?? 'Nama Toko' }}</span>
                    </div>

                    @if($setting->phone)
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-phone class="w-4 h-4 text-gray-500" />
                            <span class="text-sm text-gray-600">{{ $setting->phone }}</span>
                        </div>
                    @endif

                    @if($setting->address)
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-map-pin class="w-4 h-4 text-gray-500" />
                            <span class="text-sm text-gray-600">{{ $setting->address }}</span>
                        </div>
                    @endif
                </div>

                {{-- <div class="space-y-2">
                    @if($setting->print_via_bluetooth)
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-device-phone-mobile class="w-4 h-4 text-green-600" />
                            <span class="text-sm text-green-600">Print via Bluetooth</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-computer-desktop class="w-4 h-4 text-blue-600" />
                            <span class="text-sm text-blue-600">Print via USB</span>
                        </div>
                    @endif

                    @if($setting->name_printer_local)
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-printer class="w-4 h-4 text-gray-500" />
                            <span class="text-sm text-gray-600">Printer: {{ $setting->name_printer_local }}</span>
                        </div>
                    @endif
                </div> --}}
            </div>
        @else
            <div class="text-center py-4">
                <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-yellow-500 mx-auto mb-2" />
                <p class="text-sm text-gray-600">Pengaturan toko belum dikonfigurasi</p>
                <a href="{{ route('filament.admin.pages.shop-settings') }}" class="text-sm text-primary-600 hover:text-primary-700">
                    Konfigurasi sekarang →
                </a>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
