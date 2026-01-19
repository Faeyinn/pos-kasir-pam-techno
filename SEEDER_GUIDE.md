# 📘 Panduan Penggunaan Transaction Seeder

## 🎯 Tujuan
Seeder ini membuat data transaksi dummy yang **sangat realistis** untuk testing dan demo halaman Laporan Admin.

## 📦 File Seeder

### 1. `TransactionSeeder.php`
Generate 300+ transaksi realistis dalam 30 hari terakhir.

### 2. `CleanTransactionsSeeder.php`  
Membersihkan semua data transaksi (untuk reset).

## 🚀 Cara Penggunaan

### Generate Data Transaksi
```bash
php artisan db:seed --class=TransactionSeeder
```

**Output yang diharapkan:**
```
INFO  Seeding database.

🚀 Mulai generate transaksi dummy...
✅ Generated 20 transactions...
✅ Generated 40 transactions...
...
✅ Generated 380 transactions...

🎉 Seeder selesai!
📊 Total transaksi: 382
💰 Total profit (estimasi): Rp 5.450.000

⚠️  Catatan: Stok produk TIDAK dikurangi oleh seeder ini.
    Jika ingin mengurangi stok, uncomment baris di seeder.
```

### Reset/Hapus Data Transaksi
```bash
php artisan db:seed --class=CleanTransactionsSeeder
```

**Output:**
```
⚠️  Menghapus semua data transaksi...
📊 Ditemukan 382 transaksi dengan 968 items.
✅ Semua data transaksi telah dihapus!
💡 Jalankan TransactionSeeder untuk generate data baru.
```

### Re-generate Data Baru
```bash
# 1. Clean
php artisan db:seed --class=CleanTransactionsSeeder

# 2. Re-seed
php artisan db:seed --class=TransactionSeeder
```

## 🎨 Karakteristik Data Yang Di-generate

### Pola Waktu ⏰
- **Periode**: 30 hari terakhir (dari hari ini mundur)
- **Jam Operasional**: 09:00 - 20:00
- **Hari Libur**: Tutup setiap Minggu
- **Jam Sibuk**: 10:00 - 16:00 (70% transaksi)
- **Jam Normal**: 09:00, 17:00-20:00 (30% transaksi)

### Pola Harian 📅
- **Weekday** (Senin-Jumat): 8-18 transaksi/hari
- **Weekend** (Sabtu): 15-25 transaksi/hari
- **Minggu**: TUTUP (0 transaksi)

### Metode Pembayaran 💳
Distribusi realistis dengan weighted random:
- **Tunai**: 60% (~230 transaksi)
- **Kartu**: 20% (~75 transaksi)
- **QRIS**: 15% (~57 transaksi)
- **E-wallet**: 5% (~20 transaksi)

### Tipe Transaksi 🛒
- **Retail** (Eceran): 90% (~345 transaksi)
- **Wholesale** (Grosir): 10% (~37 transaksi)

### Item per Transaksi 📦
- **Minimum**: 1 item
- **Maximum**: 4 items
- **Rata-rata**: 2-3 items
- **Total items**: ~970 items untuk 382 transaksi

### Qty per Item 🔢
#### Retail:
- 1 - 10 pcs (tergantung stok)

#### Wholesale:
- 1 - 3 units/dus (qty otomatis × qty_per_unit)
- Contoh: 2 dus snack @ 12 pcs = deduct 24 dari stok

### Harga & Kembalian 💰
#### Retail:
- Harga dari kolom `price` di tabel products

#### Wholesale:  
- Harga dari kolom `wholesale` di tabel products

#### Pembulatan (Tunai):
- Total: Rp 47.500
- Diterima: Rp 50.000 ✅
- Kembalian: Rp 2.500

#### Exact (Digital): 
- Total: Rp 47.500
- Diterima: Rp 47.500 ✅
- Kembalian: Rp 0

## 🔧 Konfigurasi Seeder

### File: `TransactionSeeder.php`

#### Ubah Jumlah Hari
```php
// Line ~48: Generate transactions for last 30 days
for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
```
Ganti `29` dengan jumlah hari yang diinginkan.

#### Ubah Jumlah Transaksi Per Hari
```php
// Line ~56-57
$dailyTransactionCount = $isWeekend ? rand(15, 25) : rand(8, 18);
```
- Weekend: 15-25 → ubah jadi `rand(20, 30)` untuk lebih ramai
- Weekday: 8-18 → ubah jadi `rand(10, 15)` untuk konsisten

#### Ubah Distribusi Metode Pembayaran
```php
// Line ~34-35
$paymentMethods = ['tunai', 'kartu', 'qris', 'ewallet'];
$paymentMethodWeights = [60, 20, 15, 5]; // Total harus 100
```

Contoh jika ingin QRIS lebih populer:
```php
$paymentMethodWeights = [40, 20, 30, 10]; // Tunai 40%, QRIS 30%
```

#### Aktifkan Pengurangan Stok
```php
// Line ~158 (uncomment line berikut)
$item['product']->decrement('stock', $item['stock_deduction']);
```

⚠️ **Warning**: Ini akan MENGURANGI stok produk secara permanen!

## 📊 Verifikasi Data

### Via SQL Query
```sql
-- Total transaksi
SELECT COUNT(*) as total_transactions FROM transactions;

-- Total penjualan
SELECT SUM(total) as total_sales FROM transactions;

-- Transaksi per metode pembayaran
SELECT 
    payment_method, 
    COUNT(*) as count,
    SUM(total) as total_sales,
    AVG(total) as avg_transaction
FROM transactions 
GROUP BY payment_method;

-- Transaksi per hari
SELECT 
    DATE(created_at) as date,
    DAYNAME(created_at) as day,
    COUNT(*) as transactions,
    SUM(total) as sales
FROM transactions 
GROUP BY DATE(created_at) 
ORDER BY date DESC;

-- Top 10 produk terlaris
SELECT 
    product_name,
    SUM(qty) as total_sold,
    SUM(subtotal) as revenue
FROM transaction_items 
GROUP BY product_name 
ORDER BY total_sold DESC 
LIMIT 10;
```

### Via Tinker
```bash
php artisan tinker
```

```php
// Total stats
echo "Transactions: " . \App\Models\Transaction::count() . "\n";
echo "Items Sold: " . \App\Models\TransactionItem::count() . "\n";

// Date range
echo "From: " . \App\Models\Transaction::min('created_at') . "\n";
echo "To: " . \App\Models\Transaction::max('created_at') . "\n";

// Total sales & profit
$total = \App\Models\Transaction::sum('total');
echo "Total Sales: Rp " . number_format($total, 0, ',', '.') . "\n";
```

## 🎯 Testing Laporan Page

Setelah running seeder:

1. **Buka browser**: `http://localhost:8000/admin/reports`

2. **Cek Summary Cards**:
   - Total Penjualan harus muncul (± Rp 15-25 juta)
   - Total Laba harus terisi (± Rp 3-7 juta)
   - Total Transaksi: 382
   - Rata-rata: ± Rp 40-70 ribu

3. **Cek Charts**:
   - Sales vs Profit: Line chart dengan data 30 hari
   - Profit by Tag: Donut chart dengan kategori
   - Transaction Trend: Spike di hari Sabtu

4. **Cek Table**:
   - 968 rows data (pagination 10/page)
   - Sortable columns berfungsi
   - Search berfungsi

5. **Test Filters**:
   - Ubah date range → data update
   - Pilih "Eceran" → data filtered
   - Pilih tag → data filtered by tag

6. **Test Export**:
   - Click "Excel" → CSV downloaded
   - Click "PDF" → Print dialog muncul

## ⚠️ Troubleshooting

### Error: "Tidak ada produk aktif"
```bash
# Run product seeder first
php artisan db:seed --class=ProductSeeder
```

### Error: "Tidak ada user"
```bash
# Run user seeder
php artisan db:seed --class=UserSeeder
```

### Stok produk 0 setelah seeding
- Seeder secara default TIDAK mengurangi stok
- Jika stok berkurang, cek apakah line decrement sudah di-uncomment

### Data tidak muncul di Laporan
1. Check browser console (F12) untuk error
2. Check Network tab → `/api/admin/reports/summary` response
3. Pastikan login sebagai Admin (role: admin)

## 📚 Dependencies

Seeder ini membutuhkan:
- ✅ Tabel `products` dengan data & `cost_price`
- ✅ Tabel `product_tag` (relasi products-tags)
- ✅ Tabel `tags` dengan data
- ✅ Tabel `users` dengan data
- ✅ `is_active = true` pada beberapa produk

## 🔄 Best Practices

### Development
```bash
# Fresh database + seed all
php artisan migrate:fresh --seed
```

### Testing Laporan
```bash
# Clean + Generate fresh data
php artisan db:seed --class=CleanTransactionsSeeder
php artisan db:seed --class=TransactionSeeder
```

### Production
⚠️ **JANGAN** run seeder di production!  
Data transaksi harus dari kasir real, bukan dari seeder.

---

**Happy Testing! 🎉**
