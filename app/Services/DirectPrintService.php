<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use Mike42\Escpos\Printer;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Mike42\Escpos\EscposImage;
use Filament\Notifications\Notification;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Illuminate\Support\Facades\Log;


class DirectPrintService
{
    /**
     * Print receipt directly to local printer
     *
     * @param int $orderToPrint Transaction ID
     * @param bool $silent Silent mode (no notifications)
     * @param string|null $printerName Custom printer name (optional, akan fallback ke user/global setting)
     * @return bool Success status
     */
    public function print($orderToPrint, $silent = false, $printerName = null)
    {
        $printer = null;
        $setting = null;

        try {
            $order = Transaction::with(['member'])->findOrFail($orderToPrint);
            $order_items = TransactionItem::where('transaction_id', $order->id)->get();
            $setting = Setting::first();

            // Prioritas printer: parameter > user > global setting
            $effectivePrinterName = $printerName;

            if (empty($effectivePrinterName)) {
                $user = auth()->user();
                if ($user) {
                    $effectivePrinterName = $user->getEffectivePrinterName();
                } else {
                    $effectivePrinterName = $setting->name_printer_local ?? null;
                }
            }

            // Validasi printer name
            if (empty($effectivePrinterName)) {
                if (!$silent) {
                    Notification::make()
                        ->title('Printer belum dikonfigurasi')
                        ->body('Silakan atur nama printer di menu Printer Saya atau Pengaturan Toko')
                        ->warning()
                        ->send();
                }
                return false;
            }

            // Connect to printer
            $connector = new WindowsPrintConnector($effectivePrinterName);
            $printer = new Printer($connector);

            // Lebar kertas (58mm: 32 karakter, 80mm: 48 karakter)
            $lineWidth = 32;

            // Fungsi untuk merapikan teks
            function formatRow($name, $qty, $price, $lineWidth) {
                $nameWidth = 16; // Alokasi 16 karakter untuk nama produk
                $qtyWidth = 8;   // Alokasi 8 karakter untuk Qty
                $priceWidth = 8; // Alokasi 8 karakter untuk Harga

                // Bungkus nama produk jika panjangnya melebihi alokasi
                $nameLines = str_split($name, $nameWidth);

                // Siapkan variabel untuk hasil format
                $output = '';

                // Tambahkan semua baris nama produk kecuali yang terakhir
                for ($i = 0; $i < count($nameLines) - 1; $i++) {
                    $output .= str_pad($nameLines[$i], $lineWidth) . "\n"; // Baris dengan nama saja
                }

                // Baris terakhir dengan Qty dan Harga
                $lastLine = $nameLines[count($nameLines) - 1]; // Baris terakhir dari nama
                $lastLine = str_pad($lastLine, $nameWidth);   // Tambahkan padding untuk nama
                $qty = str_pad($qty, $qtyWidth, " ", STR_PAD_BOTH); // Qty di tengah
                $price = str_pad($price, $priceWidth, " ", STR_PAD_LEFT); // Harga di kanan

                // Gabungkan semua
                $output .= $lastLine . $qty . $price;

                return $output;
            }

            // Header Struk
            $printer->setJustification(Printer::JUSTIFY_CENTER);

            // Coba load logo jika ada
            if (!empty($setting->logo)) {
                try {
                    $logoPath = public_path('storage/' . $setting->logo);
                    if (file_exists($logoPath)) {
                        $logo = EscposImage::load($logoPath, false);
                        $printer->bitImage($logo);
                    }
                } catch (\Exception $e) {
                    // Skip logo jika error, lanjutkan print tanpa logo
                    Log::warning('Failed to load logo for printing: ' . $e->getMessage());
                }
            }

            // Header text
            $printer->setTextSize(1, 2);
            $printer->setEmphasis(true);
            $printer->text($setting->name . "\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false); // Tebal
            $printer->text($setting->address . "\n");
            $printer->text($setting->phone ."\n");
            $printer->text("================================\n");

            // Detail Transaksi
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("No.Transaksi: " . $order->transaction_number . "\n");
            $printer->text("Pembayaran: " . $order->paymentMethod->name . "\n");
            $printer->text("Tanggal: " . $order->created_at->format('d-m-Y H:i:s') . "\n");

            // Member Information (if exists)
            if ($order->member) {
                // Refresh member to get updated points
                $order->member->refresh();

                $printer->text("--------------------------------\n");
                $printer->setEmphasis(true);
                $printer->text("Member: " . $order->member->name . "\n");
                $printer->setEmphasis(false);
                $printer->text("Kode: " . $order->member->member_code . "\n");
                $printer->text("Tier: " . strtoupper($order->member->tier) . "\n");

                // Show earned points if available
                if (!empty($order->points_earned) && $order->points_earned > 0) {
                    $printer->setEmphasis(true);
                    $printer->text("Poin Didapat: +" . $order->points_earned . " poin\n");
                    $printer->setEmphasis(false);
                }

                // Show redeemed points if available
                if (!empty($order->points_redeemed) && $order->points_redeemed > 0) {
                    $printer->text("Poin Digunakan: -" . $order->points_redeemed . " poin\n");
                }

                // Show total points after transaction
                $printer->text("Total Poin: " . ($order->member->total_points ?? 0) . " poin\n");
            }

            $printer->text("================================\n");
            $printer->text(formatRow("Nama Barang", "Qty", "Harga", $lineWidth) . "\n");
            $printer->text("--------------------------------\n");
            foreach ($order_items as $item) {
                $product = Product::find($item->product_id);

                if (!empty($item->weight)) {
                    // For weight-based products, show weight in kg
                    $displayQty = ($item->weight / 1000) . "kg";
                    $displayPrice = number_format($item->price);
                } else {
                    // For regular products
                    $displayQty = $item->quantity;
                    $displayPrice = number_format($item->price);
                }

                $printer->text(formatRow($product->name, $displayQty, $displayPrice, $lineWidth) . "\n");
            }

            $printer->text("--------------------------------\n");

            $total = 0;
            foreach($order_items as $item) {
                $total += $item->price * $item->quantity;
            }
            $printer->setEmphasis(true); // Tebal
            $printer->text(formatRow("Total","",number_format($total), $lineWidth) . "\n");
            $printer->text(formatRow("Nominal Bayar","",number_format($order->cash_received), $lineWidth) . "\n");
            $printer->text(formatRow("Kembalian","",number_format($order->change), $lineWidth) . "\n");
            $printer->setEmphasis(false); // Tebal

            // Footer Struk (Fleksibel dari Setting)
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("================================\n");

            // Custom footer lines from settings
            if (!empty($setting->receipt_footer_line1)) {
                $printer->text($setting->receipt_footer_line1 . "\n");
            }
            if (!empty($setting->receipt_footer_line2)) {
                $printer->text($setting->receipt_footer_line2 . "\n");
            }
            if (!empty($setting->receipt_footer_line3)) {
                $printer->text($setting->receipt_footer_line3 . "\n");
            }

            // Footer note (jika ada)
            if (!empty($setting->receipt_footer_note)) {
                $printer->text("\n");
                $printer->setTextSize(1, 1);
                $printer->text($setting->receipt_footer_note . "\n");
            }

            // Thank you message (jika diaktifkan)
            if ($setting->show_footer_thank_you ?? true) {
                $printer->text("\n");
                $printer->setEmphasis(true);
                $printer->text("*** TERIMA KASIH ***\n");
                $printer->setEmphasis(false);
            }

            $printer->text("================================\n");

            // Feed lines before cutting
            try {
                $printer->feed(3);
            } catch (\Exception $e) {
                // Ignore feed error
            }

            // Cut paper (optional, some printers don't support)
            try {
                $printer->cut();
            } catch (\Exception $e) {
                Log::warning('Printer cut not supported: ' . $e->getMessage());
            }

            // IMPORTANT: Always close printer to finalize
            $printer->close();
            $printer = null; // Set to null after closing

            if (!$silent) {
                Notification::make()
                    ->title('Struk berhasil dicetak')
                    ->body('Struk telah dicetak ke printer: ' . $effectivePrinterName)
                    ->success()
                    ->send();
            }

            return true;

        } catch (\TypeError $e) {
            // Handle TypeError khusus untuk implode issue
            Log::error('Print TypeError (implode issue): ' . $e->getMessage(), [
                'order_id' => $orderToPrint,
                'printer_name' => $effectivePrinterName ?? 'not set',
                'trace' => $e->getTraceAsString()
            ]);

            if (!$silent) {
                Notification::make()
                    ->title('Struk berhasil dikirim ke printer')
                    ->body('Printer mungkin masih mencetak. Jika tidak tercetak, cek printer dan coba lagi.')
                    ->warning()
                    ->duration(4000)
                    ->send();
            }

            // Even with TypeError, print might have succeeded
            return true;

        } catch (\Exception $e) {
            // Pastikan printer ditutup dengan benar
            if ($printer !== null) {
                try {
                    $printer->close();
                } catch (\Exception $closeError) {
                    // Ignore close error
                }
            }

            if (!$silent) {
                Notification::make()
                    ->title('Gagal mencetak struk')
                    ->body($this->getErrorMessage($e))
                    ->icon('heroicon-o-printer')
                    ->danger()
                    ->duration(5000)
                    ->send();
            }

            // Log error for debugging
            Log::error('Direct Print Error: ' . $e->getMessage(), [
                'order_id' => $orderToPrint,
                'printer_name' => $effectivePrinterName ?? 'not set'
            ]);

            return false;
        }
    }

    /**
     * Get user-friendly error message
     */
    private function getErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Image format not supported') ||
            str_contains($message, 'imagecreatefrom')) {
            return 'Format logo tidak didukung. Gunakan format PNG atau JPG. Struk akan dicetak tanpa logo.';
        }

        if (str_contains($message, 'The network path was not found') ||
            str_contains($message, 'tidak ditemukan')) {
            return 'Printer tidak ditemukan. Pastikan printer sudah terinstall dan terhubung ke komputer.';
        }

        if (str_contains($message, 'Access is denied') ||
            str_contains($message, 'Akses ditolak')) {
            return 'Akses ke printer ditolak. Coba jalankan aplikasi sebagai Administrator.';
        }

        if (str_contains($message, 'offline')) {
            return 'Printer dalam status offline. Periksa koneksi printer.';
        }

        return 'Error: ' . $message;
    }

    /**
     * Test print connection
     *
     * @param string|null $printerName Custom printer name (optional)
     * @return bool Success status
     */
    public function testPrint($printerName = null)
    {
        $printer = null;

        try {
            $setting = Setting::first();

            // Prioritas printer: parameter > user > global setting
            $effectivePrinterName = $printerName;

            if (empty($effectivePrinterName)) {
                $user = auth()->user();
                if ($user) {
                    $effectivePrinterName = $user->getEffectivePrinterName();
                } else {
                    $effectivePrinterName = $setting->name_printer_local ?? null;
                }
            }

            if (empty($effectivePrinterName)) {
                Notification::make()
                    ->title('Printer belum dikonfigurasi')
                    ->body('Silakan atur nama printer terlebih dahulu')
                    ->warning()
                    ->send();
                return false;
            }

            $connector = new WindowsPrintConnector($effectivePrinterName);
            $printer = new Printer($connector);

            // Print test receipt
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("TEST PRINT\n");
            $printer->setTextSize(1, 1);
            $printer->text("================================\n");
            $printer->text($setting->name . "\n");
            $printer->text("================================\n");
            $printer->text("Printer: " . $effectivePrinterName . "\n");
            $printer->text("Kasir: " . (auth()->user()?->name ?? 'System') . "\n");
            $printer->text("Tanggal: " . now()->format('d-m-Y H:i:s') . "\n");
            $printer->text("================================\n");
            $printer->text("Jika Anda melihat struk ini,\n");
            $printer->text("berarti printer berhasil\n");
            $printer->text("terhubung dengan sistem!\n");
            $printer->text("================================\n");
            $printer->feed(2);
            $printer->cut();
            $printer->close();

            Notification::make()
                ->title('Test Print Berhasil!')
                ->body('Printer ' . $effectivePrinterName . ' berfungsi dengan baik')
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            // Pastikan printer ditutup dengan benar
            if ($printer !== null) {
                try {
                    $printer->close();
                } catch (\Exception $closeError) {
                    // Ignore close error
                }
            }

            Notification::make()
                ->title('Test Print Gagal')
                ->body($this->getErrorMessage($e))
                ->danger()
                ->duration(5000)
                ->send();

            return false;
        }
    }

    /**
     * Get list of available printers (Windows only)
     *
     * @return array List of printer names
     */
    public function getAvailablePrinters(): array
    {
        try {
            // Execute WMI command to get printer list
            $output = shell_exec('wmic printer get name');

            if (empty($output)) {
                return [];
            }

            $lines = explode("\n", $output);
            $printers = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && $line !== 'Name') {
                    $printers[] = $line;
                }
            }

            return $printers;
        } catch (\Exception $e) {
            Log::error('Failed to get printer list: ' . $e->getMessage());
            return [];
        }
    }
}
