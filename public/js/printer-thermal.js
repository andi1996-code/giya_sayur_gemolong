// Prevent double loading
if (typeof window.printerThermalLoaded !== 'undefined') {
    console.warn('⚠️ Printer Thermal module already loaded, skipping...');
} else {
    window.printerThermalLoaded = true;

const connectButton = document.getElementById('connect-button');

if (connectButton) {
    connectButton.addEventListener('click', async () => {
        window.connectedPrinter = await getPrinter();

        if (window.connectedPrinter) {
            console.log("Berhasil menyambungkan ke printer.");
        } else {
            console.error("Gagal menyambungkan ke printer.");
        }
    });
}

// fungsi ini untuk mencari jaringan printer bluetooth
async function getPrinter() {
    try {
        const device = await navigator.bluetooth.requestDevice({
            filters: [{
                    namePrefix: "RPP"
                },
                {
                    namePrefix: "Thermal"
                },
                {
                    namePrefix: "POS"
                }
            ],
            optionalServices: ["000018f0-0000-1000-8000-00805f9b34fb"]
        });

        alert("Perangkat berhasil tersambung:", device.name);
        return device;
    } catch (e) {
        alert("Perangkat gagal tersambung");
        console.error("Perangkat gagal tersambung, Erorr :", e);
        return null;
    }
}

// Fungsi ini di lempar dari livewire untuk ngeprint dan mengerimkan data
document.addEventListener('livewire:init', () => {
    Livewire.on('doPrintReceipt', async (data) => {
        console.log(data)
        await printThermalReceipt(data);
    });
});


// Fungsi ini untuk mencetak struk
async function printThermalReceipt(data) {
    try {
        if (!window.connectedPrinter) {
            window.connectedPrinter = await getPrinter();
        }

        console.log("Menyambungkan ke printer...");
        const server = await window.connectedPrinter.gatt.connect();
        const service = await server.getPrimaryService("000018f0-0000-1000-8000-00805f9b34fb");
        const characteristic = await service.getCharacteristic("00002af1-0000-1000-8000-00805f9b34fb");

        console.log("Printer siap, mengirim struk...");

        const encoder = new TextEncoder();

        let receipt = "\x1B\x40"; // Reset printer
        receipt += "\x1B\x61\x01"; // Perataan Tengah
        receipt += "\x1B\x21\x10"; // Text tebal dan besar
        receipt += data.store.name + "\n";
        receipt += "\x1B\x21\x00"; // Normal text
        receipt += data.store.address + "\n";
        receipt += "Telp: " + data.store.phone + "\n";
        receipt += "================================\n";
        receipt += "\x1B\x61\x00"; // Kembalikan ke rata kiri

        // Detail Transaksi
        receipt += "Kode Transaksi: " + data.order.transaction_number + "\n";
        receipt += "Pembayaran: " + data.order.payment_method.name + "\n";
        receipt += "Tanggal: " + data.date + "\n";
        if (data.cashier && data.cashier.name) {
            receipt += "Kasir: " + data.cashier.name + "\n";
        }

        // Member Information (if exists)
        if (data.member && data.member.name) {
            receipt += "--------------------------------\n";
            receipt += "\x1B\x21\x08"; // Bold text
            receipt += "Member: " + data.member.name + "\n";
            receipt += "\x1B\x21\x00"; // Normal text
            receipt += "Kode: " + data.member.member_code + "\n";
            receipt += "Tier: " + data.member.tier.toUpperCase() + "\n";

            // Show earned points if available
            if (data.order.points_earned && data.order.points_earned > 0) {
                receipt += "\x1B\x21\x08"; // Bold text
                receipt += "Poin Didapat: +" + data.order.points_earned + " poin\n";
                receipt += "\x1B\x21\x00"; // Normal text
            }

            // Show redeemed points if available
            if (data.order.points_redeemed && data.order.points_redeemed > 0) {
                receipt += "Poin Digunakan: -" + data.order.points_redeemed + " poin\n";
            }

            // Show total points after transaction
            receipt += "Total Poin: " + (data.member.total_points || 0) + " poin\n";
        }

        receipt += "================================\n";
        receipt += formatRow("Nama Barang", "Qty", "Harga") + "\n";
        receipt += "--------------------------------\n";

        let total = 0;
        data.items.forEach(item => {
            let displayQty, displayPrice;

            if (item.weight && item.weight > 0) {
                // For weight-based products, display weight in kg without trailing zeros
                const weightValue = parseFloat(item.weight);
                displayQty = weightValue + "kg";
                displayPrice = formatRibuan(item.price);
            } else {
                // For regular products - show quantity
                displayQty = item.quantity;
                displayPrice = formatRibuan(item.price);
            }

            receipt += formatRow(item.product.name, displayQty, displayPrice) + "\n";
            total += item.quantity * item.price;
        });

        receipt += "--------------------------------\n";
        receipt += formatRow("Total", "", formatRibuan(total)) + "\n";

        // Jika ada promo diskon
        if (data.order.promo_discount && data.order.promo_discount > 0) {
            receipt += formatRow("Diskon Promo", "", "-" + formatRibuan(data.order.promo_discount)) + "\n";
            receipt += formatRow("Total Akhir", "", formatRibuan(total - data.order.promo_discount)) + "\n";
        }

        receipt += formatRow("Nominal Bayar", "", formatRibuan(data.order.cash_received)) + "\n";
        receipt += formatRow("Kembalian", "", formatRibuan(data.order.change)) + "\n";
        receipt += "================================\n";
        receipt += "\x1B\x61\x01"; // Perataan Tengah

        // Footer Struk (Fleksibel dari Setting)
        if (data.store.receipt_footer_line1) {
            receipt += data.store.receipt_footer_line1 + "\n";
        }
        if (data.store.receipt_footer_line2) {
            receipt += data.store.receipt_footer_line2 + "\n";
        }
        if (data.store.receipt_footer_line3) {
            receipt += data.store.receipt_footer_line3 + "\n";
        }

        // Footer note (jika ada)
        if (data.store.receipt_footer_note) {
            receipt += data.store.receipt_footer_note + "\n";
        }

        // Thank you message (jika diaktifkan)
        if (data.store.show_footer_thank_you !== false) {
            receipt += "*** TERIMA KASIH ***\n";
        }

        receipt += "================================";
        receipt += "\x1B\x61\x00"; // Kembalikan ke rata kiri
        receipt += "\x1D\x56\x00"; // ESC/POS cut paper
        receipt += "\x1B\x70\x00\x3C\xFF"; // Buka cash drawer
        await sendChunks(characteristic, encoder.encode(receipt));

        console.log("Sukses mencetak struk dan membuka cash drawer");
    } catch (e) {
        console.error("Failed to print thermal", e);
    }
}

//  Fungsi untuk Mengirim Data dalam Potongan Kecil agar tidak ada batasan print
async function sendChunks(characteristic, data) {
    const chunkSize = 180; // BLE limit
    let offset = 0;

    while (offset < data.length) {
        let chunk = data.slice(offset, offset + chunkSize);
        await characteristic.writeValue(chunk);
        offset += chunkSize;
    }
}

function formatRibuan(number) {
    // Format ke Rupiah dengan titik sebagai pemisah ribuan
    // Contoh: 50000 menjadi 50.000, 100000 menjadi 100.000
    return new Intl.NumberFormat('id-ID', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number);
}

//  Fungsi untuk Format Teks agar Rapi
function formatRow(name, qty, price) {
    const nameWidth = 14,
        qtyWidth = 8,
        priceWidth = 10;

    // Trim dan potong nama jika terlalu panjang
    name = name.substring(0, nameWidth);
    qty = qty.toString().substring(0, qtyWidth);
    price = price.toString().substring(0, priceWidth);

    // Rata kiri untuk name, rata kanan untuk qty dan price
    let line = name.padEnd(nameWidth) + qty.padStart(qtyWidth) + price.padStart(priceWidth);

    return line;
}

} // End of double-load prevention





