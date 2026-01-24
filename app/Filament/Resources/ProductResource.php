<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms\Form;
use Milon\Barcode\DNS1D;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class ProductResource extends Resource implements HasShieldPermissions
{
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
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Menejemen Produk';

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Daftar Produk';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ])->orderBy('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori Produk')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('cost_price')
                    ->label('Harga Modal')
                    ->required()
                    ->prefix('Rp ')
                    ->extraInputAttributes([
                        'class' => 'price-input',
                        'x-data' => '',
                        'x-on:input' => "
                            let val = \$event.target.value.replace(/[^0-9]/g, '');
                            if (val) {
                                \$event.target.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        ",
                    ])
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                    ->dehydrateStateUsing(fn($state) => $state ? (int) preg_replace('/[^0-9]/', '', $state) : null),
                Forms\Components\TextInput::make('price')
                    ->label('Harga Jual unit')
                    ->requiredWithout('price_per_kg')
                    ->prefix('Rp ')
                    ->extraInputAttributes([
                        'class' => 'price-input',
                        'x-data' => '',
                        'x-on:input' => "
                            let val = \$event.target.value.replace(/[^0-9]/g, '');
                            if (val) {
                                \$event.target.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        ",
                    ])
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                    ->dehydrateStateUsing(fn($state) => $state ? (int) preg_replace('/[^0-9]/', '', $state) : null),
                Forms\Components\TextInput::make('price_per_kg')
                    ->label('Harga per Kg')
                    ->requiredWithout('price')
                    ->prefix('Rp ')
                    ->extraInputAttributes([
                        'class' => 'price-input',
                        'x-data' => '',
                        'x-on:input' => "
                            let val = \$event.target.value.replace(/[^0-9]/g, '');
                            if (val) {
                                \$event.target.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        ",
                    ])
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                    ->dehydrateStateUsing(fn($state) => $state ? (int) preg_replace('/[^0-9]/', '', $state) : null),
                Forms\Components\FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->disk('public')
                    ->directory('products')
                    ->helperText('jika tidak diisi akan diisi foto default')
                    ->image(),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok Reguler')
                    ->helperText('Stok hanya dapat diisi/ditambah pada menejemen inventori atau supplier debt')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->default(0),
                Forms\Components\TextInput::make('stok_kongsi')
                    ->label('Stok Kongsi (Titipan)')
                    ->helperText('Stok titipan dari supplier')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->default(0),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->helperText('jika tidak diisi akan di generate otomatis')
                    ->maxLength(255),
                Forms\Components\TextInput::make('barcode')
                    ->label('Kode Barcode')
                    ->numeric()
                    ->helperText('jika tidak diisi akan di generate otomatis')
                    ->maxLength(255)
                    ->unique('products', 'barcode', ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Kode barcode ini sudah digunakan oleh produk lain. Gunakan kode yang unik.',
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->label('Produk Aktif')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi Produk')
                    ->columnSpanFull(),

                // Section Produk Reward
                Forms\Components\Section::make('Produk Reward (Tukar Poin)')
                    ->description('Atur produk ini sebagai hadiah yang bisa ditukar dengan poin member')
                    ->schema([
                        Forms\Components\Toggle::make('is_reward')
                            ->label('Produk Reward')
                            ->helperText('Aktifkan jika produk ini bisa ditukar dengan poin (bukan dijual dengan uang)')
                            ->live()
                            ->default(false),

                        Forms\Components\TextInput::make('points_required')
                            ->label('Poin Dibutuhkan')
                            ->helperText('Jumlah poin yang dibutuhkan untuk menukar produk ini')
                            ->numeric()
                            ->required(fn(Forms\Get $get) => $get('is_reward'))
                            ->visible(fn(Forms\Get $get) => $get('is_reward'))
                            ->minValue(1)
                            ->suffix('poin'),

                        Forms\Components\TextInput::make('max_redeem_per_member')
                            ->label('Maksimal Tukar per Member')
                            ->helperText('Batasi berapa kali member bisa tukar produk ini. Kosongkan untuk unlimited.')
                            ->numeric()
                            ->visible(fn(Forms\Get $get) => $get('is_reward'))
                            ->minValue(1)
                            ->suffix('kali'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->description(fn(Product $record): string => $record->category()->withTrashed()->value('name'))
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Reguler')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stok_kongsi')
                    ->label('Stok Kongsi')
                    ->numeric()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Total Stok')
                    ->getStateUsing(fn($record) => ($record->stock ?? 0) + ($record->stok_kongsi ?? 0))
                    ->color('primary')
                    ->weight('bold')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Harga Modal')
                    ->prefix('Rp ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Jual unit')
                    ->prefix('Rp ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_kg')
                    ->label('Harga per Kg')
                    ->prefix('Rp ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('No.Barcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_reward')
                    ->label('Reward')
                    ->boolean()
                    ->trueIcon('heroicon-o-gift')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('points_required')
                    ->label('Poin')
                    ->suffix(' poin')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn($livewire) => $livewire->tableFilters['is_reward']['value'] ?? false),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Produk Aktif'),
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
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_reward')
                    ->label('Produk Reward')
                    ->placeholder('Semua Produk')
                    ->trueLabel('Hanya Produk Reward')
                    ->falseLabel('Hanya Produk Biasa')
                    ->queries(
                        true: fn($query) => $query->where('is_reward', true),
                        false: fn($query) => $query->where('is_reward', false),
                    ),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Produk')
                    ->placeholder('Semua Produk')
                    ->trueLabel('Hanya Aktif')
                    ->falseLabel('Hanya Non-Aktif')
                    ->queries(
                        true: fn($query) => $query->where('is_active', true),
                        false: fn($query) => $query->where('is_active', false),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('Reset Stok')
                    ->action(fn(Product $record) => $record->update(['stock' => 0]))
                    ->button()
                    ->color('info')
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make()
                    ->button(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\BulkAction::make('printBarcodes')
                    ->label('Cetak Barcode')
                    ->button()
                    ->icon('heroicon-o-printer')
                    ->form([
                        Forms\Components\Select::make('barcode_format')
                            ->label('Pilih Format Barcode')
                            ->options([
                                'label_33x15' => 'Label Produk (33 × 15 mm) - Untuk stiker produk',
                                'label_60x30' => 'Label Rak (60 × 30 mm) - Untuk label harga rak',
                            ])
                            ->required()
                            ->default('label_33x15'),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah Cetak per Produk')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->helperText('Masukkan berapa banyak label yang ingin dicetak untuk setiap produk'),
                    ])
                    ->action(function ($records, $data) {
                        return self::generateBulkBarcode($records, $data['barcode_format'], $data['quantity']);
                    })
                    ->color('success'),

                Tables\Actions\BulkAction::make('Reset Stok')
                    ->action(fn($records) => $records->each->update(['stock' => 0]))
                    ->button()
                    ->color('info')
                    ->requiresConfirmation(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('printBarcodes')
                    ->label('Cetak Barcode')
                    ->icon('heroicon-o-printer')
                    ->form([
                        Forms\Components\Select::make('barcode_format')
                            ->label('Pilih Format Barcode')
                            ->options([
                                'label_33x15' => 'Label Produk (33 × 15 mm) - Untuk stiker produk',
                                'label_60x30' => 'Label Rak (60 × 30 mm) - Untuk label harga rak',
                            ])
                            ->required()
                            ->default('label_33x15'),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah Cetak per Produk')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->helperText('Masukkan berapa banyak label yang ingin dicetak untuk setiap produk'),
                    ])
                    ->action(function ($data) {
                        return self::generateBulkBarcode(Product::all(), $data['barcode_format'], $data['quantity']);
                    })
                    ->color('success'),
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
            'index' => Pages\ListProducts::route('/'),
        ];
    }

    protected static function generateBulkBarcode($records, $format = 'label_33x15', $quantity = 1)
    {
        $barcodes = [];
        $barcodeGenerator = new DNS1D();

        foreach ($records as $product) {
            // Duplicate setiap produk sesuai dengan quantity yang diminta
            for ($i = 0; $i < $quantity; $i++) {
                $barcodes[] = [
                    'name' => $product->name,
                    'price' => $product->price,
                    'barcode' => 'data:image/png;base64,' . $barcodeGenerator->getBarcodePNG($product->barcode, 'C128'),
                    'number' => $product->barcode
                ];
            }
        }

        // Generate PDF dengan passing variable yang benar
        $pdf = Pdf::loadView('pdf.barcodes.barcode', [
            'barcodes' => $barcodes,
            'format' => $format
        ]);

        // Tentukan ukuran kertas berdasarkan format
        if ($format === 'label_33x15') {
            // 33mm x 15mm - thermal label exact size
            $pdf->setPaper([0, 0, 93.54, 42.52], 'portrait'); // 33x15 mm in points (1mm = 2.83465 points)
        } elseif ($format === 'label_60x30') {
            // 60mm x 30mm - rak label exact size
            $pdf->setPaper([0, 0, 170.08, 85.04], 'portrait'); // 60x30 mm in points
        }

        // Set DomPDF options untuk render barcode dengan baik
        $pdf->setOptions([
            'enable_remote' => true,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'dpi' => 300,
        ]);

        // Tentukan nama file
        $filename = match($format) {
            'label_33x15' => 'barcodes_33x15.pdf',
            'label_60x30' => 'barcodes_60x30.pdf',
            default => 'barcodes.pdf',
        };

        // Kembalikan response download
        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }
}
