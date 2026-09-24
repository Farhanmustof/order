# Dapur Produksi

Aplikasi web untuk usaha makanan/minuman: **pre-order pelanggan**, **rencana produksi**, dan **stok bahan baku** dalam satu tempat.

Dibangun dengan Laravel 13 (PHP) + MySQL, tanpa proses build JavaScript. Tinggal upload dan jalan, cocok untuk shared hosting.

---

## Isi aplikasi

| Menu | Fungsi |
|---|---|
| Beranda | Pesanan jatuh tempo 7 hari ke depan, produksi terjadwal, bahan yang perlu dibeli, batch yang hampir kedaluwarsa |
| Pre-order | Catat pesanan, DP, dan sisa bayar. Alur status: Draft → Dikonfirmasi → Diproduksi → Selesai → Dikirim (atau Dibatalkan) |
| Rencana produksi | Gabungkan beberapa pre-order + produksi tambahan, lalu cek kecukupan bahan secara otomatis |
| Stok bahan baku | Stok per batch dengan tanggal kedaluwarsa, stok minimum, stok masuk/keluar, riwayat |
| Produk & resep | Resep (takaran bahan per 1 produk) menjadi dasar hitung kebutuhan bahan |
| Supplier | Pemasok bahan baku |
| Pengguna, Riwayat aktivitas, Data terhapus | Pengawasan (khusus admin/direksi) |

**Yang terjadi otomatis:**
- Saat produksi **dimulai**, pre-order terkait berstatus *Diproduksi*.
- Saat produksi **selesai**, bahan baku dipotong dari stok sesuai resep, dan pre-order terkait berstatus *Selesai*.
- Stok diambil dengan aturan **FEFO** (*First Expired, First Out*): batch yang paling dekat kedaluwarsa dipakai lebih dulu. Batch yang sudah kedaluwarsa **tidak** dipakai untuk produksi.
- Jika satu bahan saja kurang, produksi ditolak dan stok tidak berubah sama sekali.
- Semua perubahan tercatat di *Riwayat aktivitas*. Data yang dihapus bisa dipulihkan admin.
- Ekspor ke Excel (CSV) untuk pre-order, stok, dan riwayat stok.

### Level akun

| Level | Bisa apa |
|---|---|
| **Admin** | Semua. Termasuk kelola akun, produk/resep, bahan baku, supplier, dan memulihkan data terhapus |
| **User (staf)** | Mencatat, mengubah, dan menghapus pre-order, rencana produksi, serta stok masuk/keluar |
| **Direksi** | Melihat semua data, ekspor, dan riwayat aktivitas, tanpa bisa mengubah apa pun |

Aturan ini ada di satu file: `app/Domain/Auth/Role.php`.

---

## Cara menjalankan di komputer sendiri (Windows)

### 1. Pasang Laragon
Unduh di https://laragon.org, lalu pasang. Laragon sudah berisi PHP, Composer, dan MySQL.

> Aplikasi ini butuh **PHP 8.3 atau lebih baru**. Setelah Laragon terpasang, buka *Terminal* di Laragon dan ketik `php -v`. Jika versinya di bawah 8.3, tambahkan PHP versi baru lewat menu **Laragon → PHP → Version**.

### 2. Siapkan project
1. Ekstrak zip ini ke `C:\laragon\www\dapur-produksi`.
2. Klik **Start All** di Laragon.
3. Klik **Terminal**, lalu jalankan:

```bash
cd C:\laragon\www\dapur-produksi
composer install
copy .env.example .env
php artisan key:generate
```

### 3. Buat database
1. Di Laragon klik **Database** (membuka HeidiSQL), lalu buat database baru bernama `produksi_db`.
2. Kembali ke terminal:

```bash
php artisan migrate --seed
php artisan serve
```

3. Buka **http://localhost:8000** di browser.

### Akun awal

| Level | Email | Kata sandi |
|---|---|---|
| Admin | admin@example.com | password123 |
| User | user@example.com | password123 |
| Direksi | direksi@example.com | password123 |

> ⚠️ **Segera ganti semua kata sandi** lewat menu *Akun saya* (atau admin lewat menu *Pengguna*), terutama sebelum web di-online-kan. Ubah juga email-nya ke email asli lewat menu *Pengguna*.

Database awal sudah berisi **data contoh usaha roti** (bahan, resep, pre-order, rencana produksi) supaya bisa langsung dicoba. Untuk mulai dari data kosong (hanya 3 akun):

```bash
php artisan migrate:fresh --seeder=UserSeeder
```

---

## Upload ke hosting

Hampir semua shared hosting Indonesia yang mendukung PHP 8.3 bisa dipakai (misalnya yang memakai cPanel atau hPanel). Paling mudah bila hosting menyediakan **Terminal/SSH**.

### Langkah umum
1. **Di komputer**, siapkan versi produksi:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. **Di panel hosting**:
   - Atur versi PHP ke **8.3 atau lebih baru**.
   - Buat database MySQL beserta user-nya. Catat nama database, user, dan kata sandinya.
3. **Upload** seluruh isi folder project (termasuk folder `vendor`) ke folder **di luar** `public_html`, misalnya `/home/namaakun/dapur-produksi`. Jangan ikut upload file `.env` dari komputer.
4. **Arahkan domain ke folder `public`**:
   - *Cara terbaik:* di pengaturan domain/subdomain, ubah *Document Root* menjadi `/home/namaakun/dapur-produksi/public`.
   - *Jika tidak bisa diubah:* salin **isi** folder `public` ke `public_html`, lalu edit `public_html/index.php` dan ganti setiap `__DIR__.'/../` menjadi `__DIR__.'/../dapur-produksi/`.
5. **Buat file `.env`** di folder project (salin dari `.env.example`), lalu ubah:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domainanda.com

   DB_DATABASE=nama_database_dari_hosting
   DB_USERNAME=user_database_dari_hosting
   DB_PASSWORD=kata_sandi_database
   ```
6. **Jalankan di Terminal hosting** (dari folder project):
   ```bash
   php artisan key:generate --force
   php artisan migrate --force --seed --seeder=UserSeeder
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
7. Pastikan folder `storage` dan `bootstrap/cache` bisa ditulisi (permission 775).
8. Buka domain Anda, login sebagai admin, lalu **ganti kata sandi semua akun**.

**Hosting tanpa Terminal?** Jalankan `php artisan migrate --seed --seeder=UserSeeder` di komputer sendiri, ekspor databasenya (HeidiSQL → *Export database as SQL*), lalu impor file SQL tersebut lewat phpMyAdmin di hosting. Untuk `APP_KEY`, salin nilainya dari `.env` di komputer Anda.

**Setelah mengubah `.env` di server**, jalankan lagi `php artisan config:cache`.

---

## Struktur kode (clean architecture)

Kode dibagi menjadi empat lapisan. Aturan utamanya: **lapisan dalam tidak boleh tahu lapisan luar**.

```
          ┌──────────────────────────────────────────────┐
          │  Presentation   app/Http, resources/views    │  halaman, form, tombol
          │  Infrastructure app/Infrastructure           │  database (Eloquent), log, CSV
          │   ┌────────────────────────────────────────┐ │
          │   │ Application   app/Application          │ │  use case: "Selesaikan produksi"
          │   │   ┌──────────────────────────────────┐ │ │
          │   │   │ Domain   app/Domain              │ │ │  aturan bisnis murni (PHP biasa)
          │   │   └──────────────────────────────────┘ │ │
          │   └────────────────────────────────────────┘ │
          └──────────────────────────────────────────────┘
```

| Lapisan | Isi | Contoh |
|---|---|---|
| **Domain** | Entitas, aturan bisnis, dan *interface* repository. Tidak memakai Laravel sama sekali. | `PreOrder.php` (alur status, validasi DP), `StockAllocator.php` (FEFO), `Role.php` (hak akses) |
| **Application** | *Use case*: urutan langkah satu aksi pengguna, dijalankan dalam satu transaksi, lalu dicatat ke riwayat. | `CompleteProduction.php`, `CreatePreOrder.php`, `IssueStock.php` |
| **Infrastructure** | Cara teknis menyimpan dan membaca data. Mengimplementasikan interface dari Domain/Application. | `Persistence/Repositories/Eloquent*Repository.php`, `Queries/*Query.php`, `Services/CsvExporter.php` |
| **Presentation** | Controller (menerima request, validasi format, memanggil use case) dan tampilan Blade. | `app/Http/Controllers`, `resources/views` |

Penghubung interface ke implementasinya ada di `app/Infrastructure/Providers/InfrastructureServiceProvider.php`.

Sisi *baca* (daftar, filter, dasbor) memakai kelas `Queries` langsung, karena tidak mengubah data. Sisi *tulis* selalu lewat use case agar aturan bisnis tidak bisa dilewati.

### Mau mengubah sesuatu? Buka file ini

| Kebutuhan | File |
|---|---|
| Hak akses tiap level akun | `app/Domain/Auth/Role.php` |
| Alur/label status pre-order | `app/Domain/PreOrder/PreOrderStatus.php` |
| Batas peringatan kedaluwarsa (default 14 hari) | `app/Infrastructure/Queries/InventoryQuery.php` (`EXPIRY_WARNING_DAYS`) |
| Pilihan satuan bahan/produk | `RawMaterialController::UNITS`, `ProductController::UNITS` |
| Warna dan tampilan | `public/css/app.css` (variabel di bagian `:root`) |
| Nama aplikasi | `APP_NAME` di file `.env` |
| Pesan validasi | `lang/id/validation.php` |

---

## Menjalankan tes otomatis

```bash
vendor/bin/phpunit
```

Tes mencakup aturan domain (FEFO, alur status, DP, perhitungan kebutuhan bahan), hak akses tiap level, dan alur produksi lengkap. Tes memakai database sementara di memori, jadi data asli aman.

---

## Batasan yang perlu diketahui

- **Stok "tersedia" di rencana produksi belum dikurangi rencana lain yang belum selesai.** Jika dua rencana sama-sama butuh tepung, keduanya bisa tampil "cukup". Pengaman tetap ada: saat ditandai selesai, sistem mengecek ulang stok sebenarnya.
- **Satuan di resep harus sama dengan satuan stok bahan.** Jika tepung dicatat dalam kg, takaran resep juga dalam kg (misalnya 0.15, bukan 150 gram).
- Jumlah stok sebuah batch yang sudah terpakai tidak bisa diedit. Untuk koreksi hasil hitung fisik, gunakan **Catat stok keluar → Koreksi stok**.
- Belum ada modul pembayaran/kasir, penjualan langsung (non pre-order), atau stok produk jadi.
#   a p l i k a s i - k p  
 #   a p l i k a s i - k p  
 