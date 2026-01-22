<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SalesReportExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected $data;
    protected $simpleView;
    protected $fileName;
    protected $startDateTime;
    protected $endDateTime;

    public function __construct($data, $simpleView, $fileName, $startDateTime, $endDateTime)
    {
        $this->data = $data;
        $this->simpleView = $simpleView;
        $this->fileName = $fileName;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
    }

    public function array(): array
    {
        $rows = [];

        // Header Info
        $rows[] = ['Laporan Penjualan'];
        $rows[] = [$this->fileName];
        $rows[] = ['Periode: ' . $this->startDateTime->format('d/m/Y H:i') . ' - ' . $this->endDateTime->format('d/m/Y H:i')];
        $rows[] = []; // Empty row

        $total_Order_amount = 0;
        $total_Profit_amount = 0;

        // Group transactions by category
        $categoriesData = [];
        foreach($this->data as $order) {
            foreach($order->transactionItems as $item) {
                $categoryName = $item->product->category->name ?? 'Tanpa Kategori';

                if (!isset($categoriesData[$categoryName])) {
                    $categoriesData[$categoryName] = [
                        'transactions' => [],
                        'total_amount' => 0,
                        'total_profit' => 0
                    ];
                }

                $transactionKey = $order->id;
                if (!isset($categoriesData[$categoryName]['transactions'][$transactionKey])) {
                    $categoriesData[$categoryName]['transactions'][$transactionKey] = [
                        'order' => $order,
                        'items' => [],
                        'transaction_total' => 0,
                        'transaction_profit' => 0
                    ];
                }

                $categoriesData[$categoryName]['transactions'][$transactionKey]['items'][] = $item;
                $itemTotal = $item->price * $item->quantity;
                $categoriesData[$categoryName]['transactions'][$transactionKey]['transaction_total'] += $itemTotal;
                $categoriesData[$categoryName]['transactions'][$transactionKey]['transaction_profit'] += $item->total_profit;

                $categoriesData[$categoryName]['total_amount'] += $itemTotal;
                $categoriesData[$categoryName]['total_profit'] += $item->total_profit;

                $total_Order_amount += $itemTotal;
                $total_Profit_amount += $item->total_profit;
            }
        }

        if ($this->simpleView) {
            // VERSI SINGKAT
            foreach($categoriesData as $categoryName => $categoryData) {
                $rows[] = ['KATEGORI: ' . strtoupper($categoryName)];
                $rows[] = ['No. Transaksi', 'Jenis Pembayaran', 'Total Bayar', 'Total Profit'];

                foreach($categoryData['transactions'] as $transaction) {
                    $rows[] = [
                        $transaction['order']->transaction_number,
                        $transaction['order']->paymentMethod->name,
                        $transaction['transaction_total'],
                        $transaction['transaction_profit']
                    ];
                }

                $rows[] = ['SUBTOTAL KATEGORI', '', $categoryData['total_amount'], $categoryData['total_profit']];
                $rows[] = []; // Empty row
            }
        } else {
            // VERSI DETAIL
            foreach($categoriesData as $categoryName => $categoryData) {
                $rows[] = ['KATEGORI: ' . strtoupper($categoryName)];
                $rows[] = []; // Empty row

                foreach($categoryData['transactions'] as $transaction) {
                    $rows[] = [
                        'No. Transaksi: ' . $transaction['order']->transaction_number,
                        '',
                        'Pembayaran: ' . $transaction['order']->paymentMethod->name
                    ];
                    $rows[] = ['Produk', 'Harga Modal', 'Harga Jual', 'Qty', 'Total Bayar', 'Total Profit'];

                    foreach($transaction['items'] as $item) {
                        $qty = $item->weight ? number_format($item->weight, 3, ',', '.') . ' kg' : $item->quantity;
                        $rows[] = [
                            $item->product->name,
                            $item->cost_price,
                            $item->price,
                            $qty,
                            $item->price * $item->quantity,
                            $item->total_profit
                        ];
                    }

                    $rows[] = ['Total Transaksi', '', '', '', $transaction['transaction_total'], $transaction['transaction_profit']];
                    $rows[] = []; // Empty row
                }

                $rows[] = ['SUBTOTAL KATEGORI ' . strtoupper($categoryName)];
                $rows[] = ['Total Uang Masuk:', $categoryData['total_amount']];
                $rows[] = ['Total Keuntungan:', $categoryData['total_profit']];
                $rows[] = []; // Empty row
            }
        }

        // Total Keseluruhan
        $rows[] = ['TOTAL KESELURUHAN'];
        $rows[] = ['Total Uang Masuk:', $total_Order_amount];
        $rows[] = ['Total Keuntungan:', $total_Profit_amount];

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['italic' => true]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }
}
