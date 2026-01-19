# Ringkasan Implementasi: Halaman Laporan Admin (Pam Techno POS)

Dokumen ini merangkum fitur teknis dan fungsional yang telah diimplementasikan untuk halaman Laporan Admin.

## 1. Ikhtisar Fungsional
Halaman Laporan dirancang untuk pemilik bisnis (Owner) guna menganalisis kinerja penjualan, laba, dan tren transaksi. Sistem ini mendukung filter mendalam dan ekspor data.

**Fitur Utama:**
- **Analisis Laba Rugi Real-time**: Menghitung laba bersih berdasarkan `(Harga Jual - Harga Modal) * Qty`.
- **Visualisasi Data**: Grafik interaktif untuk tren penjualan, distribusi laba per kategori/tag, dan tren transaksi.
- **Filter Komprehensif**: Rentang tanggal, tipe transaksi (Eceran/Grosir), dan filter Tag Produk.
- **Ekspor Data**: Mendukung unduhan **Excel (format CSV)** dan **Cetak PDF**.

## 2. Struktur Backend (LaporanController.php)

Controller baru dibuat untuk menangani logika pelaporan secara terpusat untuk menjaga performa dan keterbacaan kode.

### Endpoint API
| Method | Endpoint | Fungsi |
| :--- | :--- | :--- |
| `getSummary` | `/api/admin/reports/summary` | Mengambil statistik utama (Total Penjualan, Laba, Transaksi, Rata-rata). |
| `getCharts` | `/api/admin/reports/charts` | Mengambil data untuk grafik (Tren Harian, Laba per Tag, Tren Transaksi). |
| `getDetail` | `/api/admin/reports/detail` | Mengambil data tabel detail dengan pagination dan pencarian. |
| `exportCSV` | `/api/admin/reports/export/csv` | Menghasilkan file CSV yang difilter untuk diunduh (Streamed Response). |

### Logika Bisnis
- **Kalkulasi Laba**: Laba dihitung pada level *item transaksi* untuk akurasi tinggi, memperhitungkan harga modal saat transaksi terjadi (jika historis dicatat) atau harga modal saat ini.
- **Filter Tag**: Menggunakan `join` ke tabel `product_tag` untuk memfilter transaksi yang mengandung produk dengan tag tertentu.

## 3. Struktur Frontend (Blade & Alpine.js)

Tampilan dibangun menggunakan pendekatan modular dengan **Blade Components** dan **Alpine.js** untuk interaktivitas tanpa reload halaman (SPA-like feel).

### Komponen View
- **`pages/admin/reports.blade.php`**: Layout utama yang menghubungkan semua komponen.
- **`components/admin/report-filter.blade.php`**: Panel filter global (Date Range, Dropdown Tipe, Multi-select Tag).
- **`components/admin/report-summary.blade.php`**: Kartu statistik ringkasan (Cards).
- **`components/admin/report-charts.blade.php`**: Kontainer untuk grafik Chart.js.
- **`components/admin/report-table.blade.php`**: Tabel detail penjualan dengan fitur sorting dan pagination.

### Teknologi Frontend
- **Chart.js**: Digunakan untuk rendering grafik (Line Chart & Doughnut Chart).
- **Alpine.js**: Mengelola state aplikasi (filter, data loading, pagination) di sisi klien.
- **Tailwind CSS**: Styling responsif dan modern.
- **Lucide Icons**: Ikon vektor untuk antarmuka pengguna.
- **Print CSS**: Styling khusus `@media print` untuk menyembunyikan sidebar/navigasi saat mencetak laporan ke PDF.

## 4. Cara Penggunaan

1. **Akses Halaman**: Menu "Laporan" di sidebar admin.
2. **Filter Data**:
   - Pilih rentang tanggal (default: bulan ini).
   - Pilih tipe transaksi (Eceran/Grosir/Semua).
   - Filter berdasarkan Tag Produk (misal: "Makanan", "Minuman") untuk melihat performa kategori spesifik.
3. **Analisis**:
   - Lihat ringkasan angka di kartu atas.
   - Analisis tren kenaikan/penurunan di grafik.
   - Cek detail per item di tabel bawah.
4. **Ekspor**:
   - Klik **"Excel"** untuk mengunduh data mentah.
   - Klik **"PDF"** untuk mencetak tampilan laporan yang bersih (tanpa menu).
