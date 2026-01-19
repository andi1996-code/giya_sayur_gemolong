<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierDebtResource\Pages;
use App\Filament\Resources\SupplierDebtResource\RelationManagers;
use App\Models\SupplierDebt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;

class SupplierDebtResource extends Resource
{
    protected static ?string $model = SupplierDebt::class;

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

    protected static ?string $navigationGroup = 'Menejemen keuangan';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Piutang Supplier';

    protected static ?string $modelLabel = 'Piutang Supplier';

    protected static ?string $pluralModelLabel = 'Piutang Supplier';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Pesan informasi untuk edit mode
                Forms\Components\Placeholder::make('edit_info')
                    ->label('Informasi')
                    ->content('⚠️ **Catatan**: Data supplier debt tidak dapat diubah setelah dibuat untuk menjaga integritas data stok dan keuangan. Jika perlu koreksi, silakan hapus record ini dan buat yang baru.')
                    ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                    ->columnSpanFull(),

                Section::make('Informasi Supplier')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('supplier_name')
                                    ->label('Nama Supplier')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt),

                                Forms\Components\TextInput::make('supplier_phone')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->maxLength(20)
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt),
                            ]),

                        Forms\Components\Textarea::make('supplier_address')
                            ->label('Alamat Supplier')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt),
                    ]),

                Section::make('Detail Transaksi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('transaction_type')
                                    ->label('Jenis Transaksi')
                                    ->options([
                                        'hutang' => 'Hutang (Kita berhutang)',
                                        'piutang' => 'Piutang (Supplier berhutang)',
                                    ])
                                    ->required()
                                    ->live()
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                                    ->afterStateUpdated(function ($state, $set) {
                                        // Auto update remaining amount when type changes
                                        $set('remaining_amount', $state ? 0 : 0);
                                    }),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Jumlah Total')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->lazy()
                                    ->inputMode('numeric')
                                    ->helperText('Masukkan jumlah total hutang/piutang')
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $paidAmount = $get('paid_amount') ?? 0;
                                        $remaining = ($state ?? 0) - $paidAmount;
                                        $set('remaining_amount', max(0, $remaining));
                                    }),

                                Forms\Components\TextInput::make('paid_amount')
                                    ->label('Jumlah Dibayar')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->minValue(0)
                                    ->placeholder('0')
                                    ->inputMode('numeric')
                                    ->helperText('Kosongkan atau isi 0 jika belum ada pembayaran')
                                    ->lazy()
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $totalAmount = $get('amount') ?? 0;
                                        $remaining = $totalAmount - ($state ?? 0);
                                        $set('remaining_amount', max(0, $remaining));
                                    })
                                    ->dehydrateStateUsing(fn ($state) => $state ?? 0),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                                    ->helperText('Pilih produk yang terkait dengan transaksi ini'),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Kuantitas')
                                    ->required()
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0.01)
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                                    ->helperText('Jumlah produk dalam transaksi'),

                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('kg, pcs, liter, dll')
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                                    ->helperText('Satuan unit produk'),
                            ]),

                        Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('stock_type')
                                    ->label('Jenis Stok')
                                    ->options([
                                        'regular' => 'Stok Reguler',
                                        'kongsi' => 'Stok Kongsi (Titipan)',
                                    ])
                                    ->default('regular')
                                    ->required()
                                    ->live()
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                                    ->helperText(function ($get) {
                                        $transactionType = $get('transaction_type');
                                        if ($transactionType === 'hutang') {
                                            return 'Reguler: Barang milik sendiri | Kongsi: Barang titipan dari supplier';
                                        } else {
                                            return 'Reguler: Ambil dari stok milik sendiri | Kongsi: Ambil dari stok titipan';
                                        }
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('transaction_date')
                                    ->label('Tanggal Transaksi')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Jatuh Tempo')
                                    ->native(false)
                                    ->after('transaction_date')
                                    ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt),

                        Forms\Components\Placeholder::make('stock_info')
                            ->label('Informasi Stok')
                            ->content(function ($get) {
                                $transactionType = $get('transaction_type');
                                $stockType = $get('stock_type') ?? 'regular';
                                $stockTypeLabel = $stockType === 'kongsi' ? 'Stok Kongsi (Titipan)' : 'Stok Reguler';

                                if ($transactionType === 'piutang') {
                                    return "⚠️ **Catatan Stok**: Ketika piutang dibuat, {$stockTypeLabel} produk akan **BERKURANG** otomatis karena barang telah diberikan kepada supplier.";
                                } elseif ($transactionType === 'hutang') {
                                    return "✅ **Catatan Stok**: Ketika hutang dibuat, {$stockTypeLabel} produk akan **BERTAMBAH** otomatis karena supplier memberikan barang kepada kita.";
                                }
                                return 'Pilih jenis transaksi untuk melihat informasi stok.';
                            })
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('remaining_amount_display')
                            ->label('Sisa Hutang/Piutang')
                            ->live()
                            ->content(function ($get) {
                                $total = $get('amount') ?? 0;
                                $paid = $get('paid_amount') ?? 0;
                                $remaining = max(0, $total - $paid);
                                return 'Rp ' . number_format($remaining, 0, ',', '.');
                            }),
                    ]),

                Section::make('Catatan & Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'belum_lunas' => 'Belum Lunas',
                                'sebagian_lunas' => 'Sebagian Lunas',
                                'lunas' => 'Lunas',
                            ])
                            ->default('belum_lunas')
                            ->required()
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                            ->helperText(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt ? 'Status akan otomatis terupdate melalui tombol "Bayar"' : ''),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->helperText(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt ? 'Catatan pembayaran akan otomatis ditambahkan melalui tombol "Bayar"' : '')
                            ->disabled(fn ($livewire) => $livewire instanceof \App\Filament\Resources\SupplierDebtResource\Pages\EditSupplierDebt)
                            ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),

                        Forms\Components\Hidden::make('remaining_amount')
                            ->default(0),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium'),

                Tables\Columns\TextColumn::make('supplier_phone')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->formatStateUsing(fn($state, $record) => number_format($state, 2) . ' ' . $record->unit)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('stock_type')
                    ->label('Jenis Stok')
                    ->colors([
                        'primary' => 'regular',
                        'warning' => 'kongsi',
                    ])
                    ->icons([
                        'heroicon-o-cube' => 'regular',
                        'heroicon-o-hand-raised' => 'kongsi',
                    ])
                    ->formatStateUsing(fn($state) => $state === 'kongsi' ? 'Kongsi' : 'Reguler')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('transaction_type')
                    ->label('Jenis')
                    ->colors([
                        'danger' => 'hutang',
                        'success' => 'piutang',
                    ])
                    ->icons([
                        'heroicon-o-arrow-down' => 'hutang',
                        'heroicon-o-arrow-up' => 'piutang',
                    ]),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->money('IDR')
                    ->color(fn($record) => $record->remaining_amount > 0 ? 'warning' : 'success')
                    ->weight('font-bold')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'belum_lunas',
                        'warning' => 'sebagian_lunas',
                        'success' => 'lunas',
                    ]),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : 'gray')
                    ->weight(fn($record) => $record->isOverdue() ? 'font-bold' : 'font-normal')
                    ->placeholder('Tidak ada')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('transaction_type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'hutang' => 'Hutang',
                        'piutang' => 'Piutang',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'belum_lunas' => 'Belum Lunas',
                        'sebagian_lunas' => 'Sebagian Lunas',
                        'lunas' => 'Lunas',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->label('Jatuh Tempo')
                    ->query(fn (Builder $query): Builder => $query->overdue()),

                Tables\Filters\Filter::make('belum_lunas')
                    ->label('Belum Lunas')
                    ->query(fn (Builder $query): Builder => $query->belumLunas()),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadNote')
                    ->label('Nota')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn($record) => route('supplier-debt.note', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('pay')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'lunas')
                    ->form([
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('Jumlah Pembayaran')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->maxValue(fn($record) => $record->remaining_amount),

                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Catatan Pembayaran')
                            ->rows(3),
                    ])
                    ->action(function ($record, $data) {
                        $record->paid_amount += $data['payment_amount'];
                        $record->remaining_amount = $record->amount - $record->paid_amount;

                        if ($record->remaining_amount <= 0) {
                            $record->status = 'lunas';
                            $record->remaining_amount = 0;
                        } elseif ($record->paid_amount > 0) {
                            $record->status = 'sebagian_lunas';
                        }

                        if ($data['payment_notes']) {
                            $record->notes .= "\n[" . now()->format('d M Y H:i') . "] Pembayaran Rp " . number_format($data['payment_amount'], 0, ',', '.') . " - " . $data['payment_notes'];
                        }

                        $record->save();
                    })
                    ->requiresConfirmation(),

                Tables\Actions\ViewAction::make(),

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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListSupplierDebts::route('/'),
            'create' => Pages\CreateSupplierDebt::route('/create'),
            'view' => Pages\ViewSupplierDebt::route('/{record}'),
            'edit' => Pages\EditSupplierDebt::route('/{record}/edit'),
        ];
    }
}
