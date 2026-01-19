<?php

namespace App\Filament\Resources\SupplierDebtResource\Pages;

use App\Filament\Resources\SupplierDebtResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSupplierDebt extends EditRecord
{
    protected static string $resource = SupplierDebtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Supplier Debt')
                ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Stok produk akan dikembalikan sesuai dengan transaksi yang dihapus.')
                ->modalSubmitActionLabel('Ya, Hapus'),
        ];
    }

    protected function getFormActions(): array
    {
        // Mengembalikan array kosong untuk menyembunyikan tombol Save
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Tampilkan notifikasi informasi saat halaman edit dimuat
        Notification::make()
            ->title('Mode View Only')
            ->body('Data supplier debt tidak dapat diubah untuk menjaga integritas data. Gunakan tombol "Bayar" untuk update pembayaran.')
            ->info()
            ->persistent()
            ->send();

        return $data;
    }

    protected function beforeSave(): void
    {
        // Mencegah penyimpanan dengan menghentikan proses
        Notification::make()
            ->title('Tidak Dapat Menyimpan')
            ->body('Data supplier debt tidak dapat diubah setelah dibuat.')
            ->warning()
            ->send();

        $this->halt();
    }
}
