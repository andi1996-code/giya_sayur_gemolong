# Setup POS Griya di Laragon (Windows)

## Masalah Upload Gambar di Laragon

Jika upload gambar tidak berfungsi setelah deploy ke Laragon di komputer client, ikuti langkah-langkah berikut:

## 🚀 Solusi Cepat (Otomatis)

1. Buka folder `C:\laragon\www\pos_griya`
2. Double-click file **`setup-laragon.bat`**
3. Tunggu sampai selesai
4. Test dengan buka: **http://pos_griya.test/test-storage.php**
5. Pastikan semua check menunjukkan ✅ YES
6. Coba upload gambar di aplikasi

## 🔧 Solusi Manual (Jika Otomatis Gagal)

### Langkah 1: Buat Folder Livewire-tmp

Buka Command Prompt/Terminal di folder project, lalu jalankan:

```bash
mkdir storage\app\livewire-tmp
mkdir storage\app\public\livewire-tmp
```

### Langkah 2: Set Permission

```bash
icacls storage /grant Everyone:(OI)(CI)F /T
icacls bootstrap\cache /grant Everyone:(OI)(CI)F /T
```

### Langkah 3: Hapus & Buat Ulang Symbolic Link

```bash
# Hapus link lama
rmdir public\storage /S /Q

# Buat link baru dengan format Windows
mklink /D public\storage ..\storage\app\public
```

### Langkah 4: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
```

### Langkah 5: Test

Buka browser: **http://pos_griya.test/test-storage.php**

Pastikan semua check menunjukkan **YES ✅**

## 📋 Checklist Troubleshooting

- ✅ Folder `storage/app/livewire-tmp` ada
- ✅ Folder `storage/app/public/livewire-tmp` ada
- ✅ Permission storage sudah di-set (Everyone: Full Control)
- ✅ Symbolic link `public/storage` dibuat dengan format Windows
- ✅ Cache sudah di-clear
- ✅ Test storage berhasil (semua check hijau)

## ❓ Kenapa Terjadi?

1. **`php artisan serve`** → Bekerja karena menggunakan user yang sama dengan terminal
2. **Laragon (Apache/Nginx)** → Perlu permission khusus untuk menulis ke storage
3. **Symbolic link Unix-style** → Tidak bekerja di Windows, harus format Windows

## 📁 File Penting

- `setup-laragon.bat` - Script otomatis untuk setup
- `public/test-storage.php` - Script untuk test permission
- `config/livewire.php` - Konfigurasi Livewire upload

## 🆘 Jika Masih Error

1. Pastikan Laragon Apache/Nginx sudah running
2. Restart Laragon setelah setup
3. Cek log error di `storage/logs/laravel.log`
4. Pastikan PHP extension `fileinfo` enabled
5. Hubungi developer

---

**Catatan:** Script test (`test-storage.php`) bisa dihapus setelah upload berhasil.
