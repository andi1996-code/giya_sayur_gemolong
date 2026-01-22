<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Laporan Uang Keluar</title>
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
        <h1>Laporan Uang Keluar<br><span>{{ '(' . $fileName . ')' }}</span></h1>
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

        {{-- Summary per Sumber --}}
        <table>
            <thead>
                <tr>
                    <th colspan="2" style="background-color:#FF9800; color:white; font-size:14px">RINGKASAN PER KATEGORI PENGELUARAN</th>
                </tr>
                <tr>
                    <th>Kategori</th>
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
                    <th colspan="5" style="background-color:#F44336; color:white; font-size:16px">Total Keseluruhan: Rp {{ number_format( $total_Order_amount, 0, ',', '.') }}</th>
                </tr>
            </thead>
        </table>
    </main>

    <footer>
        Laporan ini dihasilkan secara otomatis tanpa tanda tangan.
    </footer>

</body>

</html>
