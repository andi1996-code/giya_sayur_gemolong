<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Get;
use App\Models\Setting;
use App\Services\DirectPrintService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class PrinterSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationLabel = 'Printer Saya';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $slug = 'printer-settings';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.printer-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $globalSetting = Setting::first();

        $this->form->fill([
            'printer_name' => $user->printer_name,
            'print_via_bluetooth' => $user->printer_name ? ($user->print_via_bluetooth ? '1' : '0') : null,
            'auto_print' => $user->printer_name ? $user->auto_print : false,
            // Info global setting untuk ditampilkan
            'global_printer_name' => $globalSetting?->name_printer_local,
            'global_print_via_bluetooth' => $globalSetting?->print_via_bluetooth ? '1' : '0',
        ]);
    }

    public function form(Form $form): Form
    {
        $user = Auth::user();
        $globalSetting = Setting::first();

        return $form
            ->schema([
                Section::make('Pengaturan Printer Kasir')
                    ->description('Konfigurasi printer khusus untuk akun Anda. Jika tidak diisi, akan menggunakan pengaturan printer global.')
                    ->schema([
                        Placeholder::make('info')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="text-sm text-blue-800 dark:text-blue-200">
                                            <strong>Multi-Kasir Support:</strong> Setiap kasir bisa mengatur printer masing-masing.
                                            Pengaturan ini khusus untuk akun <strong>' . e($user->name) . '</strong>.
                                        </div>
                                    </div>
                                </div>
                            ')),

                        Placeholder::make('current_status')
                            ->label('Status Saat Ini')
                            ->content(function () use ($user, $globalSetting) {
                                $printerName = $user->getEffectivePrinterName();
                                $source = !empty($user->printer_name) ? 'Pengaturan Kasir' : 'Pengaturan Global';
                                $sourceColor = !empty($user->printer_name) ? 'text-green-600' : 'text-yellow-600';

                                if (!$printerName) {
                                    return new HtmlString('<span class="text-red-600 font-medium">Belum dikonfigurasi</span>');
                                }

                                return new HtmlString("
                                    <span class='font-medium'>{$printerName}</span>
                                    <span class='{$sourceColor} text-sm'> (dari {$source})</span>
                                ");
                            }),

                        Radio::make('print_via_bluetooth')
                            ->label('Tipe Koneksi Printer')
                            ->options([
                                '0' => 'Kabel (Server Local)',
                                '1' => 'Bluetooth'
                            ])
                            ->inline()
                            ->live()
                            ->helperText('Pilih metode koneksi printer untuk PC Anda'),

                        TextInput::make('printer_name')
                            ->label('Nama Printer')
                            ->maxLength(255)
                            ->placeholder($globalSetting?->name_printer_local ?? 'Contoh: POS-58, Epson T20')
                            ->helperText(function () use ($globalSetting) {
                                $hint = 'Masukkan nama printer sesuai yang terdaftar di Windows.';
                                if ($globalSetting?->name_printer_local) {
                                    $hint .= ' Kosongkan untuk menggunakan printer global: ' . $globalSetting->name_printer_local;
                                }
                                return $hint;
                            })
                            ->visible(fn (Get $get): bool => $get('print_via_bluetooth') === '0')
                            ->suffixAction(
                                FormAction::make('testPrint')
                                    ->label('Test')
                                    ->icon('heroicon-o-printer')
                                    ->color('success')
                                    ->action(function (Get $get) {
                                        $printerName = $get('printer_name');
                                        if (empty($printerName)) {
                                            // Fallback ke global
                                            $printerName = Setting::first()?->name_printer_local;
                                        }

                                        if (empty($printerName)) {
                                            Notification::make()
                                                ->title('Nama printer belum diatur')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        $directPrint = app(DirectPrintService::class);
                                        $directPrint->testPrint($printerName);
                                    })
                            ),

                        Toggle::make('auto_print')
                            ->label('Cetak Otomatis')
                            ->helperText('Cetak struk secara otomatis setelah transaksi selesai')
                            ->visible(fn (Get $get): bool => $get('print_via_bluetooth') === '0')
                            ->default(false),
                    ])
                    ->columns(1),

                Section::make('Pengaturan Printer Global')
                    ->description('Pengaturan printer default yang digunakan jika kasir tidak mengatur printer sendiri')
                    ->collapsed()
                    ->schema([
                        Placeholder::make('global_info')
                            ->label('Printer Global')
                            ->content(function () use ($globalSetting) {
                                if (!$globalSetting?->name_printer_local) {
                                    return new HtmlString('<span class="text-gray-500">Belum dikonfigurasi</span>');
                                }
                                $type = $globalSetting->print_via_bluetooth ? 'Bluetooth' : 'Kabel';
                                return new HtmlString("
                                    <span class='font-medium'>{$globalSetting->name_printer_local}</span>
                                    <span class='text-gray-500 text-sm'> ({$type})</span>
                                ");
                            }),
                        Placeholder::make('global_note')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="text-sm text-gray-500">
                                    Untuk mengubah pengaturan printer global, silakan ke menu
                                    <a href="/admin/shop-settings" class="text-primary-600 hover:underline">Pengaturan Toko</a>.
                                </div>
                            ')),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        // Update user's printer settings
        $user->update([
            'printer_name' => $data['printer_name'] ?: null,
            'print_via_bluetooth' => $data['print_via_bluetooth'] === '1',
            'auto_print' => $data['auto_print'] ?? false,
        ]);

        // Clear localStorage cache via JavaScript
        $this->dispatch('clearPrinterCache');

        Notification::make()
            ->title('Pengaturan printer berhasil disimpan')
            ->body($data['printer_name']
                ? "Printer: {$data['printer_name']}"
                : 'Menggunakan pengaturan printer global')
            ->success()
            ->send();
    }

    public function clearSettings(): void
    {
        $user = Auth::user();

        $user->update([
            'printer_name' => null,
            'print_via_bluetooth' => false,
            'auto_print' => false,
        ]);

        $this->form->fill([
            'printer_name' => null,
            'print_via_bluetooth' => null,
            'auto_print' => false,
        ]);

        $this->dispatch('clearPrinterCache');

        Notification::make()
            ->title('Pengaturan printer direset')
            ->body('Sekarang menggunakan pengaturan printer global')
            ->success()
            ->send();
    }
}
