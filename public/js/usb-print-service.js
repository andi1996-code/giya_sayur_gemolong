/**
 * USB Print Service Integration
 *
 * This module handles printing to thermal printers via local Python Flask server.
 * Server runs at http://127.0.0.1:9100 using python-escpos library.
 *
 * VERSION: 1.0.0 - 18 Jan 2026
 *
 * Requirements:
 * - Python Flask server running (print_service.py)
 * - python-escpos library installed
 * - Printer name configured in Settings
 */

// Prevent double loading
if (typeof window.usbPrintServiceLoaded !== 'undefined') {
    console.warn('⚠️ USB Print Service module already loaded, skipping...');
} else {
    window.usbPrintServiceLoaded = true;

console.log('🔄 Loading USB Print Service Module v1.0.0...');

const PRINT_SERVICE_URL = 'http://127.0.0.1:9100';

/**
 * Cache key for printer name in localStorage
 */
const PRINTER_CACHE_KEY = 'usb_print_printer_name';

/**
 * Fetch printer name from Settings API
 */
async function getPrinterNameFromSettings() {
    try {
        // Check localStorage cache first (valid for 1 hour)
        const cached = localStorage.getItem(PRINTER_CACHE_KEY);
        if (cached) {
            console.log('📦 Using cached printer name:', cached);
            return cached;
        }

        // Fetch from API if not cached
        const response = await fetch('/api/settings/printer-name', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (response.ok) {
            const data = await response.json();
            if (data.printer_name) {
                console.log('📡 Fetched printer name from Settings:', data.printer_name);
                // Cache for 1 hour
                localStorage.setItem(PRINTER_CACHE_KEY, data.printer_name);
                return data.printer_name;
            }
        }
    } catch (error) {
        console.warn('⚠️ Failed to fetch printer name from API:', error);
    }
    return null;
}

/**
 * Check if print service is running
 */
async function checkPrintService() {
    try {
        const response = await fetch(`${PRINT_SERVICE_URL}/ping`, {
            method: 'GET',
            mode: 'cors',
        });

        if (response.ok) {
            console.log('✅ Print service is running');
            return true;
        }
        return false;
    } catch (error) {
        console.error('❌ Print service not reachable:', error);
        return false;
    }
}

/**
 * Format ESC/POS receipt data (same format as QZ Tray & Bluetooth)
 */
function formatReceiptData(data) {
    const ESC = '\x1B';
    const GS = '\x1D';

    let receipt = '';

    // Initialize printer
    receipt += ESC + '@'; // Reset printer
    receipt += ESC + 'a' + '\x01'; // Center alignment
    receipt += ESC + '!' + '\x10'; // Bold and large text
    receipt += (data.store?.name || 'TOKO') + '\n';
    receipt += ESC + '!' + '\x00'; // Normal text
    receipt += (data.store?.address || '') + '\n';
    receipt += 'Telp: ' + (data.store?.phone || '') + '\n';
    receipt += '================================\n';
    receipt += ESC + 'a' + '\x00'; // Left alignment

    // Transaction details
    receipt += 'Kode Transaksi: ' + (data.order?.transaction_number || '') + '\n';
    receipt += 'Pembayaran: ' + (data.order?.payment_method?.name || 'Tunai') + '\n';
    receipt += 'Tanggal: ' + (data.date || '') + '\n';
    if (data.cashier && data.cashier.name) {
        receipt += 'Kasir: ' + data.cashier.name + '\n';
    }

    // Member Information
    if (data.member && data.member.name) {
        receipt += '--------------------------------\n';
        receipt += ESC + '!' + '\x08'; // Bold text
        receipt += 'Member: ' + data.member.name + '\n';
        receipt += ESC + '!' + '\x00'; // Normal text
        receipt += 'Kode: ' + (data.member.member_code || '') + '\n';
        receipt += 'Tier: ' + (data.member.tier || '').toUpperCase() + '\n';

        // Show earned points if available
        if (data.order?.points_earned && data.order.points_earned > 0) {
            receipt += ESC + '!' + '\x08'; // Bold text
            receipt += 'Poin Didapat: +' + data.order.points_earned + ' poin\n';
            receipt += ESC + '!' + '\x00'; // Normal text
        }

        // Show redeemed points if available
        if (data.order?.points_redeemed && data.order.points_redeemed > 0) {
            receipt += 'Poin Digunakan: -' + data.order.points_redeemed + ' poin\n';
        }

        // Show total points after transaction
        receipt += 'Total Poin: ' + (data.member.total_points || 0) + ' poin\n';
    }

    receipt += '================================\n';
    receipt += formatRow('Nama Barang', 'Qty', 'Harga') + '\n';
    receipt += '--------------------------------\n';

    // Items
    let total = 0;
    if (data.items && data.items.length > 0) {
        data.items.forEach(item => {
            let displayQty, displayPrice;

            if (item.weight && item.weight > 0) {
                const weightValue = parseFloat(item.weight);
                displayQty = weightValue + 'kg';
                displayPrice = formatMoney(item.price || 0);
            } else {
                displayQty = item.quantity || 0;
                displayPrice = formatMoney(item.price || 0);
            }

            const productName = item.product?.name || item.product_name || 'Produk';
            receipt += formatRow(productName, displayQty, displayPrice) + '\n';
            total += (item.quantity || 0) * (item.price || 0);
        });
    }

    receipt += '--------------------------------\n';
    receipt += formatRow('Total', '', formatMoney(total)) + '\n';

    // Promo discount
    if (data.order?.promo_discount && data.order.promo_discount > 0) {
        receipt += formatRow('Diskon Promo', '', '-' + formatMoney(data.order.promo_discount)) + '\n';
        receipt += formatRow('Total Akhir', '', formatMoney(total - data.order.promo_discount)) + '\n';
    }

    // Payment details
    receipt += formatRow('Nominal Bayar', '', formatMoney(data.order?.cash_received || 0)) + '\n';
    receipt += formatRow('Kembalian', '', formatMoney(data.order?.change || 0)) + '\n';
    receipt += '================================\n';
    receipt += ESC + 'a' + '\x01'; // Center alignment

    // Footer
    if (data.store?.receipt_footer_line1) {
        receipt += data.store.receipt_footer_line1 + '\n';
    }
    if (data.store?.receipt_footer_line2) {
        receipt += data.store.receipt_footer_line2 + '\n';
    }
    if (data.store?.receipt_footer_line3) {
        receipt += data.store.receipt_footer_line3 + '\n';
    }

    // Footer note
    if (data.store?.receipt_footer_note) {
        receipt += data.store.receipt_footer_note + '\n';
    }

    // Thank you message
    if (data.store?.show_footer_thank_you !== false) {
        receipt += '*** TERIMA KASIH ***\n';
    }

    receipt += '================================';
    receipt += ESC + 'a' + '\x00'; // Left alignment
    receipt += "\x1B\x70\x00\x3C\xFF"; // Buka cash drawer

    return receipt;
}

/**
 * Print receipt via USB Print Service
 */
async function printReceiptUSB(receiptData, printerName = null) {
    try {
        console.log('📄 Preparing to print receipt via USB Print Service...');
        console.log('Receipt data:', receiptData);

        // Check if print service is running
        const serviceRunning = await checkPrintService();
        if (!serviceRunning) {
            throw new Error('Print service tidak berjalan. Pastikan print_service.py sudah dijalankan.');
        }

        // Get printer name: parameter > receiptData > API > localStorage
        let printer = printerName || receiptData.printerName || receiptData.store?.printer_name;

        // If not provided, fetch from Settings API
        if (!printer) {
            console.log('🔍 Printer name not provided, fetching from Settings...');
            printer = await getPrinterNameFromSettings();
        }

        if (!printer) {
            throw new Error('Nama printer tidak ditemukan. Silakan set nama printer di Settings.');
        }

        console.log('🖨️ Using printer:', printer);

        // Format receipt data
        const formattedReceipt = formatReceiptData(receiptData);
        console.log('📋 Receipt formatted, length:', formattedReceipt.length);

        // Send to print service
        console.log('🚀 Sending to print service...');
        const response = await fetch(`${PRINT_SERVICE_URL}/print`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                printer_name: printer,
                receipt: formattedReceipt
            })
        });

        const result = await response.json();

        if (result.status === 'ok') {
            console.log('✅ Print job sent successfully');

            // Notify user
            if (window.Livewire) {
                window.Livewire.dispatch('notify', {
                    type: 'success',
                    message: 'Struk berhasil dicetak!'
                });
            }

            return true;
        } else {
            throw new Error(result.message || 'Gagal mencetak');
        }
    } catch (error) {
        console.error('❌ Print error:', error);

        // Show user-friendly error message
        if (window.Livewire) {
            window.Livewire.dispatch('notify', {
                type: 'error',
                message: error.message || 'Gagal mencetak struk'
            });
        }

        return false;
    }
}

/**
 * Helper function to format money
 */
function formatMoney(amount) {
    if (amount === null || amount === undefined || isNaN(amount)) {
        amount = 0;
    }
    return new Intl.NumberFormat('id-ID', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

/**
 * Helper function to format row
 * Format: Name (14 chars) | Qty (8 chars) | Price (10 chars)
 */
function formatRow(name, qty, price) {
    const nameWidth = 14;
    const qtyWidth = 8;
    const priceWidth = 10;

    name = String(name || '').substring(0, nameWidth);
    qty = String(qty || '').substring(0, qtyWidth);
    price = String(price || '').substring(0, priceWidth);

    return name.padEnd(nameWidth, ' ') + qty.padStart(qtyWidth, ' ') + price.padStart(priceWidth, ' ');
}

/**
 * Listen for Livewire events to print receipt
 */
document.addEventListener('livewire:init', () => {
    // Listen for USB print event from Livewire
    Livewire.on('doPrintReceiptUSB', async (data) => {
        console.log('🎯 USB Print event received:', data);

        // Get printer name from data
        const printerName = data[0]?.printerName || null;

        await printReceiptUSB(data[0], printerName);
    });
});

/**
 * Test function to check print service connection
 */
async function testPrintService() {
    try {
        const running = await checkPrintService();
        if (running) {
            console.log('✅ Print service is running!');
            alert('Print service OK! Server berjalan di ' + PRINT_SERVICE_URL);
            return true;
        } else {
            console.error('❌ Print service not running');
            alert('Print service TIDAK berjalan.\nJalankan: python print_service.py');
            return false;
        }
    } catch (error) {
        console.error('❌ Print service test failed:', error);
        alert('Error: ' + error.message);
        return false;
    }
}

/**
 * Test print function
 */
async function testPrint(printerName) {
    if (!printerName) {
        // Try to get from Settings API first
        console.log('📡 Fetching printer name from Settings...');
        printerName = await getPrinterNameFromSettings();

        if (!printerName) {
            printerName = prompt('Masukkan nama printer (contoh: POS-58):');
            if (!printerName) return;
        }
    }

    const testData = {
        store: {
            name: 'TEST TOKO',
            address: 'Jl. Test No. 123',
            phone: '08123456789',
            printer_name: printerName
        },
        order: {
            transaction_number: 'TEST-001',
            payment_method: { name: 'Tunai' },
            cash_received: 50000,
            change: 10000
        },
        date: new Date().toLocaleString('id-ID'),
        cashier: { name: 'Admin' },
        items: [
            { product: { name: 'Test Item 1' }, quantity: 2, price: 15000 },
            { product: { name: 'Test Item 2' }, quantity: 1, price: 10000 }
        ],
        printerName: printerName
    };

    console.log('🧪 Running test print...');
    await printReceiptUSB(testData, printerName);
}

/**
 * Get printer name from Settings API (for testing in console)
 */
async function testGetPrinterName() {
    console.log('📡 Fetching printer name from Settings API...');
    const printer = await getPrinterNameFromSettings();
    if (printer) {
        console.log('✅ Printer name:', printer);
    } else {
        console.warn('⚠️ No printer name found in Settings');
    }
    return printer;
}

// Export functions for use in console/debugging
window.usbPrintHelper = {
    test: testPrintService,
    testPrint: testPrint,
    testGetPrinterName: testGetPrinterName,
    print: printReceiptUSB,
    checkService: checkPrintService,
    getPrinterName: getPrinterNameFromSettings,
    version: '1.0.0'
};

console.log('✅ USB Print Service Module v1.0.0 Loaded Successfully');
console.log('💡 Test print service with: window.usbPrintHelper.test()');
console.log('🖨️ Test print with: window.usbPrintHelper.testPrint()');
console.log('📡 Get printer name: window.usbPrintHelper.testGetPrinterName()');
console.log('📌 Check version: window.usbPrintHelper.version');

} // End of double-load prevention
