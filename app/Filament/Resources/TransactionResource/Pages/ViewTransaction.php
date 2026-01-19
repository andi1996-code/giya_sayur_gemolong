<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Models\Setting;
use App\Models\Transaction;
use App\Services\DirectPrintService;
use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected static ?string $title = 'Detail Pesanan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('PrintBluetooth')
                ->label('Cetak (Bluetooth)')
                ->visible(fn() => Setting::first()->value('print_via_bluetooth') == true)
                ->action(function () {
                    $order = Transaction::with(['paymentMethod', 'transactionItems.product', 'member'])->findOrFail($this->record->id);
                    $items = $order->transactionItems;

                    // Refresh member data to get updated points
                    if ($order->member) {
                        $order->member->refresh();
                    }

                    $this->printStruk($order, $items);
                })
                ->icon('heroicon-o-printer')
                ->color('info'),
            Actions\Action::make('PrintUSB')
                ->label('Cetak (Cable)')
                ->visible(fn() => Setting::first()->value('print_via_bluetooth') == false)
                ->action(function () {
                    $order = Transaction::with(['paymentMethod', 'transactionItems.product', 'member'])->findOrFail($this->record->id);
                    $items = $order->transactionItems;
                    $settings = Setting::first();

                    // Refresh member data if exists
                    if ($order->member) {
                        $order->member->refresh();
                    }

                    // Prepare receipt data for USB Print
                    $receiptData = [
                        'store' => [
                            'name' => $settings->name,
                            'address' => $settings->address,
                            'phone' => $settings->phone,
                            'website' => $settings->website ?? '',
                            'receipt_footer_line1' => $settings->receipt_footer_line1 ?? '',
                            'receipt_footer_line2' => $settings->receipt_footer_line2 ?? '',
                            'receipt_footer_line3' => $settings->receipt_footer_line3 ?? '',
                            'receipt_footer_note' => $settings->receipt_footer_note ?? '',
                            'show_footer_thank_you' => $settings->show_footer_thank_you ?? true,
                        ],
                        'order' => [
                            'transaction_number' => $order->transaction_number,
                            'payment_method' => [
                                'name' => $order->paymentMethod->name,
                            ],
                            'subtotal' => $order->subtotal,
                            'promo_discount' => $order->promo_discount ?? 0,
                            'points_discount' => $order->points_discount ?? 0,
                            'total' => $order->total,
                            'cash_received' => $order->cash_received ?? $order->total,
                            'change' => $order->change ?? 0,
                            'points_earned' => $order->points_earned ?? 0,
                            'points_redeemed' => $order->points_redeemed ?? 0,
                        ],
                        'items' => $items->map(function ($item) {
                            return [
                                'product' => [
                                    'name' => $item->product ? $item->product->name : $item->product_name,
                                ],
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'weight' => $item->weight ?? 0,
                                'price' => $item->price,
                                'subtotal' => $item->subtotal,
                            ];
                        })->toArray(),
                        'date' => $order->created_at->format('d-m-Y H:i:s'),
                        'cashier' => [
                            'name' => $order->user->name ?? 'Kasir',
                        ],
                        'member' => $order->member ? [
                            'name' => $order->member->name,
                            'member_code' => $order->member->member_code,
                            'tier' => $order->member->tier,
                            'points' => $order->member->points ?? 0,
                            'total_points' => $order->member->total_points ?? 0,
                        ] : null,
                        'printerName' => $settings->name_printer_local,
                    ];

                    // Dispatch to USB Print Service
                    $this->dispatch('doPrintReceiptUSB', $receiptData);
                })
                ->icon('heroicon-o-printer')
                ->color('amber'),
            Actions\EditAction::make(),
        ];
    }

    public function printStruk($order, $items)
    {
        $this->dispatch('doPrintReceipt',
            store: Setting::first(),
            order: $order,
            items: $items,
            date: $order->created_at->format('d-m-Y H:i:s'),
            cashier: Auth::user(), // Use current logged in user
            member: $order->member // Include member data if exists
        );
    }
}
