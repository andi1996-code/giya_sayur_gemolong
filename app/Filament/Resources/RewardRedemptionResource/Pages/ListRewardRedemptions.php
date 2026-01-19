<?php

namespace App\Filament\Resources\RewardRedemptionResource\Pages;

use App\Filament\Resources\RewardRedemptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRewardRedemptions extends ListRecords
{
    protected static string $resource = RewardRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['processed_by'] = Auth::id();

                    // Validasi poin member mencukupi
                    $member = \App\Models\Member::find($data['member_id']);
                    $pointsRequired = $data['points_used'];

                    if ($member && $member->total_points < $pointsRequired) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Poin Tidak Cukup')
                            ->body("Member {$member->name} hanya memiliki {$member->total_points} poin. Dibutuhkan {$pointsRequired} poin.")
                            ->persistent()
                            ->send();

                        $this->halt();
                    }

                    // Validasi stok produk
                    $product = \App\Models\Product::find($data['product_id']);
                    $quantity = $data['quantity'];

                    if ($product && $product->stock < $quantity) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Stok Tidak Cukup')
                            ->body("Produk {$product->name} hanya memiliki stok {$product->stock}. Dibutuhkan {$quantity} item.")
                            ->persistent()
                            ->send();

                        $this->halt();
                    }

                    // Validasi limit per member
                    if ($product && $product->max_redeem_per_member !== null) {
                        $redeemedCount = \App\Models\RewardRedemption::where('member_id', $member->id)
                            ->where('product_id', $product->id)
                            ->where('status', 'completed')
                            ->sum('quantity');

                        if (($redeemedCount + $quantity) > $product->max_redeem_per_member) {
                            $remaining = $product->max_redeem_per_member - $redeemedCount;
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Melebihi Limit Penukaran')
                                ->body("Member sudah menukar {$redeemedCount}x. Sisa kuota: {$remaining}x. Maksimal per member: {$product->max_redeem_per_member}x.")
                                ->persistent()
                                ->send();

                            $this->halt();
                        }
                    }

                    return $data;
                })
                ->after(function ($record) {
                    // Setelah berhasil save, kurangi poin member dan stok produk
                    $member = $record->member;
                    $product = $record->product;

                    // Kurangi poin member (transactionId=null, description=string)
                    $member->deductPoints($record->points_used, null, "Tukar reward: {$product->name}");

                    // Kurangi stok produk
                    $product->decrement('stock', $record->quantity);

                    // Hitung sisa poin setelah redeem
                    $member->refresh();
                    $remainingPoints = (int) ($member->total_points ?? 0);

                    // Simpan data ke session untuk fallback (jika full page reload)
                    session()->flash('reward_success', [
                        'member_name' => $member->name,
                        'product_name' => $product->name,
                        'remaining_points' => $remainingPoints,
                    ]);

                    // Dispatch Livewire event untuk trigger popup langsung (tanpa reload)
                    $this->dispatch('reward-redemption-success', [
                        'member_name' => $member->name,
                        'product_name' => $product->name,
                        'remaining_points' => $remainingPoints,
                    ]);
                }),
        ];
    }
}
