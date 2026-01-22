<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Laporan Order</title>
    <style>
        body {
            margin: 0 auto;
            font-family: Arial, sans-serif;
            background: #FFFFFF;
            font-size: 12px;
            color: #001028;
        }

        header {
            padding: 10px 0;
            text-align: center;
            border-bottom: 1px solid #5D6975;
            margin-bottom: 20px;
        }

        #logo img {
            width: 120px;
        }

         h1 {
            font-size: 2em;
            margin: 14px 0;
        }

        span {
            font-size: 14px;
            color: #5D6975;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            border: 1px solid #C1CED9;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #F5F5F5;
            color: #5D6975;
        }

        .desc {
            text-align: left;
        }

        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 30px;
            border-top: 1px solid #C1CED9;
            text-align: center;
            padding: 8px 0;
            font-size: 0.8em;
            color: #5D6975;
        }
    </style>
</head>

<body>

    <header>
        <div id="logo">
            <img src="{{ storage_path('app/public/' . $logo) }}" alt="{{ asset('storage/' . $logo) }}">
        </div>
        <h1>Laporan Penjualan<br><span>{{ '(' . $fileName . ')' }}</span></h1>
        <span>Periode: {{ $startDateTime->format('d/m/Y H:i') }} - {{ $endDateTime->format('d/m/Y H:i') }}</span>
    </header>

    <main>
    <?php
    $total_Order_amount = 0;
    $total_Profit_amount = 0;

    // Group transactions by category
    $categoriesData = [];
    foreach($data as $order) {
        foreach($order->transactionItems as $item) {
            $categoryName = $item->product->category->name ?? 'Tanpa Kategori';

            if (!isset($categoriesData[$categoryName])) {
                $categoriesData[$categoryName] = [
                    'transactions' => [],
                    'total_amount' => 0,
                    'total_profit' => 0
                ];
            }

            // Cek apakah transaksi sudah ada di kategori ini
            $transactionKey = $order->id;
            if (!isset($categoriesData[$categoryName]['transactions'][$transactionKey])) {
                $categoriesData[$categoryName]['transactions'][$transactionKey] = [
                    'order' => $order,
                    'items' => [],
                    'transaction_total' => 0,
                    'transaction_profit' => 0
                ];
            }

            // Tambahkan item ke transaksi
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
    ?>

    @if($simpleView ?? false)
        {{-- VERSI SINGKAT: Hanya tampil No Transaksi, Jenis Pembayaran, Total per Kategori --}}
        @foreach($categoriesData as $categoryName => $categoryData)
        <table>
            <thead>
                <tr>
                    <th colspan="4" style="background-color:#4CAF50; color:white; font-size:14px">
                        KATEGORI: {{ strtoupper($categoryName) }}
                    </th>
                </tr>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Jenis Pembayaran</th>
                    <th>Total Bayar</th>
                    <th>Total Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryData['transactions'] as $transaction)
                <tr>
                    <td>{{ $transaction['order']->transaction_number }}</td>
                    <td>{{ $transaction['order']->paymentMethod->name }}</td>
                    <td>Rp {{ number_format($transaction['transaction_total'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($transaction['transaction_profit'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr style="background-color:#E8F5E9;">
                    <td colspan="2"><strong>SUBTOTAL KATEGORI</strong></td>
                    <td><strong>Rp {{ number_format($categoryData['total_amount'], 0, ',', '.') }}</strong></td>
                    <td><strong>Rp {{ number_format($categoryData['total_profit'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        @endforeach
    @else
        {{-- VERSI DETAIL: Tampil dengan detail produk per kategori --}}
        @foreach($categoriesData as $categoryName => $categoryData)
        <div style="margin-bottom: 30px;">
            <h2 style="background-color:#4CAF50; color:white; padding:10px; margin-bottom:0;">
                KATEGORI: {{ strtoupper($categoryName) }}
            </h2>

            @foreach($categoryData['transactions'] as $transaction)
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color:yellow; color:black;">No.Transaksi: {{ $transaction['order']->transaction_number }}</th>
                        <th colspan="2" style="background-color:yellow; color:black;">Pembayaran: {{ $transaction['order']->paymentMethod->name }}</th>
                    </tr>
                    <tr>
                        <th>Produk</th>
                        <th>Harga Modal</th>
                        <th>Harga Jual</th>
                        <th>Qty</th>
                        <th>Total Bayar</th>
                        <th>Total Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction['items'] as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>
                            @if($item->weight)
                                {{ number_format($item->weight, 3, ',', '.') }} kg
                            @else
                                {{ $item->quantity }}
                            @endif
                        </td>
                        <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->total_profit, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="4">Total Transaksi</td>
                        <td>Rp {{ number_format($transaction['transaction_total'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($transaction['transaction_profit'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
            @endforeach

            {{-- Subtotal per kategori --}}
            <table style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th colspan="2" style="background-color:#E8F5E9; color:black; font-size:14px">
                            SUBTOTAL KATEGORI {{ strtoupper($categoryName) }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background-color:#E8F5E9;">
                        <td style="text-align:left; padding-left:20px;"><strong>Total Uang Masuk:</strong></td>
                        <td><strong>Rp {{ number_format($categoryData['total_amount'], 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr style="background-color:#E8F5E9;">
                        <td style="text-align:left; padding-left:20px;"><strong>Total Keuntungan:</strong></td>
                        <td><strong>Rp {{ number_format($categoryData['total_profit'], 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach
    @endif

        {{-- Total Keseluruhan --}}
        <table style="margin-top: 20px;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color:#2196F3; color:white; font-size:16px">TOTAL KESELURUHAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color:white; color:black; font-size:16px"><strong>Total Uang Masuk:</strong></td>
                    <td style="background-color:white; color:black; font-size:16px"><strong>Rp {{ number_format($total_Order_amount, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td style="background-color:white; color:black; font-size:16px"><strong>Total Keuntungan:</strong></td>
                    <td style="background-color:white; color:black; font-size:16px"><strong>Rp {{ number_format($total_Profit_amount, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </main>

    <footer>
        Laporan ini dihasilkan secara otomatis tanpa tanda tangan.
    </footer>

</body>

</html>
