<div class="display-wrapper">
    <!-- Receipt Modal Overlay -->
    @if($showReceipt && $receiptData)
        <div class="receipt-overlay" wire:click="hideReceipt">
            <div class="receipt-modal" @click.stop>
                <div class="receipt-content">
                    <!-- Receipt Header -->
                    <div class="receipt-header">
                        <h1>✅ TRANSAKSI SELESAI</h1>
                        <p class="receipt-number">Nomor: {{ $receiptData['transaction_number'] ?? 'N/A' }}</p>
                    </div>

                    <!-- Receipt Body -->
                    <div class="receipt-body">
                        <div class="receipt-time">
                            {{ $receiptData['date'] ?? now()->format('d-m-Y H:i:s') }}
                        </div>

                        <!-- Items -->
                        <div class="receipt-items">
                            @foreach($receiptData['items'] ?? [] as $item)
                                <div class="receipt-item">
                                    <span class="receipt-item-name">{{ $item['name'] ?? 'Item' }}</span>
                                    <span class="receipt-item-qty">{{ $item['quantity'] ?? 1 }} x</span>
                                    <span class="receipt-item-price">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</span>
                                    <span class="receipt-item-subtotal">= Rp {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Divider -->
                        <div class="receipt-divider"></div>

                        <!-- Summary -->
                        <div class="receipt-summary">
                            <div class="receipt-summary-row">
                                <span class="summary-label">Subtotal</span>
                                <span class="summary-value">Rp {{ number_format($receiptData['subtotal'] ?? 0, 0, ',', '.') }}</span>
                            </div>

                            @if(($receiptData['promo_discount'] ?? 0) > 0)
                                <div class="receipt-summary-row discount">
                                    <span class="summary-label">Diskon</span>
                                    <span class="summary-value">- Rp {{ number_format($receiptData['promo_discount'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="receipt-summary-row total">
                                <span class="summary-label">TOTAL BAYAR</span>
                                <span class="summary-value">Rp {{ number_format($receiptData['total'] ?? 0, 0, ',', '.') }}</span>
                            </div>

                            <div class="receipt-summary-row">
                                <span class="summary-label">Nominal Terima</span>
                                <span class="summary-value">Rp {{ number_format($receiptData['cash_received'] ?? 0, 0, ',', '.') }}</span>
                            </div>

                            @if(($receiptData['change'] ?? 0) > 0)
                                <div class="receipt-summary-row change">
                                    <span class="summary-label">KEMBALIAN</span>
                                    <span class="summary-value">Rp {{ number_format($receiptData['change'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Thank You -->
                        <div class="receipt-footer">
                            <h2>Terima Kasih! 🙏</h2>
                            <p>Silakan ambil barang Anda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Promo Section (6 Columns) -->
    <div class="promo-section">
        <!-- Store Info Header -->
        <div class="promo-store-header">
            <div class="store-info-left">
                @if($storeLogo)
                    <div class="store-logo-left">
                        <img src="{{ $storeLogo }}" alt="Logo Toko">
                    </div>
                @endif
                <div class="store-details-left">
                    <h1 class="store-name-left">{{ $storeName }}</h1>
                    @if($storeAddress)
                        <p class="store-address-left">{{ $storeAddress }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Logo Display Area -->
        <div class="logo-display-area">
            @if($customerDisplayImage)
                <div class="logo-center">
                    <img src="{{ $customerDisplayImage }}" alt="{{ $storeName }}">
                </div>
            @elseif($storeLogo)
                <div class="logo-center">
                    <img src="{{ $storeLogo }}" alt="{{ $storeName }}">
                </div>
            @else
                <div class="logo-placeholder">
                    <div class="placeholder-icon">🏪</div>
                    <p>{{ $storeName }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Customer Display Section (4 Columns) -->
    <div class="customer-display-container" wire:poll.1s="loadCartData">
        <!-- Header -->
        <div class="display-header">
            <h1>🛒 Pesanan Anda</h1>
            <p>{{ $transaction_name }}</p>
        </div>

        <!-- Content -->
        <div class="display-content">
            @if(count($order_items) > 0)
                <!-- Items List -->
                <div class="items-list">
                    @foreach($order_items as $index => $item)
                        <div class="item-card" wire:key="item-{{ $index }}">
                            <div class="item-info">
                                <div class="item-name">{{ $item['name'] }}</div>
                                <div class="item-details">
                                    <div class="item-detail-group">
                                        <div class="item-detail-label">Harga Satuan</div>
                                        <div class="item-detail-value">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="item-detail-group">
                                        <div class="item-detail-label">Jumlah</div>
                                        <div class="item-detail-value">
                                            {{ $item['quantity'] }} {{ $item['unit'] ?? 'pcs' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="item-subtotal">
                                <div class="subtotal-label">Subtotal</div>
                                <div class="subtotal-value">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Summary -->
                <div class="display-summary">
                    <div class="summary-row">
                        <span class="summary-label">📊 Subtotal</span>
                        <span class="summary-value">
                            Rp {{ number_format($total_price, 0, ',', '.') }}
                        </span>
                    </div>

                    @if($promo_discount > 0)
                        <div class="summary-row discount-row">
                            <span class="summary-label">🎉 Diskon Promo</span>
                            <span class="summary-value">
                                - Rp {{ number_format($promo_discount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <div class="summary-row total-row">
                        <span class="summary-label">💰 Total Bayar</span>
                        <span class="summary-value">
                            Rp {{ number_format($final_total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">🛒</div>
                    <h2 class="empty-title">Belum Ada Barang</h2>
                    <p class="empty-text">Silakan scan barcode atau pilih produk di kasir</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer-info">
            <span>Data diperbarui secara real-time</span>
            <span class="pulse">⟳</span>
        </div>
    </div>
</div>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
        background: #f3f4f6;
    }

    body {
        display: grid;
        grid-template-columns: 1fr;
        place-items: stretch;
        padding: 20px;
    }

    /* Receipt Modal Styles */
    .receipt-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .receipt-modal {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        width: 90%;
        animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .receipt-content {
        padding: 0;
        max-height: 90vh;
        overflow-y: auto;
    }

    .receipt-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 24px;
        text-align: center;
        border-radius: 12px 12px 0 0;
    }

    .receipt-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .receipt-number {
        font-size: 14px;
        opacity: 0.9;
        font-weight: 500;
    }

    .receipt-body {
        padding: 24px;
    }

    .receipt-time {
        text-align: center;
        color: #6b7280;
        font-size: 12px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .receipt-items {
        margin-bottom: 20px;
    }

    .receipt-item {
        display: grid;
        grid-template-columns: 2fr 0.5fr 1fr 1fr;
        gap: 8px;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
    }

    .receipt-item-name {
        font-weight: 600;
        color: #1f2937;
        word-break: break-word;
    }

    .receipt-item-qty {
        text-align: center;
        color: #6b7280;
    }

    .receipt-item-price {
        text-align: right;
        color: #6b7280;
    }

    .receipt-item-subtotal {
        text-align: right;
        font-weight: 600;
        color: #1f2937;
    }

    .receipt-divider {
        height: 2px;
        background: #e5e7eb;
        margin: 20px 0;
    }

    .receipt-summary {
        margin-bottom: 24px;
    }

    .receipt-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .receipt-summary-row.discount {
        background: #fef2f2;
        padding: 10px;
        border-radius: 6px;
        border-left: 3px solid #dc2626;
    }

    .receipt-summary-row.discount .summary-label,
    .receipt-summary-row.discount .summary-value {
        color: #991b1b;
        font-weight: 600;
    }

    .receipt-summary-row.total {
        background: #f0fdf4;
        padding: 12px;
        border-radius: 6px;
        border-left: 3px solid #10b981;
    }

    .receipt-summary-row.total .summary-label,
    .receipt-summary-row.total .summary-value {
        color: #065f46;
        font-weight: 700;
        font-size: 15px;
    }

    .receipt-summary-row.change {
        background: #f0f9ff;
        padding: 12px;
        border-radius: 6px;
        border-left: 3px solid #0284c7;
    }

    .receipt-summary-row.change .summary-label,
    .receipt-summary-row.change .summary-value {
        color: #075985;
        font-weight: 700;
    }

    .summary-label {
        color: #6b7280;
        font-weight: 500;
    }

    .summary-value {
        color: #1f2937;
        font-weight: 600;
        text-align: right;
    }

    .receipt-footer {
        text-align: center;
        padding: 20px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        border-radius: 0 0 12px 12px;
    }

    .receipt-footer h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .receipt-footer p {
        font-size: 13px;
        color: #6b7280;
    }

    .display-wrapper {
        display: grid;
        grid-template-columns: 4fr 6fr;
        gap: 20px;
        height: calc(100vh - 40px);
    }

    /* Promo Carousel Section */
    .promo-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Store Info in Left Column */
    .promo-store-header {
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        color: white;
        padding: 15px 15px;
        border-bottom: 3px solid #10b981;
    }

    .store-info-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .store-logo-left {
        flex-shrink: 0;
    }

    .store-logo-left img {
        width: 50px;
        height: 50px;
        object-fit: contain;
        border-radius: 8px;
        background: white;
        padding: 4px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .store-details-left {
        flex: 1;
    }

    .store-name-left {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 4px 0;
        color: white;
        line-height: 1.2;
    }

    .store-address-left {
        font-size: 11px;
        opacity: 0.9;
        margin: 0;
        color: #d1d5db;
        line-height: 1.3;
    }

    .promo-header {
        background: #1f2937;
        color: white;
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid #374151;
    }

    .promo-header h2 {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
    }

    /* Logo Display Area */
    .logo-display-area {
        flex: 1;
        display: flex;
        align-items: stretch;
        justify-content: center;
        padding: 0;
        background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
        overflow: hidden;
    }

    .logo-center {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 0;
    }

    .logo-center img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .logo-center img:hover {
        transform: scale(1.02);
    }

    .logo-placeholder {
        text-align: center;
        color: #9ca3af;
    }

    .placeholder-icon {
        font-size: 120px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .logo-placeholder p {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        color: #6b7280;
    }

    .promo-content {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        padding: 40px;
    }

    .promo-slides {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
        border-radius: 8px;
    }

    .promo-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        display: flex;
        flex-direction: column;
    }

    .promo-slide.active {
        opacity: 1;
        z-index: 1;
    }

    .promo-slide img {
        width: 100%;
        height: 70%;
        object-fit: cover;
        border-radius: 8px 8px 0 0;
    }

    .promo-caption {
        background: white;
        padding: 20px;
        text-align: center;
        height: 30%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border-radius: 0 0 8px 8px;
    }

    .promo-caption h3 {
        font-size: 22px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .promo-caption p {
        font-size: 15px;
        color: #6b7280;
    }

    .nav-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        font-size: 24px;
        padding: 12px 18px;
        cursor: pointer;
        border-radius: 4px;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .nav-button:hover {
        background: rgba(0, 0, 0, 0.8);
    }

    .nav-button.prev {
        left: 50px;
    }

    .nav-button.next {
        right: 50px;
    }

    .slide-indicators {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }

    .indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid rgba(0, 0, 0, 0.3);
    }

    .indicator.active {
        background: white;
        width: 30px;
        border-radius: 6px;
    }

    .indicator:hover {
        background: rgba(255, 255, 255, 0.8);
    }

    /* Customer Display Section */
    .customer-display-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .display-header {
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        color: white;
        padding: 20px;
        text-align: center;
        border-bottom: 3px solid #10b981;
    }

    .display-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .display-header p {
        font-size: 14px;
        opacity: 0.8;
        font-weight: 400;
    }

    .display-content {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    .items-list {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .items-list::-webkit-scrollbar {
        width: 6px;
    }

    .items-list::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 3px;
    }

    .items-list::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 3px;
    }

    .items-list::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .item-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s ease;
    }

    .item-card:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 6px;
        line-height: 1.3;
    }

    .item-details {
        display: flex;
        gap: 12px;
        font-size: 12px;
    }

    .item-detail-group {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .item-detail-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
    }

    .item-detail-value {
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
    }

    .item-subtotal {
        text-align: right;
        margin-left: 12px;
        min-width: 90px;
    }

    .subtotal-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
    }

    .subtotal-value {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
        text-align: center;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.4;
    }

    .empty-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 6px;
    }

    .empty-text {
        font-size: 13px;
        color: #6b7280;
    }

    .display-summary {
        border-top: 1px solid #e5e7eb;
        padding-top: 16px;
        space-y: 12px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        font-size: 13px;
    }

    .summary-label {
        font-weight: 600;
        color: #6b7280;
    }

    .summary-value {
        font-weight: 700;
        color: #1f2937;
    }

    .discount-row {
        background: #fef2f2;
        padding: 10px;
        border-radius: 6px;
        border-left: 3px solid #dc2626;
    }

    .discount-row .summary-label {
        color: #991b1b;
        font-weight: 700;
    }

    .discount-row .summary-value {
        color: #991b1b;
        font-weight: 700;
    }

    .total-row {
        background: #1f2937;
        color: white;
        padding: 12px;
        border-radius: 6px;
        margin-top: 12px;
    }

    .total-row .summary-label {
        color: #d1d5db;
        font-size: 12px;
    }

    .total-row .summary-value {
        color: white;
        font-size: 18px;
        font-weight: 800;
    }

    .footer-info {
        text-align: center;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
        color: #9ca3af;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .pulse {
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    @media (max-width: 1400px) {
        .nav-button.prev {
            left: 20px;
        }

        .nav-button.next {
            right: 20px;
        }
    }

    @media (max-width: 1200px) {
        .display-wrapper {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .carousel-slide {
            width: 160px;
        }
    }

    @media (max-width: 1024px) {
        .display-wrapper {
            grid-template-columns: 1fr;
        }

        .item-details {
            flex-direction: column;
            gap: 4px;
        }

        .item-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .item-subtotal {
            width: 100%;
            text-align: left;
            margin-left: 0;
            margin-top: 8px;
        }
    }
</style>

<script>
    let currentSlide = 0;
    let receiptTimeout = null;

    function showSlide(index) {
        const slides = document.querySelectorAll('.promo-slide');
        const indicators = document.querySelectorAll('.indicator');

        // Hapus active dari semua
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));

        // Tambah active ke slide yang dipilih
        if (slides[index]) {
            slides[index].classList.add('active');
        }
        if (indicators[index]) {
            indicators[index].classList.add('active');
        }

        currentSlide = index;
    }

    function changeSlide(direction) {
        const slides = document.querySelectorAll('.promo-slide');
        currentSlide += direction;

        // Loop ke awal/akhir
        if (currentSlide >= slides.length) {
            currentSlide = 0;
        } else if (currentSlide < 0) {
            currentSlide = slides.length - 1;
        }

        showSlide(currentSlide);
    }

    function goToSlide(index) {
        showSlide(index);
    }

    // Inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        showSlide(0);
        setupReceiptAutoHide();
    });

    // Auto scroll ke item terbaru di customer display
    document.addEventListener('livewire:updated', function(event) {
        scrollToLatestItem();
        setupReceiptAutoHide();
    });

    document.addEventListener('livewire:navigated', function(event) {
        scrollToLatestItem();
        setupReceiptAutoHide();
    });

    function scrollToLatestItem() {
        setTimeout(function() {
            const itemsList = document.querySelector('.items-list');
            if (itemsList && itemsList.children.length > 0) {
                const lastItem = itemsList.lastElementChild;
                lastItem.scrollIntoView({behavior: 'smooth', block: 'end'});
            }
        }, 100);
    }

    function setupReceiptAutoHide() {
        // Clear timeout sebelumnya jika ada
        if (receiptTimeout) {
            clearTimeout(receiptTimeout);
        }

        // Check apakah receipt ditampilkan
        const receiptOverlay = document.querySelector('.receipt-overlay');
        if (receiptOverlay) {
            // Auto hide setelah 5 detik
            receiptTimeout = setTimeout(function() {
                @this.hideReceipt();
            }, 5000);
        }
    }

    // Listen untuk dispatch scheduleHideReceipt
    document.addEventListener('livewire:init', () => {
        Livewire.on('scheduleHideReceipt', () => {
            console.log('Receipt akan disembunyikan dalam 5 detik...');
            receiptTimeout = setTimeout(function() {
                @this.hideReceiptAfter();
            }, 5000);
        });
    });
</script>

