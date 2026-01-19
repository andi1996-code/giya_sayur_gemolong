<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Setting;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\SettingResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;


class SettingResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete_any',
        ];
    }

    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-printer';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationGroup = 'Pengaturan Toko';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profil Toko')
                ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Toko'),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255)
                    ->label('Alamat Toko'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255)
                    ->label('Nomor Telepon'),
                ]),
                Forms\Components\Section::make('Setting Printer')
                ->schema([
                Forms\Components\ToggleButtons::make('print_via_bluetooth')
                    ->required()
                    ->label('Tipe Print')
                    ->options([
                        // 0 => 'Kabel (Server Local)', // DISABLED - Error
                        1 => 'Bluetooth'
                    ])
                    ->default(1)
                    ->grouped()
                    ->helperText('⚠️ Print via kabel sementara di-disable. Gunakan Bluetooth printer.')
                    ->disabled() // Lock to bluetooth only
                    ->dehydrated(true),
                Forms\Components\TextInput::make('name_printer_local')
                    ->maxLength(255)
                    ->label('Nama Printer (Khusus untuk kabel)')
                    ->helperText('Fitur print via kabel sementara dinonaktifkan')
                    ->disabled()
                    ->hidden(), // Always hide since cable print is disabled
                Forms\Components\FileUpload::make('logo')
                    ->disk('public')
                    ->image()
                    ->required()
                    ->helperText('Pastikan format gambar adalah PNG')
                    ->directory('logos')
                    ->label('Logo Toko'),
                Forms\Components\Toggle::make('auto_print')
                    ->label('Auto Print Struk (Bluetooth)')
                    ->helperText('Otomatis print struk setelah checkout via bluetooth (tanpa konfirmasi)')
                    ->default(false),
                ]),
                Forms\Components\Section::make('Footer Struk')
                    ->description('Atur teks yang muncul di bagian bawah struk belanja')
                    ->schema([
                        Forms\Components\Textarea::make('receipt_footer_line1')
                            ->label('Baris 1')
                            ->placeholder('Contoh: Terima kasih atas kunjungan Anda')
                            ->rows(2)
                            ->maxLength(100)
                            ->helperText('Maksimal 100 karakter'),

                        Forms\Components\Textarea::make('receipt_footer_line2')
                            ->label('Baris 2')
                            ->placeholder('Contoh: Jl. Merdeka No. 123, Jakarta')
                            ->rows(2)
                            ->maxLength(100)
                            ->helperText('Maksimal 100 karakter'),

                        Forms\Components\Textarea::make('receipt_footer_line3')
                            ->label('Baris 3')
                            ->placeholder('Contoh: WA: 0812-3456-7890 | IG: @tokoku')
                            ->rows(2)
                            ->maxLength(100)
                            ->helperText('Maksimal 100 karakter'),

                        Forms\Components\Textarea::make('receipt_footer_note')
                            ->label('Catatan Footer')
                            ->placeholder('Contoh: Barang yang sudah dibeli tidak dapat dikembalikan')
                            ->rows(3)
                            ->maxLength(200)
                            ->helperText('Maksimal 200 karakter - untuk informasi penting'),

                        Forms\Components\Toggle::make('show_footer_thank_you')
                            ->label('Tampilkan "Terima Kasih"')
                            ->helperText('Tampilkan teks "Terima Kasih" di akhir struk')
                            ->default(true),

                        Forms\Components\Placeholder::make('footer_preview')
                            ->label('Preview Footer Struk')
                            ->content(function (Get $get) {
                                $preview = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

                                if ($get('receipt_footer_line1')) {
                                    $preview .= $get('receipt_footer_line1') . "\n";
                                }
                                if ($get('receipt_footer_line2')) {
                                    $preview .= $get('receipt_footer_line2') . "\n";
                                }
                                if ($get('receipt_footer_line3')) {
                                    $preview .= $get('receipt_footer_line3') . "\n";
                                }
                                if ($get('receipt_footer_note')) {
                                    $preview .= "\n" . $get('receipt_footer_note') . "\n";
                                }
                                if ($get('show_footer_thank_you')) {
                                    $preview .= "\n*** TERIMA KASIH ***\n";
                                }

                                $preview .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

                                return $preview;
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular()
                    ->label('Logo Toko'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Toko')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat Toko')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor Telepon')
                    ->searchable(),
                Tables\Columns\IconColumn::make('print_via_bluetooth')
                    ->label('Print Via Bluetooth')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return Setting::count() < 1;
    }
}
