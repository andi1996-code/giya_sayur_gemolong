# CHECKLIST INSTALASI POS GRIYA DI KOMPUTER CLIENT

## ✅ Checklist Sebelum Instalasi

- [ ] Laragon sudah terinstall dan running
- [ ] PHP versi >= 8.3
- [ ] Composer sudah terinstall
- [ ] Database sudah dibuat
- [ ] File .env sudah dikonfigurasi

## ✅ Checklist Instalasi Laravel

- [ ] Run: `composer install`
- [ ] Run: `php artisan key:generate`
- [ ] Run: `php artisan migrate --seed`
- [ ] Run: `php artisan shield:generate --all`
- [ ] Run: `php artisan shield:super-admin`

## ✅ Checklist Fix Upload Gambar (PENTING!)

Pilih salah satu:

### Opsi 1: Auto Installer (Recommended)
- [ ] Double-click: `INSTALL.bat`
- [ ] Tunggu sampai selesai
- [ ] Buka: http://pos_griya.test/test-storage.php
- [ ] Pastikan semua check: **YES ✅**

### Opsi 2: Quick Setup
- [ ] Double-click: `setup-laragon.bat`
- [ ] Buka: http://pos_griya.test/test-storage.php
- [ ] Pastikan semua check: **YES ✅**

## ✅ Checklist Testing

- [ ] Login ke aplikasi (admin@gmail.com / admin123)
- [ ] Buka menu POS/Kasir
- [ ] Coba scan barcode produk
- [ ] Coba tambah produk ke keranjang
- [ ] Coba checkout transaksi
- [ ] **PENTING:** Coba upload gambar produk
- [ ] Coba print struk (jika printer sudah di-setup)

## ✅ Checklist Printer Setup (Opsional)

- [ ] Printer thermal sudah terinstall di Windows
- [ ] Catat nama printer yang terlihat di Windows
- [ ] Login ke aplikasi
- [ ] Buka: **Pengaturan Toko** > **Pengaturan Printer**
- [ ] Pilih mode: **Kabel (Server Local)**
- [ ] Masukkan nama printer
- [ ] Klik tombol **Test** untuk test print
- [ ] Jika berhasil, klik **Simpan**

## 🆘 Jika Ada Masalah

### Upload Gambar Tidak Berfungsi
- [ ] Jalankan: `diagnostic-laragon.bat`
- [ ] Screenshot hasilnya
- [ ] Kirim ke developer

### Printer Tidak Berfungsi
- [ ] Pastikan printer sudah terinstall di Windows
- [ ] Pastikan nama printer sudah benar (case-sensitive)
- [ ] Test print dari Windows terlebih dahulu
- [ ] Periksa apakah print service sudah running

### Error Lainnya
- [ ] Cek file log: `storage/logs/laravel.log`
- [ ] Screenshot error message
- [ ] Kirim ke developer

## 📝 Catatan Penting

1. **Upload gambar di Laragon** memerlukan setup khusus (sudah disediakan script `INSTALL.bat`)
2. **Dengan `php artisan serve`** tidak ada masalah upload
3. Masalah disebabkan oleh:
   - Folder `livewire-tmp` tidak ada
   - Symbolic link format Unix tidak bekerja di Windows
   - Permission storage belum di-set untuk web server

4. Semua masalah sudah diperbaiki dengan menjalankan `INSTALL.bat` atau `setup-laragon.bat`

---

**Updated:** January 20, 2026
**Version:** 1.0
