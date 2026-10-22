🍞 Dapur Produksi

Aplikasi Web Pre-Order, Perencanaan Produksi, dan Manajemen Stok Bahan Baku

Dapur Produksi adalah aplikasi web untuk membantu usaha makanan dan minuman mengelola pesanan pelanggan, merencanakan produksi, serta memantau persediaan bahan baku dalam satu sistem terintegrasi.

Aplikasi ini dibangun menggunakan Laravel 13, PHP, dan MySQL, dengan antarmuka berbasis Blade tanpa proses build JavaScript tambahan. Dirancang agar mudah dijalankan secara lokal maupun di shared hosting yang mendukung persyaratan teknis aplikasi.

⸻

📋 Daftar Isi

* Fitur Utama
* Otomatisasi Sistem
* Hak Akses Pengguna
* Teknologi
* Persyaratan Sistem
* Instalasi Lokal
* Akun Default
* Deployment ke Hosting
* Arsitektur Aplikasi
* Panduan Pengembangan
* Pengujian
* Batasan Sistem

⸻

✨ Fitur Utama

Modul	Deskripsi
Dashboard	Menampilkan pesanan yang jatuh tempo dalam 7 hari ke depan, jadwal produksi, kebutuhan pembelian bahan, dan batch yang mendekati kedaluwarsa.
Pre-Order	Mencatat pesanan pelanggan, uang muka (DP), sisa pembayaran, serta status pesanan.
Rencana Produksi	Menggabungkan beberapa pre-order dan produksi tambahan, kemudian menghitung kebutuhan bahan berdasarkan resep.
Manajemen Stok	Mengelola stok bahan baku per batch, tanggal kedaluwarsa, stok minimum, serta riwayat stok masuk dan keluar.
Produk dan Resep	Mengelola produk dan resep yang digunakan untuk menghitung kebutuhan bahan baku produksi.
Supplier	Menyimpan dan mengelola informasi pemasok bahan baku.
Manajemen Pengguna	Mengatur akun pengguna sesuai peran dan hak akses.
Riwayat Aktivitas	Mencatat perubahan dan aktivitas pengguna di dalam aplikasi.
Data Terhapus	Memungkinkan admin memulihkan data yang telah dihapus sesuai dukungan sistem.
Ekspor CSV	Mengekspor data pre-order, stok, dan riwayat stok untuk diolah menggunakan aplikasi spreadsheet seperti Microsoft Excel.

⚙️ Otomatisasi Sistem

Dapur Produksi menerapkan sejumlah aturan bisnis untuk menjaga konsistensi data dan proses produksi.

* Otomatisasi status pesanan: pre-order terkait berubah menjadi Diproduksi ketika produksi dimulai dan Selesai ketika produksi berhasil diselesaikan.
* Pemotongan stok otomatis: bahan baku dikurangi berdasarkan resep ketika produksi diselesaikan.
* FEFO (First Expired, First Out): batch dengan tanggal kedaluwarsa paling dekat digunakan terlebih dahulu.
* Pencegahan penggunaan bahan kedaluwarsa: batch yang sudah kedaluwarsa tidak digunakan dalam produksi.
* Validasi ketersediaan stok: proses penyelesaian produksi ditolak jika ada bahan yang tidak mencukupi, sehingga perubahan stok tidak diterapkan sebagian.
* Pencatatan aktivitas: perubahan data dicatat dalam riwayat aktivitas untuk membantu pengawasan.
* Pemulihan data: data yang dihapus dapat dipulihkan oleh admin jika didukung oleh mekanisme penghapusan aplikasi.

Alur Status Pre-Order

Draft
  ↓
Dikonfirmasi
  ↓
Diproduksi
  ↓
Selesai
  ↓
Dikirim

Pesanan juga dapat dibatalkan sesuai aturan status yang berlaku di aplikasi.

⸻

👥 Hak Akses Pengguna

Aplikasi menyediakan tiga level akun dengan hak akses berbeda.

Peran	Hak Akses
Admin	Memiliki akses penuh, termasuk pengelolaan akun, produk, resep, bahan baku, supplier, dan pemulihan data terhapus.
User (Staf)	Mengelola pre-order, rencana produksi, serta transaksi stok masuk dan keluar sesuai aturan aplikasi.
Direksi	Melihat data, melakukan ekspor, dan memeriksa riwayat aktivitas tanpa mengubah data.

Konfigurasi aturan peran dan hak akses utama berada di:

app/Domain/Auth/Role.php

⸻

🛠️ Teknologi

Teknologi	Kegunaan
PHP 8.3+	Bahasa pemrograman backend
Laravel 13	Framework aplikasi web
MySQL	Basis data relasional
Blade	Template antarmuka
HTML dan CSS	Struktur dan tampilan halaman
PHPUnit	Pengujian otomatis
Composer	Manajemen dependensi PHP

⸻

💻 Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan perangkat sudah memiliki:

* Windows, Linux, atau sistem operasi yang kompatibel.
* PHP 8.3 atau lebih baru dan ekstensi PHP yang dibutuhkan Laravel.
* Composer.
* MySQL.
* Git (opsional, untuk mengambil kode dari GitHub).
* Browser modern.

Untuk Windows, kamu dapat menggunakan Laragon sebagai lingkungan pengembangan lokal.

Catatan: Pastikan versi PHP, ekstensi, dan dependensi yang digunakan sesuai dengan persyaratan Laravel 13 dan file composer.json project.

⸻

🚀 Instalasi Lokal

1. Unduh Project

Jika menggunakan Git, jalankan:

git clone https://github.com/USERNAME/aplikasi-kp.git
cd aplikasi-kp

Ganti USERNAME/aplikasi-kp dengan alamat repository GitHub yang sebenarnya.

Jika menggunakan file ZIP, ekstrak project ke:

C:\laragon\www\dapur-produksi

2. Jalankan Laragon

Buka Laragon, kemudian klik Start All untuk menjalankan layanan yang diperlukan.

Buka Terminal Laragon dan masuk ke direktori project:

cd C:\laragon\www\dapur-produksi

3. Instal Dependensi

Jalankan perintah berikut:

composer install

Buat file konfigurasi lingkungan:

copy .env.example .env

Buat application key:

php artisan key:generate

4. Konfigurasi Database

Buka HeidiSQL atau phpMyAdmin, lalu buat database MySQL dengan nama:

CREATE DATABASE produksi_db;

Buka file .env dan sesuaikan konfigurasi database:

APP_NAME="Dapur Produksi"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=produksi_db
DB_USERNAME=root
DB_PASSWORD=

Sesuaikan nama pengguna dan kata sandi database dengan konfigurasi MySQL di komputer kamu.

5. Migrasi dan Data Awal

Untuk membuat tabel sekaligus mengisi data contoh:

php artisan migrate --seed

Jika ingin membuat database baru dengan hanya tiga akun awal tanpa data contoh lainnya, gunakan:

php artisan migrate:fresh --seeder=UserSeeder

Peringatan: migrate:fresh menghapus seluruh tabel yang ada di database yang dikonfigurasi. Gunakan hanya pada database pengembangan yang aman untuk dihapus.

6. Jalankan Aplikasi

php artisan serve

Buka browser dan akses:

http://localhost:8000

⸻

🔐 Akun Default

Jika seeder bawaan membuat akun berikut, kamu dapat menggunakannya untuk pengujian awal.

Peran	Email	Kata Sandi
Admin	admin@example.com	password123
User	user@example.com	password123
Direksi	direksi@example.com	password123

Keamanan akun:

* Segera ganti semua kata sandi default.
* Gunakan alamat email yang benar untuk setiap akun.
* Jangan gunakan kata sandi default pada server produksi.
* Jangan mengunggah file .env yang berisi kredensial ke repository publik.

⸻

🌐 Deployment ke Hosting

Aplikasi dapat dijalankan di shared hosting yang memenuhi kebutuhan PHP, database, dan ekstensi Laravel.

1. Persiapan Lokal

Di komputer pengembangan, jalankan:

composer install --no-dev --optimize-autoloader

Pastikan aplikasi sudah diuji sebelum diunggah ke hosting.

2. Konfigurasi Hosting

Di panel hosting:

1. Pilih versi PHP yang kompatibel dengan project.
2. Buat database MySQL beserta pengguna database.
3. Berikan hak akses database yang diperlukan.
4. Catat nama database, username, dan password.

3. Upload File

Upload seluruh kode aplikasi, termasuk folder vendor, ke direktori yang tidak dapat diakses langsung dari publik.

Contoh struktur:

/home/namaakun/
├── dapur-produksi/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   └── .env
│
└── public_html/

4. Atur Document Root

Metode yang disarankan: arahkan document root domain atau subdomain ke folder public milik Laravel, misalnya:

/home/namaakun/dapur-produksi/public

Jika hosting tidak mendukung perubahan document root, konfigurasi alternatif harus memastikan hanya isi folder public yang dapat diakses pengunjung. Sesuaikan referensi path di index.php dengan lokasi aplikasi yang sebenarnya.

Jangan memindahkan seluruh folder aplikasi ke direktori publik karena dapat mengekspos file konfigurasi dan kode internal.

5. Konfigurasi .env Produksi

Buat file .env di direktori utama aplikasi pada server:

APP_NAME="Dapur Produksi"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainanda.com
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=kata_sandi_database

Ganti nilai contoh dengan informasi yang diberikan oleh penyedia hosting.

Gunakan APP_KEY yang valid untuk aplikasi tersebut. Jika aplikasi sudah memiliki data terenkripsi, jangan membuat ulang key tanpa memahami dampaknya.

6. Migrasi Database

Jika hosting menyediakan Terminal atau SSH, jalankan dari direktori utama aplikasi:

php artisan migrate --force

Jika database produksi memang perlu diisi dengan data awal, jalankan seeder yang sesuai secara terpisah:

php artisan db:seed --class=UserSeeder --force

Pastikan seeder tidak membuat ulang akun default atau menimpa data yang sudah ada secara tidak diinginkan.

7. Optimasi Aplikasi

Setelah konfigurasi produksi benar, jalankan:

php artisan config:cache
php artisan route:cache
php artisan view:cache

Pastikan direktori berikut dapat ditulisi oleh proses PHP:

storage/
bootstrap/cache/

Gunakan permission yang sesuai dengan konfigurasi server dan hindari memberikan izin tulis berlebihan.

8. Pemeriksaan Setelah Deployment

* Pastikan halaman login dapat diakses.
* Uji login untuk setiap peran.
* Periksa koneksi database.
* Uji pembuatan pre-order dan rencana produksi.
* Pastikan stok dan status pesanan berubah sesuai aturan.
* Periksa ekspor CSV dan riwayat aktivitas.
* Pastikan APP_DEBUG=false.
* Ganti kredensial default dan pastikan data sensitif tidak dapat diakses publik.

Hosting tanpa Terminal/SSH: migrasi database dapat dilakukan secara lokal menggunakan skema dan seeder yang sesuai, kemudian database diekspor dan diimpor melalui phpMyAdmin. Periksa kembali konfigurasi dan kompatibilitas database sebelum digunakan.

⸻

🏗️ Arsitektur Aplikasi

Project menggunakan pendekatan Clean Architecture untuk memisahkan aturan bisnis dari framework, penyimpanan data, dan antarmuka pengguna.

app/
├── Domain/
│   ├── Auth/
│   ├── PreOrder/
│   └── ...
│
├── Application/
│   └── UseCases/
│
├── Infrastructure/
│   ├── Persistence/
│   │   └── Repositories/
│   ├── Queries/
│   ├── Services/
│   └── Providers/
│
└── Http/
    └── Controllers/
resources/
└── views/
public/
└── css/

Struktur di atas merupakan gambaran lapisan utama; nama subfolder dan kelas mengikuti implementasi aktual di repository.

Lapisan	Tanggung Jawab	Contoh
Domain	Aturan bisnis, entitas, dan abstraksi yang tidak bergantung pada Laravel.	PreOrder.php, StockAllocator.php, Role.php
Application	Mengatur alur use case dan koordinasi operasi bisnis.	CompleteProduction.php, CreatePreOrder.php, IssueStock.php
Infrastructure	Implementasi database, repository, query, dan ekspor.	Eloquent*Repository.php, CsvExporter.php
Presentation	Menerima request, menjalankan validasi, dan menampilkan antarmuka.	Controller dan Blade Views

Penghubung implementasi infrastruktur dengan abstraksi aplikasi berada di:

app/Infrastructure/Providers/InfrastructureServiceProvider.php

Secara umum, operasi yang mengubah data dijalankan melalui use case agar aturan bisnis dapat diterapkan secara konsisten. Operasi pembacaan menggunakan kelas query yang sesuai.

⸻

🔧 Panduan Pengembangan

Gunakan tabel berikut untuk menemukan bagian kode yang perlu disesuaikan.

Kebutuhan	Lokasi
Hak akses dan peran pengguna	app/Domain/Auth/Role.php
Status pre-order	app/Domain/PreOrder/PreOrderStatus.php
Peringatan kedaluwarsa bahan	app/Infrastructure/Queries/InventoryQuery.php
Satuan bahan baku	RawMaterialController::UNITS
Satuan produk	ProductController::UNITS
Warna dan tampilan	public/css/app.css
Nama aplikasi	Variabel APP_NAME di .env
Pesan validasi	lang/id/validation.php

Beberapa lokasi di atas merujuk pada kelas atau konfigurasi tertentu. Pastikan nama dan path sesuai dengan kode aktual sebelum melakukan perubahan.

⸻

🧪 Pengujian

Untuk menjalankan seluruh pengujian otomatis, gunakan:

vendor/bin/phpunit

Pengujian mencakup aturan bisnis yang dijelaskan dalam project, antara lain:

* Pengalokasian stok menggunakan FEFO.
* Alur status pre-order.
* Validasi pembayaran uang muka.
* Perhitungan kebutuhan bahan baku.
* Hak akses pengguna.
* Alur penyelesaian produksi.

Pengujian menggunakan database sementara di memori jika konfigurasi test project sudah mengaturnya. Pastikan konfigurasi tersebut benar sebelum menjalankan tes agar database utama tidak terdampak.

⸻

⚠️ Batasan Sistem

Hal-hal berikut perlu diperhatikan dalam penggunaan aplikasi:

1. Perhitungan ketersediaan antar-rencana produksi: stok yang ditampilkan belum memperhitungkan alokasi bahan untuk rencana lain yang belum selesai. Beberapa rencana dapat sama-sama menampilkan status bahan cukup. Stok aktual tetap diperiksa kembali saat produksi diselesaikan.
2. Konsistensi satuan: satuan bahan pada resep harus sama dengan satuan stok bahan. Contohnya, jika stok tepung menggunakan kilogram, takaran resep juga menggunakan kilogram.
3. Koreksi stok batch: jumlah stok batch yang sudah terpakai tidak dapat diedit langsung. Gunakan fitur Catat Stok Keluar → Koreksi Stok untuk menyesuaikan hasil pemeriksaan fisik.
4. Modul yang belum tersedia: aplikasi belum mencakup modul pembayaran atau kasir, penjualan langsung non-pre-order, maupun manajemen stok produk jadi.

⸻

🔒 Keamanan

Untuk menjaga keamanan aplikasi:

* Simpan .env di luar direktori publik.
* Jangan mengunggah password, application key, atau kredensial database ke GitHub.
* Nonaktifkan debug pada lingkungan produksi.
* Gunakan kata sandi yang kuat dan berbeda untuk setiap akun.
* Batasi hak akses berdasarkan peran pengguna.
* Lakukan backup database secara berkala.
* Uji proses pemulihan backup sebelum mengandalkannya.

⸻

📌 Status Project

Nama aplikasi: Dapur Produksi
Jenis aplikasi: Web Application
Bidang: Manajemen pre-order, perencanaan produksi, dan inventaris bahan baku
Framework: Laravel 13
Database: MySQL

⸻

Dapur Produksi — membantu usaha makanan dan minuman mengelola pesanan, produksi, dan persediaan bahan baku secara terintegrasi.
