## Persiapan Project Kasir

1. Local Server Laragon/Xampp 
2. Composer
3. Git
4. Node.js
5. php version >= 8.3

*Sisi eksternal*

1. **Printer Thermal ukuran 58mm** (Sambungkan printer ke komputer/laptop, jika belum terdaftar pada komputer/laptop maka install driver printer terlebih dahulu atau tonton video tutorial di youtube terkait masalah ini) setelah itu salin nama printer yang ada di properties printer dimenu sharing yang telah terdaftar pada komputer/laptop ke dalam menu setting printer pada web kasirnya.

    **Opsi Koneksi Printer:**
    
    a. **Via Kabel (Direct Print)** - Rekomendasi untuk koneksi stabil
       - Printer akan langsung terhubung ke komputer via USB/LAN
       - Cetak struk langsung ke printer tanpa dialog browser
       - Mendukung **Auto Print**: Struk otomatis dicetak setelah transaksi selesai
       - Hanya berjalan pada server local/dalam satu jaringan yang terhubung ke printer
       - Nama printer harus sesuai dengan yang terdaftar di Windows
       - Contoh nama printer: "Epson T20", "Generic / Text Only", "POS-80"
    
    b. **Via Bluetooth** 
       - Printer terhubung langsung dari browser (hanya Chrome/Edge yang support)
       - Memerlukan koneksi Bluetooth setiap kali membuka halaman kasir
       - Cocok untuk penggunaan mobile/tablet
    
    **Cara Setting Printer Via Kabel:**
    1. Install driver printer dan pastikan sudah terdaftar di Windows
    2. Buka **Pengaturan Toko** > **Pengaturan Printer**
    3. Pilih **"Kabel (Server Local)"**
    4. Masukkan nama printer sesuai yang terlihat di Windows (case-sensitive)
    5. Klik tombol **"Test"** untuk memastikan printer terhubung
    6. (Opsional) Aktifkan **"Cetak Otomatis"** untuk print otomatis setelah transaksi
    7. Simpan pengaturan

    **🖨️ Multi-Kasir: Pengaturan Printer Per-User**
    
    Sistem mendukung multi-kasir dengan printer berbeda di setiap PC:
    
    1. Setiap kasir login dengan akun masing-masing
    2. Buka menu **Pengaturan** > **Printer Saya**
    3. Atur nama printer sesuai yang terinstall di PC kasir tersebut
    4. Jika tidak diset, kasir akan menggunakan printer global dari Pengaturan Toko
    
    **Prioritas Printer:**
    - Jika kasir sudah set printer sendiri → Gunakan printer kasir
    - Jika kasir belum set printer → Gunakan printer global (Pengaturan Toko)

    **Troubleshooting Printer:**
    
    | Error | Solusi |
    |-------|--------|
    | "Printer tidak ditemukan" | Pastikan nama printer sudah benar dan printer sudah terinstall di Windows |
    | "Image format not supported" | Gunakan logo format PNG atau JPG. Sistem akan tetap mencetak tanpa logo |
    | "Access is denied" | Jalankan aplikasi/server sebagai Administrator |
    | "Printer offline" | Periksa koneksi USB/LAN printer dan pastikan printer dalam keadaan menyala |
    | Logo tidak muncul di struk | Pastikan logo menggunakan format PNG, dan ukuran tidak terlalu besar (max 300x300px) |

2. **Scanner QR Code** dengan Kamera maupun alat scanner (Opsional)

## Setup Project Kasir

Perhatikan untuk menjalankan atau mensetup project ini.

1. Buat database terlebih dahulu
2. Konfigurasikan file .env dengan database yang telah dibuat
3. Jalankan perintah `php artisan migrate` dan `php artisan db:seed` atau `php artisan migrate:fresh --seed` (untuk membuat data default pertama)
4. Jalankan perintah `php artisan shield:generate --all` (untuk generate policy dari semua model)
5. Jalankan perintah `php artisan shield:super-admin` (untuk menambahkan/assign role super_admin ke user tertentu)
6. Jalankan perintah `php artisan storage:link`  untuk membuat symlink
7. Jalankan perintah `php artisan serve` untuk menjalankan projek
8. Buka browser dan kunjungi link http://127.0.0.1:8000
9. Login dengan email (admin@gmail.com) dan password (admin123)

Aplikasi siap di gunakan....
## 🔧 Troubleshooting Upload Gambar di Laragon

Jika upload gambar **TIDAK berfungsi** saat deploy ke **Laragon** (padahal dengan `php artisan serve` berfungsi), jalankan salah satu dari:

### Solusi 1: Auto Installer (Recommended)
```cmd
INSTALL.bat
```
Installer akan:
- ✅ Membuat folder livewire-tmp yang diperlukan
- ✅ Set permission storage dengan benar
- ✅ Buat ulang symbolic link dengan format Windows
- ✅ Clear dan cache ulang konfigurasi Laravel

### Solusi 2: Quick Setup
```cmd
setup-laragon.bat
```

### Test Upload Permission
Setelah setup, buka: **http://pos_griya.test/test-storage.php**
- Semua check harus menunjukkan: **YES ✅**
- Jika ada yang **NO ❌**, jalankan `diagnostic-laragon.bat`

### File Helper yang Tersedia:
| File | Fungsi |
|------|--------|
| `INSTALL.bat` | Installer lengkap dengan step-by-step |
| `setup-laragon.bat` | Quick setup otomatis |
| `diagnostic-laragon.bat` | Cek masalah dan diagnosa |
| `SETUP_LARAGON.md` | Dokumentasi lengkap |
| `BACA_DULU.txt` | Panduan singkat |
| `public/test-storage.php` | Test permission via browser |

Untuk detail lengkap, baca: **[SETUP_LARAGON.md](SETUP_LARAGON.md)**
