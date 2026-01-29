# Detail Proyek: POS Kasir PAM Techno

Dokumen ini berisi informasi mendalam mengenai struktur, tumpukan teknologi, dan fitur yang ada dalam proyek Point of Sale (POS) ini.

## 🛠️ Tumpukan Teknologi (Tech Stack)

Proyek ini dibangun menggunakan teknologi modern berikut:
- **Backend:** Laravel 12.x (PHP 8.2+)
- **Frontend:** Alpine.js (diintegrasikan via Vite)
- **Styling:** Tailwind CSS 4.0
- **Database:** SQLite (Default untuk pengembangan)
- **Iconography:** Lucide Icons

## 📂 Struktur Direktori Utama

- `app/Models/`: Berisi entitas database.
- `app/Http/Controllers/`: Logika bisnis aplikasi, dibagi menjadi `Admin` dan `Api`.
- `database/migrations/`: Definisi skema database.
- `resources/views/`: File template UI (Blade).
- `routes/web/`: Pengelompokan rute (Admin, Kasir, Auth).
- `vite.config.js`: Konfigurasi build aset frontend.

## 💾 Model Database & Migrasi

Berikut adalah entitas utama dalam sistem ini:
1. **Product:** Mengelola data produk.
2. **ProdukSatuan:** Definisi satuan produk.
3. **Tag:** Kategori atau label untuk produk.
4. **Discount:** Logika diskon yang dapat diterapkan pada produk atau tag.
5. **Transaction & TransactionItem:** Mencatat riwayat penjualan dan item yang terjual.
6. **User:** Manajemen pengguna (Admin dan Kasir).

## 🚀 Fitur Utama

- **Manajemen Inventaris:** Pengaturan produk, satuan, dan kategori (tags).
- **Sistem Diskon Fleksibel:** Diskon dapat diterapkan pada produk spesifik atau seluruh kategori.
- **Transaksi Kasir:** Antarmuka untuk melakukan penjualan.
- **Panel Admin:** Dashboard untuk mengelola data master dan laporan.
- **Autentikasi:** Sistem login yang aman dengan pemisahan peran (Role-based).

## 💻 Instruksi Pengembangan

Untuk menjalankan proyek ini secara lokal:

1. **Instalasi:**
   ```bash
   composer install
   npm install
   ```

2. **Setup Lingkungan:**
   Salin `.env.example` ke `.env` dan jalankan:
   ```bash
   php artisan key:generate
   ```

3. **Migrasi Database:**
   ```bash
   php artisan migrate
   ```

4. **Menjalankan Server:**
   Gunakan perintah gabungan (menggunakan `concurrently`):
   ```bash
   composer dev
   ```
   *Perintah ini akan menjalankan server PHP, antrian (queue), dan Vite secara bersamaan.*

---
*Dibuat secara otomatis untuk memberikan gambaran umum teknis proyek.*
