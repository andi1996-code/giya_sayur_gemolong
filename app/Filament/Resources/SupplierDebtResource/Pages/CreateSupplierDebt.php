<?php

namespace App\Filament\Resources\SupplierDebtResource\Pages;

use App\Filament\Resources\SupplierDebtResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplierDebt extends CreateRecord
{
    protected static string $resource = SupplierDebtResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure no NULL values
        $data['paid_amount'] = $data['paid_amount'] ?? 0;
        $data['notes'] = $data['notes'] ?? '';

        // Calculate remaining amount
        $data['remaining_amount'] = ($data['amount'] ?? 0) - ($data['paid_amount'] ?? 0);

        // Auto-determine status
        if ($data['paid_amount'] >= $data['amount']) {
            $data['status'] = 'lunas';
            $data['remaining_amount'] = 0;
        } elseif ($data['paid_amount'] > 0) {
            $data['status'] = 'sebagian_lunas';
        } else {
            $data['status'] = 'belum_lunas';
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
