<?php

namespace App\Filament\Resources\SupplierDebtResource\Pages;

use App\Filament\Resources\SupplierDebtResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplierDebt extends ViewRecord
{
    protected static string $resource = SupplierDebtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pay')
                ->label('Bayar')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'lunas')
                ->form([
                    \Filament\Forms\Components\TextInput::make('payment_amount')
                        ->label('Jumlah Pembayaran')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->maxValue(fn () => $this->record->remaining_amount),

                    \Filament\Forms\Components\Textarea::make('payment_notes')
                        ->label('Catatan Pembayaran')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
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

                    \Filament\Notifications\Notification::make()
                        ->title('Pembayaran Berhasil')
                        ->body("Pembayaran sebesar Rp " . number_format($data['payment_amount'], 0, ',', '.') . " telah dicatat.")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Catat Pembayaran')
                ->modalDescription('Masukkan jumlah pembayaran yang diterima.')
                ->modalSubmitActionLabel('Simpan Pembayaran'),

            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Supplier Debt')
                ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Stok produk akan dikembalikan sesuai dengan transaksi yang dihapus.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->visible(fn () => $this->record->status !== 'lunas'), // Hanya bisa hapus jika belum lunas
        ];
    }
}
