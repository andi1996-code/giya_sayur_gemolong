<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Piutang Supplier</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .container {
            max-width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .header .document-title {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10px;
            color: #000;
        }

        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-box {
            display: flex;
            flex-direction: column;
        }

        .info-box h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 100px 1fr;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .info-row .label {
            font-weight: bold;
            width: 100px;
        }

        .info-row .value {
            word-break: break-word;
        }

        .transaction-details {
            margin-bottom: 30px;
        }

        .transaction-details h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background-color: #f0f0f0;
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
        }

        .summary-section {
            margin-bottom: 30px;
            border: 1px solid #999;
            padding: 15px;
        }

        .summary-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .summary-row .label {
            font-weight: bold;
        }

        .summary-row .value {
            text-align: right;
            font-weight: bold;
        }

        .summary-row.total {
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 14px;
        }

        .notes-section {
            margin-bottom: 30px;
        }

        .notes-section h4 {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .notes-content {
            font-size: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            min-height: 40px;
        }

        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            text-align: center;
            font-size: 10px;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
        }

        .signature-space {
            height: 50px;
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $store->name ?? 'TOKO' }}</h1>
            <div class="subtitle">{{ $store->address ?? '' }} | Telp: {{ $store->phone ?? '' }}</div>
            <div class="document-title">NOTA TRANSAKSI PIUTANG SUPPLIER</div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <h3>Informasi Supplier</h3>
                <div class="info-row">
                    <span class="label">Nama:</span>
                    <span class="value">{{ $supplierDebt->supplier_name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Telepon:</span>
                    <span class="value">{{ $supplierDebt->supplier_phone ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Alamat:</span>
                    <span class="value">{{ $supplierDebt->supplier_address ?? '-' }}</span>
                </div>
            </div>

            <div class="info-box">
                <h3>Informasi Transaksi</h3>
                <div class="info-row">
                    <span class="label">No. Piutang:</span>
                    <span class="value">#{{ $supplierDebt->id }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Tanggal:</span>
                    <span class="value">{{ $supplierDebt->transaction_date->format('d-m-Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Jatuh Tempo:</span>
                    <span class="value">{{ $supplierDebt->due_date?->format('d-m-Y') ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value">
                        @if($supplierDebt->status === 'lunas')
                            <span class="badge badge-success">LUNAS</span>
                        @elseif($supplierDebt->status === 'sebagian_lunas')
                            <span class="badge badge-warning">SEBAGIAN LUNAS</span>
                        @else
                            <span class="badge badge-danger">BELUM LUNAS</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="transaction-details">
            <h3>Detail Produk</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%">Produk</th>
                        <th style="width: 20%">Kuantitas</th>
                        <th style="width: 20%">Jenis Stok</th>
                        <th style="width: 20%">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $supplierDebt->product->name ?? '-' }}</td>
                        <td>{{ number_format($supplierDebt->quantity, 2) }} {{ $supplierDebt->unit }}</td>
                        <td>{{ $supplierDebt->stock_type === 'kongsi' ? 'Stok Kongsi (Titipan)' : 'Stok Reguler' }}</td>
                        <td>{{ $supplierDebt->description }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-row">
                <span class="label">Jumlah Total:</span>
                <span class="value">Rp {{ number_format($supplierDebt->amount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span class="label">Jumlah Dibayar:</span>
                <span class="value">Rp {{ number_format($supplierDebt->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span class="label">Sisa Piutang:</span>
                <span class="value">Rp {{ number_format($supplierDebt->remaining_amount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row total">
                <span class="label">STATUS PEMBAYARAN:</span>
                <span class="value">
                    @if($supplierDebt->status === 'lunas')
                        <span class="badge badge-success">LUNAS</span>
                    @elseif($supplierDebt->status === 'sebagian_lunas')
                        <span class="badge badge-warning">SEBAGIAN LUNAS</span>
                    @else
                        <span class="badge badge-danger">BELUM LUNAS</span>
                    @endif
                </span>
            </div>
        </div>

        <!-- Notes Section -->
        @if($supplierDebt->notes)
            <div class="notes-section">
                <h4>Catatan & Riwayat Pembayaran:</h4>
                <div class="notes-content">{{ $supplierDebt->notes }}</div>
            </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div>Supplier</div>
                <div class="signature-space"></div>
                <div>{{ $supplierDebt->supplier_name }}</div>
            </div>
            <div class="signature-box">
                <div>Pembuat Nota</div>
                <div class="signature-space"></div>
                <div>{{ $supplierDebt->user->name ?? '-' }}</div>
            </div>
            <div class="signature-box">
                <div>Penerima</div>
                <div class="signature-space"></div>
                <div>(_________________)</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dicetak pada: {{ $generatedAt }}</p>
            <p>Dokumen ini merupakan bukti transaksi piutang supplier. Harap disimpan dengan baik.</p>
        </div>
    </div>
</body>
</html>
