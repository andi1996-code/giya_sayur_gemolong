<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Barcode Produk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            background: #fff;
        }

        /* Body untuk format 33x15 */
        body.format-33x15 {
            width: 33mm;
            height: 15mm;
        }

        /* Body untuk format 60x30 */
        body.format-60x30 {
            width: 60mm;
            height: 30mm;
        }

        /* Label 33x15 - exact thermal size */
        .label-33x15-container {
            width: 33mm;
            height: 15mm;
            margin: 0;
            padding: 0;
        }

        .label-33x15 {
            width: 33mm;
            height: 15mm;
            border: none;
            margin: 0;
            padding: 0;
            text-align: center;
            vertical-align: middle;
            page-break-inside: avoid;
            background: #fff;
            overflow: hidden;
            display: block;
            position: relative;
        }

        /* === Elemen isi label thermal === */
        .label-33x15 .name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 0.3mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1;
        }

        .label-33x15 .price {
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin-bottom: 0.4mm;
            line-height: 1;
        }

        .label-33x15 img {
            width: 32mm;
            height: 6mm;
            display: block;
            margin: 0.2mm auto;
        }

        .label-33x15 .number {
            font-size: 22px;
            color: #000;
            line-height: 1;
            margin-top: 0.2mm;
        }

        /* Label 60x30 - untuk rak */
        .label-60x30 {
            width: 60mm;
            height: 30mm;
            border: none;
            margin: 0;
            padding: 0;
            text-align: center;
            vertical-align: middle;
            page-break-inside: avoid;
            background: #fff;
            display: block;
            overflow: hidden;
        }

        .label-60x30 .name {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 1mm;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label-60x30 .price {
            font-size: 32px;
            font-weight: bold;
            color: #000;
            margin-bottom: 2mm;
            line-height: 1;
        }

        .label-60x30 img {
            width: 56mm;
            height: 12mm;
            display: block;
            margin: 1mm auto;
        }

        .label-60x30 .number {
            font-size: 32px;
            color: #000;
            margin-top: 1mm;
            line-height: 1;
        }

        /* Page settings untuk label thermal */
        @page {
            margin: 0;
        }

        /* Label 33x15 specific */
        @page label-33x15 {
            size: 33mm 15mm;
        }

        /* Label 60x30 specific */
        @page label-60x30 {
            size: 60mm 30mm;
        }

        @media print {
            body {
                margin: 0;
            }

            .label-33x15 {
                page-break-after: always;
                margin: 0;
                width: 33mm;
                height: 15mm;
                page: label-33x15;
            }

            .label-60x30 {
                page-break-after: always;
                margin: 0;
                width: 60mm;
                height: 30mm;
                page: label-60x30;
            }
        }
    </style>

</head>

<body class="format-{{ $format }}">
    <div class="container">
        @php
            $format = isset($format) ? $format : 'a4';
            $barcodes = isset($barcodes) ? $barcodes : [];
        @endphp

        @if (empty($barcodes))
            <p style="text-align: center; padding: 50px; color: #999;">
                Tidak ada data barcode untuk dicetak
            </p>
        @elseif ($format === 'label_33x15')
            <!-- Format Label Thermal 33x15 - 1 baris -->
            <div class="label-33x15-container">
                @foreach ($barcodes as $barcode)
                    <div class="label-33x15">
                        <div class="name">{{ substr($barcode['name'], 0, 20) }}</div>
                        <div class="price">Rp {{ number_format($barcode['price'], 0, ',', '.') }}</div>
                        <img src="{{ $barcode['barcode'] }}" alt="barcode">
                        <div class="number">{{ $barcode['number'] }}</div>
                    </div>
                @endforeach
            </div>
        @elseif ($format === 'label_60x30')
            <!-- Format Label 60x30 - untuk rak -->
            @foreach ($barcodes as $barcode)
                <div class="label-60x30">
                    <div class="name">{{ $barcode['name'] }}</div>
                    <div class="price">Rp {{ number_format($barcode['price'], 0, ',', '.') }}</div>
                    <img src="{{ $barcode['barcode'] }}" alt="barcode">
                    <div class="number">{{ $barcode['number'] }}</div>
                </div>
            @endforeach
        @endif
    </div>
</body>

</html>
