<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Laporan Uang Masuk</title>
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
            width: 80px;
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
        <h1>Laporan Uang Masuk<br><span>{{ '(' . $fileName . ')' }}</span></h1>
        <span>Periode: {{ $startDateTime->format('d/m/Y H:i') }} - {{ $endDateTime->format('d/m/Y H:i') }}</span>
    </header>

    <main>
    <?php
    $total_Order_amount = 0;

    // Group by source untuk summary
    $summaryBySource = [];
    foreach($data as $d) {
        $sourceLabel = app(App\Services\CashFlowLabelService::class)->getSourceLabel($d->type, $d->source);
        if (!isset($summaryBySource[$sourceLabel])) {
            $summaryBySource[$sourceLabel] = 0;
        }
        $summaryBySource[$sourceLabel] += $d->amount;
    }

    // Group by payment method untuk summary transaksi penjualan
    $summaryByPaymentMethod = [];
    $totalTransactions = 0;
    $transactionCount = 0;

    if(isset($transactions) && $transactions->count() > 0) {
        foreach($transactions as $transaction) {
            $transactionCount++;

            // Debug: Cek apakah ada payment_method_id
            if ($transaction->payment_method_id) {
                // Load paymentMethod jika belum ter-load
                if (!$transaction->relationLoaded('paymentMethod')) {
                    $transaction->load('paymentMethod');
                }

                if ($transaction->paymentMethod) {
                    $paymentMethodName = $transaction->paymentMethod->name;
                } else {
                    $paymentMethodName = 'Metode ID: ' . $transaction->payment_method_id . ' (Tidak Ditemukan)';
                }
            } else {
                $paymentMethodName = 'Tidak Ada Metode';
            }

            if (!isset($summaryByPaymentMethod[$paymentMethodName])) {
                $summaryByPaymentMethod[$paymentMethodName] = 0;
            }
            $summaryByPaymentMethod[$paymentMethodName] += $transaction->total;
            $totalTransactions += $transaction->total;
        }
    }
    ?>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Sumber</th>
                    <th>Total</th>
                    <th>notes</th>
                </tr>
            </thead>
            <tbody>
        @foreach($data as $d)
                    <tr>
                        <td>{{ $d->updated_at->format('d-m-Y H:i') }}</td>
                        <td>{{ app(App\Services\CashFlowLabelService::class)->getTypeLabel($d->type) }}</td>
                        <td>{{ app(App\Services\CashFlowLabelService::class)->getSourceLabel($d->type, $d->source) }}</td>
                        <td>Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
                        <td style="width: 200px;">{{ $d->notes }}</td>
                    </tr>
        <?php $total_Order_amount += $d->amount ?>
        @endforeach
            </tbody>
        </table>

        {{-- Summary per Metode Pembayaran (Transaksi Penjualan) --}}
        @if(!empty($summaryByPaymentMethod))
        <table>
            <thead>
                <tr>
                    <th colspan="2" style="background-color:#9C27B0; color:white; font-size:14px">
                        RINGKASAN PER METODE PEMBAYARAN ({{ $transactionCount }} Transaksi Penjualan)
                    </th>
                </tr>
                <tr>
                    <th>Metode Pembayaran</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summaryByPaymentMethod as $method => $amount)
                <tr>
                    <td style="text-align:left; padding-left:10px;">{{ $method }}</td>
                    <td>Rp {{ number_format($amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr style="background-color:#E1BEE7; font-weight:bold;">
                    <td style="text-align:left; padding-left:10px;">TOTAL PENJUALAN</td>
                    <td>Rp {{ number_format($totalTransactions, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        {{-- Summary per Sumber/Metode --}}
        <table>
            <thead>
                <tr>
                    <th colspan="2" style="background-color:#4CAF50; color:white; font-size:14px">RINGKASAN PER SUMBER</th>
                </tr>
                <tr>
                    <th>Sumber</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summaryBySource as $source => $amount)
                <tr>
                    <td style="text-align:left; padding-left:10px;">{{ $source }}</td>
                    <td>Rp {{ number_format($amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th colspan="5" style="background-color:#2196F3; color:white; font-size:16px">Total Keseluruhan: Rp {{ number_format( $total_Order_amount, 0, ',', '.') }}</th>
                </tr>
            </thead>
        </table>
    </main>

    <footer>
        Laporan ini dihasilkan secara otomatis tanpa tanda tangan.
    </footer>

</body>

</html>
