<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardRedemptionResource\Pages;
use App\Filament\Resources\RewardRedemptionResource\RelationManagers;
use App\Models\RewardRedemption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class RewardRedemptionResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    protected static ?string $model = RewardRedemption::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Riwayat Tukar Poin';

    protected static ?string $navigationGroup = 'Member & Poin';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Penukaran Reward';

    protected static ?string $pluralModelLabel = 'Riwayat Tukar Poin';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'completed')->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penukaran')
                    ->schema([
                        Forms\Components\Select::make('member_id')
                            ->label('Member')
                            ->relationship('member', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->phone}")
                            ->searchable(['name', 'phone', 'code'])
                            ->required()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Reset poin saat ganti member
                                $set('points_used', null);
                            }),

                        Forms\Components\Placeholder::make('member_points')
                            ->label('Poin Tersedia')
                            ->content(function (Forms\Get $get): \Illuminate\Support\HtmlString {
                                $memberId = $get('member_id');
                                if (!$memberId) {
                                    return new \Illuminate\Support\HtmlString('-');
                                }
                                $member = \App\Models\Member::find($memberId);
                                if (!$member) {
                                    return new \Illuminate\Support\HtmlString('-');
                                }
                                $points = number_format($member->total_points, 0, ',', '.');
                                $color = $member->total_points > 0 ? 'text-success-600' : 'text-danger-600';
                                return new \Illuminate\Support\HtmlString("<span class='font-bold text-lg {$color}'>{$points} poin</span>");
                            })
                            ->visible(fn(Forms\Get $get) => $get('member_id') !== null)
                            ->columnSpan(1),

                        Forms\Components\Select::make('product_id')
                            ->label('Produk Reward')
                            ->relationship('product', 'name', fn($query) => $query->where('is_reward', true))
                            ->searchable()
                            ->required()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    $product = \App\Models\Product::find($state);
                                    if ($product && $product->points_required) {
                                        $quantity = $get('quantity') ?? 1;
                                        $set('points_used', $product->points_required * $quantity);
                                    }
                                }
                            })
                            ->helperText('Pilih produk reward yang akan ditukar'),

                        Forms\Components\Placeholder::make('product_info')
                            ->label('Info Produk')
                            ->content(function (Forms\Get $get): \Illuminate\Support\HtmlString {
                                $productId = $get('product_id');
                                if (!$productId) {
                                    return new \Illuminate\Support\HtmlString('-');
                                }
                                $product = \App\Models\Product::find($productId);
                                if (!$product) {
                                    return new \Illuminate\Support\HtmlString('-');
                                }

                                $info = "<div class='space-y-1 text-sm'>";
                                $info .= "<div><strong>Stok:</strong> <span class='text-primary-600 font-semibold'>{$product->stock}</span></div>";
                                $info .= "<div><strong>Poin/item:</strong> <span class='text-warning-600 font-semibold'>{$product->points_required} poin</span></div>";

                                if ($product->max_redeem_per_member) {
                                    $memberId = $get('member_id');
                                    if ($memberId) {
                                        $redeemed = \App\Models\RewardRedemption::where('member_id', $memberId)
                                            ->where('product_id', $product->id)
                                            ->where('status', 'completed')
                                            ->sum('quantity');
                                        $remaining = $product->max_redeem_per_member - $redeemed;
                                        $color = $remaining > 0 ? 'text-success-600' : 'text-danger-600';
                                        $info .= "<div><strong>Limit:</strong> {$product->max_redeem_per_member}x per member (Sisa: <span class='font-semibold {$color}'>{$remaining}x</span>)</div>";
                                    } else {
                                        $info .= "<div><strong>Limit:</strong> {$product->max_redeem_per_member}x per member</div>";
                                    }
                                }

                                $info .= "</div>";
                                return new \Illuminate\Support\HtmlString($info);
                            })
                            ->visible(fn(Forms\Get $get) => $get('product_id') !== null)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('points_used')
                            ->label('Poin Digunakan')
                            ->required()
                            ->numeric()
                            ->suffix('poin')
                            ->minValue(1)
                            ->helperText('Otomatis terisi dari produk yang dipilih'),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->suffix('item')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $productId = $get('product_id');
                                if ($productId && $state) {
                                    $product = \App\Models\Product::find($productId);
                                    if ($product && $product->points_required) {
                                        $set('points_used', $product->points_required * (int)$state);
                                    }
                                }
                            })
                            ->helperText('Total poin akan dikalikan dengan jumlah'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('completed'),

                        // Field transaction_id dihidden karena akan auto-fill dari POS
                        // Jika ditukar via admin, berarti memang tidak ada transaksi terkait
                        Forms\Components\Hidden::make('transaction_id'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Contoh: Ditukar di kasir 1, member datang langsung'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name')
                    ->label('Member')
                    ->description(fn($record) => $record->member->code ?? '')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk Reward')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('points_used')
                    ->label('Poin')
                    ->numeric()
                    ->suffix(' poin')
                    ->sortable()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('transaction.transaction_number')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processedBy.name')
                    ->label('Diproses Oleh')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),

                Tables\Filters\SelectFilter::make('member_id')
                    ->label('Member')
                    ->relationship('member', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListRewardRedemptions::route('/'),
            'view' => Pages\ViewRewardRedemption::route('/{record}'),
        ];
    }
}
