<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Report;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\ReportResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class ReportResource extends Resource implements HasShieldPermissions
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Menejemen keuangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Laporan')
                ->schema([
                    Forms\Components\ToggleButtons::make('report_type')
                    ->options([
                        'inflow' => 'Uang Masuk',
                        'outflow' => 'Uang Keluar',
                        'sales' => 'Penjualan'
                    ])
                    ->colors([
                        'inflow' => 'success',
                        'outflow' => 'danger',
                        'sales' => 'info'
                    ])
                    // ->icons([
                    //     'pemasukan' => 'heroicon-o-arrow-down-circle',
                    //     'pengeluaran' => 'heroicon-o-arrow-up-circle',
                    // ])
                    ->default('inflow')
                    ->grouped()
                    ->reactive(),
                    Forms\Components\Checkbox::make('simple_view')
                        ->label('Laporan Singkat (hanya total per kategori)')
                        ->helperText('Centang untuk menampilkan laporan tanpa detail produk')
                        ->default(false)
                        ->visible(fn ($get) => $get('report_type') === 'sales'),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('Dari Tanggal')
                                ->required(),
                            Forms\Components\TimePicker::make('start_time')
                                ->label('Dari Jam')
                                ->seconds(false)
                                ->helperText('Opsional: kosongkan untuk mulai dari 00:00'),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('end_date')
                                ->label('Sampai Tanggal')
                                ->required(),
                            Forms\Components\TimePicker::make('end_time')
                                ->label('Sampai Jam')
                                ->seconds(false)
                                ->helperText('Opsional: kosongkan untuk sampai 23:59'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama/Kode Laporan')
                    ->weight('semibold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->label('Tipe Laporan')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'inflow' => 'Uang Masuk',
                        'outflow' => 'Uang Keluar',
                        'sales' => 'Penjualan',
                        default => 'Unknown',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'inflow' => 'heroicon-o-arrow-down-circle',
                        'outflow' => 'heroicon-o-arrow-up-circle',
                        'sales' => 'heroicon-o-arrow-down-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'inflow' => 'success',
                        'outflow' => 'danger',
                        'sales' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Dari')
                    ->formatStateUsing(function ($record) {
                        $date = $record->start_date->format('d/m/Y');
                        $time = $record->start_time ? ' ' . substr($record->start_time, 0, 5) : '';
                        return $date . $time;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Sampai')
                    ->formatStateUsing(function ($record) {
                        $date = $record->end_date->format('d/m/Y');
                        $time = $record->end_time ? ' ' . substr($record->end_time, 0, 5) : '';
                        return $date . $time;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                ->label('Download PDF') // Label di tombol
                ->icon('heroicon-m-arrow-down-tray') // Icon download dari Heroicons
                ->color('primary') // Warna tombol (biru)
                ->url(fn ($record) => route('reports.download', ['id' => $record->id]))
                ->openUrlInNewTab(true), // Membuka URL di tab baru
                Tables\Actions\Action::make('downloadExcel')
                ->label('Download Excel')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->url(fn ($record) => $record->excel_file ? route('reports.download-excel', ['id' => $record->id]) : null)
                ->openUrlInNewTab(true)
                ->visible(fn ($record) => $record->report_type === 'sales' && $record->excel_file),
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
            'index' => Pages\ListReports::route('/'),
        ];
    }
}
