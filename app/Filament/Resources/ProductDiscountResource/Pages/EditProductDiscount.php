<?php

namespace App\Filament\Resources\ProductDiscountResource\Pages;

use App\Filament\Resources\ProductDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductDiscount extends EditRecord
{
    protected static string $resource = ProductDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reset_discount')
                ->label('Reset Diskon')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $this->record->update([
                        'discount_percentage' => 0,
                        'discount_active' => false,
                        'discount_start_date' => null,
                        'discount_end_date' => null,
                    ]);
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->requiresConfirmation()
                ->modalHeading('Reset Diskon Produk')
                ->modalDescription('Apakah Anda yakin ingin mereset semua pengaturan diskon untuk produk ini?'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Set the product id for the form
        $data['id'] = $this->record->id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
