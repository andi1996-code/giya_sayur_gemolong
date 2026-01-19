<?php

namespace App\Filament\Resources\ProductDiscountResource\Pages;

use App\Filament\Resources\ProductDiscountResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductDiscount extends CreateRecord
{
    protected static string $resource = ProductDiscountResource::class;

    protected function handleRecordCreation(array $data): Product
    {
        $productId = $data['id'];
        $product = Product::find($productId);

        if ($product) {
            $product->update([
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discount_active' => $data['discount_active'] ?? false,
                'discount_start_date' => $data['discount_start_date'] ?? null,
                'discount_end_date' => $data['discount_end_date'] ?? null,
            ]);
        }

        return $product;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
