<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductDiscountResource\Pages;
use App\Filament\Resources\ProductDiscountResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\FontWeight;

class ProductDiscountResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

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

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kelola Diskon';

    protected static ?string $modelLabel = 'Diskon Produk';

    protected static ?string $pluralModelLabel = 'Diskon Produk';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Produk')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('id')
                                    ->label('Produk')
                                    ->options(Product::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                // Prioritaskan price_per_kg jika price null
                                                $displayPrice = $product->price ?? $product->price_per_kg ?? 0;
                                                $priceLabel = $product->price ? 'Rp ' . number_format($product->price, 0, ',', '.')
                                                            : ($product->price_per_kg ? 'Rp ' . number_format($product->price_per_kg, 0, ',', '.') . '/kg'
                                                            : 'Rp 0');

                                                $set('current_price_display', $priceLabel);
                                                $set('discount_percentage', $product->discount_percentage ?? 0);
                                                $set('discount_active', $product->discount_active ?? false);
                                                $set('discount_start_date', $product->discount_start_date);
                                                $set('discount_end_date', $product->discount_end_date);
                                            }
                                        } else {
                                            $set('current_price_display', 'Pilih produk terlebih dahulu');
                                        }
                                    }),

                                Forms\Components\Placeholder::make('current_price_display')
                                    ->label('Harga Saat Ini')
                                    ->content(function ($get, $record) {
                                        if ($record) {
                                            // Prioritaskan price_per_kg jika price null
                                            if ($record->price) {
                                                return 'Rp ' . number_format($record->price, 0, ',', '.');
                                            } elseif ($record->price_per_kg) {
                                                return 'Rp ' . number_format($record->price_per_kg, 0, ',', '.') . '/kg';
                                            }
                                            return 'Rp 0';
                                        }
                                        return $get('current_price_display') ?? 'Pilih produk terlebih dahulu';
                                    }),
                            ]),
                    ]),

                Section::make('Pengaturan Diskon')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('discount_percentage')
                                    ->label('Persentase Diskon (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                                        $productId = $get('id');
                                        $originalPrice = null;
                                        $isPerKg = false;

                                        if ($record) {
                                            $originalPrice = $record->price ?? $record->price_per_kg;
                                            $isPerKg = !$record->price && $record->price_per_kg;
                                        } elseif ($productId) {
                                            $product = Product::find($productId);
                                            if ($product) {
                                                $originalPrice = $product->price ?? $product->price_per_kg;
                                                $isPerKg = !$product->price && $product->price_per_kg;
                                            }
                                        }

                                        if ($originalPrice && $state) {
                                            $discountAmount = ($originalPrice * $state) / 100;
                                            $finalPrice = $originalPrice - $discountAmount;
                                            $priceDisplay = 'Rp ' . number_format($finalPrice, 0, ',', '.') . ($isPerKg ? '/kg' : '');
                                            $set('preview_price_display', $priceDisplay);
                                        } else {
                                            $set('preview_price_display', 'Masukkan persentase diskon');
                                        }
                                    }),

                                Forms\Components\Toggle::make('discount_active')
                                    ->label('Aktifkan Diskon')
                                    ->default(false)
                                    ->helperText('Aktifkan untuk menerapkan diskon pada produk'),
                            ]),

                        Forms\Components\Placeholder::make('preview_price_display')
                            ->label('Harga Setelah Diskon')
                            ->content(function ($get, $record) {
                                $discountPercentage = $get('discount_percentage');
                                $productId = $get('id');
                                $originalPrice = null;
                                $isPerKg = false;

                                if ($record) {
                                    $originalPrice = $record->price ?? $record->price_per_kg;
                                    $isPerKg = !$record->price && $record->price_per_kg;
                                } elseif ($productId) {
                                    $product = Product::find($productId);
                                    if ($product) {
                                        $originalPrice = $product->price ?? $product->price_per_kg;
                                        $isPerKg = !$product->price && $product->price_per_kg;
                                    }
                                }

                                if ($originalPrice && $discountPercentage) {
                                    $discount = ($originalPrice * $discountPercentage) / 100;
                                    $finalPrice = $originalPrice - $discount;
                                    return 'Rp ' . number_format($finalPrice, 0, ',', '.') . ($isPerKg ? '/kg' : '');
                                }

                                return $get('preview_price_display') ?? 'Masukkan persentase diskon';
                            }),
                    ]),

                Section::make('Periode Diskon (Opsional)')
                    ->description('Kosongkan untuk diskon permanen')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('discount_start_date')
                                    ->label('Tanggal Mulai')
                                    ->native(false),

                                Forms\Components\DateTimePicker::make('discount_end_date')
                                    ->label('Tanggal Berakhir')
                                    ->native(false)
                                    ->after('discount_start_date'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Asli')
                    ->money('IDR')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Tampilkan price jika ada, jika tidak tampilkan price_per_kg
                        $basePrice = $record->price ?? $record->price_per_kg ?? 0;
                        return $basePrice;
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->price) {
                            return 'Rp ' . number_format($state, 0, ',', '.');
                        } elseif ($record->price_per_kg) {
                            return 'Rp ' . number_format($state, 0, ',', '.') . '/kg';
                        }
                        return 'Rp 0';
                    }),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Diskon')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($record) => $record->discount_percentage > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('Harga Final')
                    ->getStateUsing(fn ($record) => $record->getFinalPrice())
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->price) {
                            return 'Rp ' . number_format($state, 0, ',', '.');
                        } elseif ($record->price_per_kg) {
                            return 'Rp ' . number_format($state, 0, ',', '.') . '/kg';
                        }
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    })
                    ->color('success')
                    ->weight(FontWeight::Bold),

                Tables\Columns\ToggleColumn::make('discount_active')
                    ->label('Status Diskon')
                    ->onColor('success')
                    ->offColor('gray'),

                Tables\Columns\TextColumn::make('discount_start_date')
                    ->label('Mulai')
                    ->dateTime('d M Y')
                    ->placeholder('Permanen')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('discount_end_date')
                    ->label('Berakhir')
                    ->dateTime('d M Y')
                    ->placeholder('Permanen')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('discount_active')
                    ->label('Status Diskon')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ]),

                Tables\Filters\Filter::make('has_discount')
                    ->label('Memiliki Diskon')
                    ->query(fn (Builder $query): Builder => $query->where('discount_percentage', '>', 0)),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit Diskon'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate_discount')
                        ->label('Aktifkan Diskon')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['discount_active' => true]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('deactivate_discount')
                        ->label('Nonaktifkan Diskon')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['discount_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListProductDiscounts::route('/'),
            'create' => Pages\CreateProductDiscount::route('/create'),
            'edit' => Pages\EditProductDiscount::route('/{record}/edit'),
        ];
    }
}
