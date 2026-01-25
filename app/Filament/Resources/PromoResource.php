<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoResource\Pages;
use App\Filament\Resources\PromoResource\RelationManagers;
use App\Models\Promo;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromoResource extends Resource
{
    protected static ?string $model = Promo::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'force_delete',
            'force_delete_any',
        ];
    }

    protected static ?string $navigationGroup = 'Menejemen Produk';

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Promo Tebus Murah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Promo')
                    ->description('Atur detail promo tebus murah')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Promo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Tebus Murah Tomat'),
                        Forms\Components\TextInput::make('description')
                            ->label('Deskripsi')
                            ->maxLength(255)
                            ->placeholder('Deskripsi singkat tentang promo ini'),
                        Forms\Components\ToggleButtons::make('type')
                            ->label('Tipe Promo')
                            ->options([
                                'buy_x_get_discount' => 'Beli X - Dapat Diskon',
                                'bundle' => 'Bundle',
                            ])
                            ->inline()
                            ->default('buy_x_get_discount')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Syarat Promo')
                    ->description('Tentukan produk dan jumlah pembelian untuk mengaktifkan promo')
                    ->schema([
                        Forms\Components\Select::make('trigger_product_id')
                            ->label('Produk yang Harus Dibeli')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        Forms\Components\TextInput::make('trigger_quantity')
                            ->label('Jumlah Pembelian')
                            ->type('text')
                            ->default('1')
                            ->rules(['required', 'regex:/^[0-9]*[.,]?[0-9]*$/'])
                            ->formatStateUsing(fn ($state, Forms\Get $get) =>
                                $get('trigger_unit') === 'qty'
                                    ? number_format(floatval($state), 0, ',', '.')
                                    : $state
                            )
                            ->mutateDehydratedStateUsing(function ($state) {
                                // Parse number, handling both dot and comma as decimal separator
                                $state = trim($state);
                                $state = str_replace(',', '.', $state); // Normalize decimal separator
                                $parsed = floatval($state);

                                if ($parsed <= 0) {
                                    throw new \InvalidArgumentException('Jumlah pembelian harus lebih besar dari 0');
                                }

                                return $parsed;
                            })
                            ->helperText(fn (Forms\Get $get) =>
                                $get('trigger_unit') === 'kg'
                                    ? 'Masukkan dalam satuan kg (contoh: 1 untuk 1 kg, 0.5 untuk 0.5 kg)'
                                    : 'Masukkan jumlah item (contoh: 12 untuk 12 item)'
                            ),
                        Forms\Components\ToggleButtons::make('trigger_unit')
                            ->label('Unit Pembelian')
                            ->options([
                                'qty' => 'Per Item',
                                'kg' => 'Per Kg',
                            ])
                            ->inline()
                            ->default('qty')
                            ->helperText('Untuk unit "Per Kg", masukkan jumlah dalam kg (bukan gram). Contoh: 1 = 1 kg, 0.5 = 0.5 kg'),
                        Forms\Components\TextInput::make('minimum_purchase')
                            ->label('Minimum Total Belanja (Rp)')
                            ->helperText('Jika diisi, promo berlaku saat total belanja mencapai nominal ini')
                            ->numeric()
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Nilai Diskon')
                    ->description('Tentukan besarnya diskon yang diberikan')
                    ->schema([
                        Forms\Components\ToggleButtons::make('discount_type')
                            ->label('Tipe Diskon')
                            ->options([
                                'percentage' => 'Persentase (%)',
                                'fixed' => 'Nominal (Rp)',
                                'price' => 'Harga Spesial (Rp)',
                            ])
                            ->inline()
                            ->required()
                            ->live()
                            ->default('percentage'),
                        Forms\Components\TextInput::make('discount_value')
                            ->label(fn (Forms\Get $get) => match($get('discount_type')) {
                                'percentage' => 'Persentase Diskon (%)',
                                'fixed' => 'Nominal Diskon (Rp)',
                                'price' => 'Harga Spesial (Rp)',
                                default => 'Nilai Diskon',
                            })
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('apply_to_product_id')
                            ->label('Produk yang Dapat Diskon')
                            ->helperText('Untuk promo produk spesifik')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Select::make('free_product_id')
                            ->label('Produk Gratis/Diskon')
                            ->helperText('Untuk promo minimum purchase')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        Forms\Components\TextInput::make('free_quantity')
                            ->label('Jumlah Produk Gratis')
                            ->type('text')
                            ->rules(['nullable', 'regex:/^[0-9]*[.,]?[0-9]*$/'])
                            ->formatStateUsing(fn ($state) =>
                                $state ? (string)$state : ''
                            )
                            ->mutateDehydratedStateUsing(function ($state) {
                                if (!$state) return null;
                                // Parse number, handling both dot and comma as decimal separator
                                $state = trim($state);
                                $state = str_replace(',', '.', $state); // Normalize decimal separator
                                $parsed = floatval($state);

                                if ($parsed <= 0) {
                                    throw new \InvalidArgumentException('Jumlah produk gratis harus lebih besar dari 0');
                                }

                                return $parsed;
                            })
                            ->helperText('Masukkan dalam satuan kg jika produk per kg')
                            ->nullable(),
                        Forms\Components\TextInput::make('max_discount_per_transaction')
                            ->label('Maximum Diskon per Transaksi (Rp)')
                            ->helperText('Biarkan kosong jika tidak ada limit')
                            ->numeric()
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Periode Promo')
                    ->description('Tentukan tanggal berlaku promo')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Tanggal Berakhir')
                            ->nullable(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Promo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Promo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('triggerProduct.name')
                    ->label('Produk')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('trigger_quantity')
                    ->label('Qty')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->trigger_unit === 'qty'
                            ? number_format(floatval($state), 0, ',', '.') . ' ' . $record->trigger_unit
                            : rtrim(rtrim(number_format(floatval($state), 3, ',', '.'), '0'), ',') . ' ' . $record->trigger_unit
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Tipe Diskon')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'percentage' => 'Persentase',
                        'fixed' => 'Nominal',
                        'price' => 'Harga Spesial',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Nilai')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->discount_type === 'percentage'
                            ? $state . '%'
                            : 'Rp ' . number_format($state, 0, ',', '.')
                    )
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPromos::route('/'),
            'create' => Pages\CreatePromo::route('/create'),
            'edit' => Pages\EditPromo::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
