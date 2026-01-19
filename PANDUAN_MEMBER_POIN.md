# 🎯 Panduan Cepat - Modul Member & Poin

## 🚀 Cara Mulai Menggunakan

### 1️⃣ Setup Pengaturan Poin (Pertama Kali)

1. Login ke Admin Panel
2. Buka menu **Settings** → **Pengaturan Poin**
3. Atur konfigurasi sesuai kebutuhan:
   - **Nominal per Poin**: Berapa rupiah = 1 poin? (default: Rp 10.000 = 1 poin)
   - **Nilai 1 Poin**: Berapa rupiah nilai tukar 1 poin? (default: Rp 1.000)
   - **Minimal Tukar**: Minimal berapa poin bisa ditukar? (default: 10 poin)
   - **Masa Berlaku**: Berapa hari poin kadaluarsa? (default: 365 hari)
4. Klik **Save**

### 2️⃣ Daftar Member Baru

1. Buka menu **Customer** → **Member**
2. Klik tombol **Create**
3. Isi form:
   - **Nama Lengkap** ✅ (wajib)
   - **No. Telepon** ✅ (wajib, harus unik)
   - Email, Alamat, Tanggal Lahir (opsional)
   - **Tier**: Pilih Bronze (default) atau manual
4. Klik **Create**
5. Kode member otomatis terbuat (contoh: MBR00001)

### 3️⃣ Transaksi dengan Member di POS

**Langkah-langkah:**

1. Buka **POS**
2. Tambah produk ke keranjang seperti biasa
3. Di bagian **Member** (atas nama customer):
   - Ketik **Kode Member** atau **No. Telepon**
   - Tekan **Enter** atau klik tombol **Search** 🔍
4. Jika member ditemukan, akan muncul:
   - ✅ Nama & Tier member
   - ✅ Total poin tersedia
   - ✅ Multiplier tier
5. **(Opsional)** Jika member ingin tukar poin:
   - Ketik jumlah poin di kolom **Tukar Poin**
   - Atau klik **Max** untuk gunakan maksimal
   - Diskon otomatis terhitung
6. Pilih **Metode Pembayaran**
7. Klik **Checkout**

**Hasil:**
- ✅ Member dapat poin baru sesuai tier multiplier
- ✅ Poin yang ditukar terpotong
- ✅ Tier otomatis upgrade jika memenuhi syarat
- ✅ Statistik member terupdate

---

## 💡 Tips & Trik

### 🎯 Tier Member

**4 Level Tier (Auto Upgrade):**

| Tier | Min. Total Belanja | Multiplier | Warna Badge |
|------|-------------------|-----------|-------------|
| 🥉 Bronze | Rp 0 | 1.0x | Orange |
| 🥈 Silver | Rp 1.000.000 | 1.2x | Gray |
| 🥇 Gold | Rp 5.000.000 | 1.5x | Yellow |
| 💎 Platinum | Rp 10.000.000 | 2.0x | Blue |

**Contoh:**
- Member Bronze belanja total Rp 5.500.000
- Otomatis upgrade ke Gold ✨
- Transaksi berikutnya dapat poin 1.5x lebih banyak!

### 📊 Contoh Perhitungan

**Setting:** Rp 10.000 = 1 poin, 1 poin = Rp 1.000

**Belanja Rp 100.000:**
- Bronze: 10 poin (100.000 / 10.000 × 1.0)
- Silver: 12 poin (100.000 / 10.000 × 1.2)
- Gold: 15 poin (100.000 / 10.000 × 1.5)
- Platinum: 20 poin (100.000 / 10.000 × 2.0)

**Tukar 50 Poin:**
- Diskon: Rp 50.000
- Total Rp 150.000 → Bayar Rp 100.000 saja!

### 🔍 Pencarian Member

**3 Cara Cari Member:**
1. Ketik **Kode Member** (contoh: MBR00001)
2. Ketik **No. Telepon** (contoh: 081234567890)
3. Tekan **Enter** untuk quick search

### 🎁 Strategi Promosi

**Ide Campaign:**

1. **Welcome Bonus**
   - Member baru dapat 10 poin gratis
   - Edit manual di admin panel

2. **Double Points Weekend**
   - Ubah multiplier sementara (contoh: Bronze jadi 2.0x)
   - Jangan lupa kembalikan setelah event

3. **Tier Challenge**
   - "Belanja Rp 5 juta upgrade Gold!"
   - "Platinum dapat poin 2x lipat!"

4. **Birthday Special**
   - Beri bonus poin manual di hari ulang tahun
   - Check tanggal lahir member di admin

5. **Loyalty Reward**
   - Member dengan transaksi terbanyak
   - Beri bonus poin sebagai apresiasi

---

## ⚠️ Troubleshooting

### ❌ Member tidak ditemukan
- ✅ Pastikan ketik nomor telepon atau kode dengan benar
- ✅ Check di admin: member status **Aktif**
- ✅ Check di admin: nomor telepon sudah terdaftar

### ❌ Tidak bisa tukar poin
**Kemungkinan penyebab:**
- Poin kurang dari minimal (default: 10 poin)
- Poin tidak cukup
- Total belanja lebih kecil dari nilai poin
- Member tidak aktif

### ❌ Poin tidak bertambah setelah transaksi
- ✅ Pastikan member dipilih sebelum checkout
- ✅ Check pengaturan poin sudah diatur
- ✅ Check log error di Admin

### ❌ Tier tidak auto-upgrade
- ✅ Check di **Pengaturan Poin**: "Auto Upgrade Tier" aktif
- ✅ Check total belanja sudah memenuhi minimal
- ✅ Coba transaksi lagi untuk trigger update

---

## 📱 Data Sample

**Untuk testing, sudah ada 5 member sample:**

```
1. MBR00001 - Budi Santoso
   📞 081234567890
   🥇 Gold | 150 poin

2. MBR00002 - Siti Nurhaliza
   📞 081234567891
   💎 Platinum | 500 poin

3. MBR00003 - Ahmad Hidayat
   📞 081234567892
   🥈 Silver | 50 poin

4. MBR00004 - Dewi Lestari
   📞 081234567893
   🥉 Bronze | 20 poin

5. MBR00005 - Rudi Hartono
   📞 081234567894
   🥈 Silver | 75 poin
```

**Coba transaksi dengan member sample untuk testing!**

---

## 🎓 Best Practices

### ✅ DO's
- ✅ Selalu informasikan customer tentang poin yang didapat
- ✅ Ajak customer daftar member untuk dapat benefit
- ✅ Update pengaturan poin sesuai strategi bisnis
- ✅ Monitor statistik member untuk campaign
- ✅ Backup data secara berkala

### ❌ DON'Ts
- ❌ Jangan hapus member yang sudah ada transaksi
- ❌ Jangan ubah pengaturan poin tanpa komunikasi ke customer
- ❌ Jangan lupa pilih member sebelum checkout
- ❌ Jangan beri poin manual tanpa alasan jelas

---

## 📞 Butuh Bantuan?

**Kontak Support:**
- Check dokumentasi lengkap: `MEMBER_POINTS_DOCUMENTATION.md`
- Check error logs: `storage/logs/`
- Database admin: PhpMyAdmin / TablePlus

---

**🎉 Selamat menggunakan sistem Member & Poin!**

Semoga meningkatkan loyalitas pelanggan dan omzet toko Anda! 💪🚀
