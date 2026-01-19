<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockTable extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Produk Stok Menipis';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('stock', '<=', 5)
                    ->where('is_active', true)
                    ->orderBy('stock', 'asc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 2 => 'warning',
                        $state <= 5 => 'info',
                        default => 'success',
                    })
                    ->icon(fn (int $state): string => match (true) {
                        $state === 0 => 'heroicon-m-x-circle',
                        $state <= 2 => 'heroicon-m-exclamation-triangle',
                        $state <= 5 => 'heroicon-m-information-circle',
                        default => 'heroicon-m-check-circle',
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price_per_kg')
                    ->label('Harga/Kg')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('N/A'),
            ])
            ->actions([
                Tables\Actions\Action::make('manage_stock')
                    ->label('Kelola Stok')
                    ->icon('heroicon-o-cube')
                    ->color('success')
                    ->url(fn (Product $record): string => route('filament.admin.resources.inventories.index'))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Tidak Ada Produk dengan Stok Menipis')
            ->emptyStateDescription('Semua produk memiliki stok yang cukup.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
