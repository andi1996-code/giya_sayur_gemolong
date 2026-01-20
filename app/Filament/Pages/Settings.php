<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Get;
use App\Models\Setting;
use App\Services\DirectPrintService;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Toko';
    protected static ?string $navigationGroup = 'Pengaturan Toko';
    protected static ?string $slug = 'shop-settings';
    protected static ?int $navigationSort = 9;
    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();
        $this->form->fill($setting ? $setting->toArray() : []);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Profil Toko')
                    ->description('Informasi dasar toko Anda')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Toko')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('address')
                            ->label('Alamat Toko')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('logo')
                            ->label('Logo Toko')
                            ->disk('public')
                            ->image()
                            ->directory('logos')
                            ->acceptedFileTypes(['image/png'])
                            ->helperText('Upload logo toko dalam format PNG')
                            ->columnSpanFull(),

                        FileUpload::make('customer_display_image')
                            ->label('Gambar Customer Display')
                            ->disk('public')
                            ->image()
                            ->directory('customer-display')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                            ->helperText('Upload gambar untuk ditampilkan di kolom kiri customer display (PNG/JPG)')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Pengaturan Printer')
                    ->description('Konfigurasi printer untuk struk')
                    ->schema([
                        Radio::make('print_via_bluetooth')
                            ->label('Tipe Koneksi Printer')
                            ->options([
                                '0' => 'Kabel (Server Local)',
                                '1' => 'Bluetooth'
                            ])
                            ->inline()
                            ->default('0')
                            ->live()
                            ->helperText('Pilih metode koneksi printer'),

                        TextInput::make('name_printer_local')
                            ->label('Nama Printer')
                            ->maxLength(255)
                            ->helperText('Contoh: Epson T20, Canon PIXMA, Generic / Text Only')
                            ->visible(fn (Get $get): bool => $get('print_via_bluetooth') === '0')
                            ->suffixAction(
                                FormAction::make('testPrint')
                                    ->label('Test')
                                    ->icon('heroicon-o-printer')
                                    ->color('success')
                                    ->action(function () {
                                        $directPrint = app(DirectPrintService::class);
                                        $directPrint->testPrint();
                                    })
                                    ->visible(fn (Get $get): bool => !empty($get('name_printer_local')))
                            ),

                        Toggle::make('auto_print')
                            ->label('Cetak Otomatis')
                            ->helperText('Cetak struk secara otomatis setelah transaksi selesai')
                            ->visible(fn (Get $get): bool => $get('print_via_bluetooth') === '0')
                            ->default(false),
                    ])
                    ->columns(1),

                Section::make('Footer Struk')
                    ->description('Atur teks yang muncul di bagian bawah struk belanja')
                    ->schema([
                        Textarea::make('receipt_footer_line1')
                            ->label('Baris 1')
                            ->placeholder('Contoh: Terima kasih atas kunjungan Anda')
                            ->maxLength(100)
                            ->helperText('Baris pertama footer (maks. 100 karakter)')
                            ->rows(2),

                        Textarea::make('receipt_footer_line2')
                            ->label('Baris 2')
                            ->placeholder('Contoh: Jl. Merdeka No. 123, Jakarta')
                            ->maxLength(100)
                            ->helperText('Baris kedua footer (maks. 100 karakter)')
                            ->rows(2),

                        Textarea::make('receipt_footer_line3')
                            ->label('Baris 3')
                            ->placeholder('Contoh: WA: 0812-3456-7890 | IG: @tokoku')
                            ->maxLength(100)
                            ->helperText('Baris ketiga footer (maks. 100 karakter)')
                            ->rows(2),

                        Textarea::make('receipt_footer_note')
                            ->label('Catatan Footer')
                            ->placeholder('Contoh: Barang yang sudah dibeli tidak dapat dikembalikan')
                            ->maxLength(200)
                            ->helperText('Catatan khusus (maks. 200 karakter)')
                            ->rows(3),

                        Toggle::make('show_footer_thank_you')
                            ->label('Tampilkan "Terima Kasih"')
                            ->helperText('Tampilkan teks "*** TERIMA KASIH ***" di akhir struk')
                            ->default(true),

                        Placeholder::make('footer_preview')
                            ->label('Preview Footer Struk')
                            ->content(function (Get $get): string {
                                $line1 = $get('receipt_footer_line1') ?: '';
                                $line2 = $get('receipt_footer_line2') ?: '';
                                $line3 = $get('receipt_footer_line3') ?: '';
                                $note = $get('receipt_footer_note') ?: '';
                                $showThankYou = $get('show_footer_thank_you') ?? true;

                                $preview = "================================\n";
                                if ($line1) $preview .= $line1 . "\n";
                                if ($line2) $preview .= $line2 . "\n";
                                if ($line3) $preview .= $line3 . "\n";
                                if ($note) $preview .= "\n" . $note . "\n";
                                if ($showThankYou) $preview .= "\n*** TERIMA KASIH ***\n";
                                $preview .= "================================";

                                return '<pre style="background: #f3f4f6; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; line-height: 1.5;">' . $preview . '</pre>';
                            })
                            ->helperText('Preview tampilan footer di struk'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::updateOrCreate([], $data);

        Notification::make()
            ->success()
            ->title('Pengaturan Disimpan')
            ->body('Pengaturan toko berhasil disimpan.')
            ->send();
    }


}
