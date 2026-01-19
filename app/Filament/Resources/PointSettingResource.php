<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointSettingResource\Pages;
use App\Filament\Resources\PointSettingResource\RelationManagers;
use App\Models\PointSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class PointSettingResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = PointSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Poin';

    protected static ?string $modelLabel = 'Pengaturan Poin';

    protected static ?string $pluralModelLabel = 'Pengaturan Poin';

    protected static ?string $navigationGroup = 'Member & Poin';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengaturan Dasar Poin')
                    ->schema([
                        Forms\Components\TextInput::make('point_per_amount')
                            ->label('Nominal per Poin')
                            ->helperText('Jumlah belanja untuk mendapat poin (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(10000),

                        Forms\Components\TextInput::make('points_earned')
                            ->label('Poin Diperoleh')
                            ->helperText('Jumlah poin yang didapat per nominal')
                            ->numeric()
                            ->required()
                            ->default(1),

                        Forms\Components\TextInput::make('point_value')
                            ->label('Nilai 1 Poin')
                            ->helperText('Nilai tukar 1 poin dalam Rupiah')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(1000),

                        Forms\Components\TextInput::make('min_points_redeem')
                            ->label('Minimal Penukaran')
                            ->helperText('Minimal poin untuk ditukar')
                            ->numeric()
                            ->required()
                            ->default(10),

                        Forms\Components\TextInput::make('point_expiry_days')
                            ->label('Masa Berlaku')
                            ->helperText('Poin kadaluarsa setelah (hari)')
                            ->numeric()
                            ->suffix('hari')
                            ->required()
                            ->default(365),

                        Forms\Components\Toggle::make('auto_tier_upgrade')
                            ->label('Auto Upgrade Tier')
                            ->helperText('Otomatis upgrade tier berdasarkan total belanja')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Persyaratan Tier (Berdasarkan Total Belanja)')
                    ->schema([
                        Forms\Components\TextInput::make('bronze_min_spent')
                            ->label('Bronze (Min. Belanja)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(0),

                        Forms\Components\TextInput::make('silver_min_spent')
                            ->label('Silver (Min. Belanja)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(1000000),

                        Forms\Components\TextInput::make('gold_min_spent')
                            ->label('Gold (Min. Belanja)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(5000000),

                        Forms\Components\TextInput::make('platinum_min_spent')
                            ->label('Platinum (Min. Belanja)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(10000000),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Multiplier Poin per Tier')
                    ->schema([
                        Forms\Components\TextInput::make('bronze_multiplier')
                            ->label('Bronze Multiplier')
                            ->helperText('Pengali poin untuk tier Bronze')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('x')
                            ->required()
                            ->default(1.0),

                        Forms\Components\TextInput::make('silver_multiplier')
                            ->label('Silver Multiplier')
                            ->helperText('Pengali poin untuk tier Silver')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('x')
                            ->required()
                            ->default(1.2),

                        Forms\Components\TextInput::make('gold_multiplier')
                            ->label('Gold Multiplier')
                            ->helperText('Pengali poin untuk tier Gold')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('x')
                            ->required()
                            ->default(1.5),

                        Forms\Components\TextInput::make('platinum_multiplier')
                            ->label('Platinum Multiplier')
                            ->helperText('Pengali poin untuk tier Platinum')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('x')
                            ->required()
                            ->default(2.0),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Contoh Perhitungan')
                    ->schema([
                        Forms\Components\Placeholder::make('example')
                            ->label('')
                            ->content(function (Forms\Get $get) {
                                $pointPerAmount = $get('point_per_amount') ?? 10000;
                                $pointsEarned = $get('points_earned') ?? 1;
                                $pointValue = $get('point_value') ?? 1000;

                                $example = "Contoh: Belanja Rp 100.000\n";
                                $example .= "- Bronze (1x): " . floor(100000 / $pointPerAmount * $pointsEarned * 1.0) . " poin\n";
                                $example .= "- Silver (1.2x): " . floor(100000 / $pointPerAmount * $pointsEarned * 1.2) . " poin\n";
                                $example .= "- Gold (1.5x): " . floor(100000 / $pointPerAmount * $pointsEarned * 1.5) . " poin\n";
                                $example .= "- Platinum (2x): " . floor(100000 / $pointPerAmount * $pointsEarned * 2.0) . " poin\n\n";
                                $example .= "Nilai tukar: 10 poin = Rp " . number_format(10 * $pointValue, 0, ',', '.');

                                return $example;
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('point_per_amount')
                    ->label('Per Nominal')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('points_earned')
                    ->label('Poin Didapat')
                    ->numeric(),

                Tables\Columns\TextColumn::make('point_value')
                    ->label('Nilai 1 Poin')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('min_points_redeem')
                    ->label('Min. Tukar')
                    ->numeric(),

                Tables\Columns\TextColumn::make('point_expiry_days')
                    ->label('Masa Berlaku')
                    ->suffix(' hari'),

                Tables\Columns\IconColumn::make('auto_tier_upgrade')
                    ->label('Auto Tier')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->paginated(false);
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
            'index' => Pages\ListPointSettings::route('/'),
            'edit' => Pages\EditPointSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return PointSetting::count() === 0;
    }
}
