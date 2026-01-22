# Panduan Migrasi DB ke Schema Indonesia (produk_satuan multi-unit)

Dokumen ini adalah panduan implementasi untuk mengganti schema database pada project **pos-kasir-pam-techno** menjadi schema Indonesia (rename tabel + kolom + PK/FK) sambil mempertahankan fungsionalitas web.

> Keputusan final dari diskusi:
> - **Auth tetap pakai email** (jadi tabel `users` harus tetap punya `email`, `remember_token`, `email_verified_at`).
> - Project ini pakai schema Indonesia untuk DB, tapi ada **compatibility layer** di beberapa area admin agar UI tetap berjalan.
> - Produk mendukung **multi-satuan** (pcs/pack/dus, dll) melalui tabel `produk_satuan`.
> - `produk.stok` adalah stok **satuan dasar** (misal pcs), dan penjualan satuan lain akan mengurangi stok via konversi.

## Catatan implementasi (per 22 Jan 2026)

- Migration `produk_satuan` yang dipakai tinggal 1 file: `2026_01_01_000002_z_create_produk_satuan_table.php` (file duplikat yang dulu “disabled” sudah dihapus).
- Admin Products: backend menerima payload key Indonesia **dan** legacy (mis. `nama_produk`/`name`, `harga_jual`/`price`) agar UI tidak perlu diubah.
- Reports: filter & output menggunakan istilah **payment_method** untuk `transaksi.metode_pembayaran` (legacy `payment_type` masih bisa diterima sebagai fallback di server untuk kompatibilitas).

---

## 1) Tujuan

1. Mengganti schema DB menjadi:
   - `produk`, `produk_satuan`, `tag`, `produk_tag`, `diskon`, `diskon_produk`, `diskon_tag`, `transaksi`, `detail_transaksi`.
2. Menjaga fungsionalitas:
   - Admin: CRUD produk/tag/diskon, laporan/statistik.
   - Kasir: pencarian produk, pilih satuan (pcs/pack/dus), checkout, pengurangan stok, diskon.
   - Auth: login/register tetap berjalan via email.

---

## 2) Skema Final (DDL)

### 2.1. Catatan kompatibilitas Laravel auth

Walaupun target schema `users` kamu hanya menyebut `nama/username/password/role`, untuk kompatibilitas auth Laravel yang sudah ada, pertahankan kolom berikut:

- `email` (UNIQUE)
- `email_verified_at` (nullable)
- `remember_token` (nullable)

Jika kolom ini dihapus, kamu harus refactor auth total (login via username, perubahan view + controller + config auth), dan itu **bukan** scope dokumen ini.

### 2.2. DDL (versi rekomendasi)

> Kamu boleh copy ini ke SQL migration manual jika ingin, namun implementasi terbaik di Laravel adalah membuat **migrations**.

```sql
-- ======================================================
-- TABLE: users (kompat Laravel auth)
-- ======================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ======================================================
-- TABLE: produk
-- stok adalah stok satuan dasar (misal pcs)
-- ======================================================
CREATE TABLE produk (
    id_produk BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    stok INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ======================================================
-- TABLE: produk_satuan
-- multi unit: pcs/pack/dus
-- jumlah_per_satuan = konversi ke stok dasar (pcs)
-- ======================================================
CREATE TABLE produk_satuan (
    id_satuan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_produk BIGINT UNSIGNED NOT NULL,
    nama_satuan VARCHAR(100) NOT NULL,
    jumlah_per_satuan INT UNSIGNED NOT NULL,
    harga_pokok BIGINT UNSIGNED NOT NULL,
    harga_jual BIGINT UNSIGNED NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_produk_satuan_produk
        FOREIGN KEY (id_produk)
        REFERENCES produk(id_produk)
        ON DELETE CASCADE
);

-- ======================================================
-- TABLE: tag
-- ======================================================
CREATE TABLE tag (
    id_tag BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_tag VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ======================================================
-- TABLE: produk_tag (pivot)
-- ======================================================
CREATE TABLE produk_tag (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_produk BIGINT UNSIGNED NOT NULL,
    id_tag BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY uniq_produk_tag (id_produk, id_tag),

    CONSTRAINT fk_produk_tag_produk
        FOREIGN KEY (id_produk)
        REFERENCES produk(id_produk)
        ON DELETE CASCADE,

    CONSTRAINT fk_produk_tag_tag
        FOREIGN KEY (id_tag)
        REFERENCES tag(id_tag)
        ON DELETE CASCADE
);

-- ======================================================
-- TABLE: diskon
-- ======================================================
CREATE TABLE diskon (
    id_diskon BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_diskon VARCHAR(255) NOT NULL,
    tipe_diskon ENUM('persen','nominal') NOT NULL,
    nilai_diskon INT UNSIGNED NOT NULL,
    target ENUM('produk','tag') NOT NULL,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    auto_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ======================================================
-- TABLE: diskon_produk
-- ======================================================
CREATE TABLE diskon_produk (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_diskon BIGINT UNSIGNED NOT NULL,
    id_produk BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY uniq_diskon_produk (id_diskon, id_produk),

    CONSTRAINT fk_diskon_produk_diskon
        FOREIGN KEY (id_diskon)
        REFERENCES diskon(id_diskon)
        ON DELETE CASCADE,

    CONSTRAINT fk_diskon_produk_produk
        FOREIGN KEY (id_produk)
        REFERENCES produk(id_produk)
        ON DELETE CASCADE
);

-- ======================================================
-- TABLE: diskon_tag
-- ======================================================
CREATE TABLE diskon_tag (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_diskon BIGINT UNSIGNED NOT NULL,
    id_tag BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY uniq_diskon_tag (id_diskon, id_tag),

    CONSTRAINT fk_diskon_tag_diskon
        FOREIGN KEY (id_diskon)
        REFERENCES diskon(id_diskon)
        ON DELETE CASCADE,

    CONSTRAINT fk_diskon_tag_tag
        FOREIGN KEY (id_tag)
        REFERENCES tag(id_tag)
        ON DELETE CASCADE
);

-- ======================================================
-- TABLE: transaksi
-- Catatan penting: untuk histori, pertimbangkan RESTRICT/SET NULL
-- dibanding CASCADE pada id_user.
-- ======================================================
CREATE TABLE transaksi (
    id_transaksi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_transaksi VARCHAR(255) NOT NULL,
    id_user BIGINT UNSIGNED NOT NULL,
    jenis_transaksi ENUM('eceran','grosir') NOT NULL DEFAULT 'eceran',
    metode_pembayaran ENUM('tunai','kartu','qris','ewallet') NOT NULL DEFAULT 'tunai',
    total_belanja BIGINT UNSIGNED NOT NULL,
    diskon BIGINT UNSIGNED NOT NULL DEFAULT 0,
    total_transaksi BIGINT UNSIGNED NOT NULL,
    jumlah_dibayar BIGINT UNSIGNED NOT NULL,
    kembalian BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_transaksi_user
        FOREIGN KEY (id_user)
        REFERENCES users(id)
        ON DELETE RESTRICT
);

-- ======================================================
-- TABLE: detail_transaksi
-- Snapshot harga disimpan di sini agar histori stabil
-- ======================================================
CREATE TABLE detail_transaksi (
    id_detail_transaksi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_transaksi BIGINT UNSIGNED NOT NULL,
    id_produk BIGINT UNSIGNED NOT NULL,
    id_satuan BIGINT UNSIGNED NOT NULL,
    nama_produk VARCHAR(255) NOT NULL,
    nama_satuan VARCHAR(100) NOT NULL,
    jumlah_per_satuan INT UNSIGNED NOT NULL,
    jumlah INT UNSIGNED NOT NULL,
    harga_pokok BIGINT UNSIGNED NOT NULL,
    harga_jual BIGINT UNSIGNED NOT NULL,
    subtotal BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_detail_transaksi_transaksi
        FOREIGN KEY (id_transaksi)
        REFERENCES transaksi(id_transaksi)
        ON DELETE CASCADE,

    CONSTRAINT fk_detail_transaksi_produk
        FOREIGN KEY (id_produk)
        REFERENCES produk(id_produk)
        ON DELETE RESTRICT,

    CONSTRAINT fk_detail_transaksi_satuan
        FOREIGN KEY (id_satuan)
        REFERENCES produk_satuan(id_satuan)
        ON DELETE RESTRICT
);
```

> Catatan: DDL kamu sebelumnya sudah benar secara sintaks. Perubahan utama di atas adalah:
> - Menambahkan kolom `users.email`, `email_verified_at`, `remember_token`.
> - Mengganti tipe uang menjadi `BIGINT UNSIGNED` (lebih aman untuk rupiah).
> - Menambahkan snapshot `nama_satuan` dan `jumlah_per_satuan` di `detail_transaksi` (rekomendasi kuat).
> - Mengubah `ON DELETE` pada `transaksi.id_user` menjadi `RESTRICT` demi histori.

---

## 3) Aturan Bisnis (wajib konsisten di backend + frontend)

### 3.1. Stok

- `produk.stok` = stok dalam **satuan dasar** (contoh: pcs).
- Saat penjualan 1 item:
  - user memilih `id_satuan`.
  - kurangi stok: `stok -= jumlah * jumlah_per_satuan`.
- Validasi stok:
  - tidak boleh negatif.

### 3.2. Default satuan

- Setiap produk harus punya minimal 1 `produk_satuan` aktif.
- Tepat 1 baris `produk_satuan` per produk yang `is_default = 1`.

### 3.3. Snapshot harga

Saat transaksi dibuat, `detail_transaksi` harus menyimpan snapshot:
- `nama_produk`, `nama_satuan`
- `jumlah_per_satuan`
- `harga_pokok`, `harga_jual`

Agar histori transaksi tidak berubah saat harga di master berubah.

---

## 4) Mapping Lama → Baru (tabel & kolom)

> Project saat ini memakai schema konvensi Laravel (English). Berikut mapping yang harus diikuti saat refactor.

### 4.1. Tabel

| Lama | Baru |
|---|---|
| `products` | `produk` |
| `transaction_items` | `detail_transaksi` |
| `transactions` | `transaksi` |
| `discounts` | `diskon` |
| `tags` | `tag` |
| `product_tag` | `produk_tag` |
| `discount_product` | `diskon_produk` |
| `discount_tag` | `diskon_tag` |
| (tidak ada) | `produk_satuan` |

### 4.2. Kolom inti (contoh penting)

**products → produk**
- `products.id` → `produk.id_produk`
- `products.name` → `produk.nama_produk`
- `products.image` → `produk.gambar`
- `products.stock` → `produk.stok`
- `products.is_active` → `produk.is_active`

**Harga (pindah ke produk_satuan)**
- `products.price` → `produk_satuan.harga_jual` untuk satuan default
- `products.cost_price` → `produk_satuan.harga_pokok` untuk satuan default
- Field grosir lama (mis. `wholesale_*`) → dibuatkan 1 atau lebih baris `produk_satuan` (pack/dus dst) dengan `jumlah_per_satuan` sesuai konversi

**transactions → transaksi**
- `transactions.id` → `transaksi.id_transaksi`
- `transactions.transaction_number` → `transaksi.nomor_transaksi`
- `transactions.user_id` → `transaksi.id_user`
- `transactions.subtotal` → `transaksi.total_belanja`
- `transactions.discount_amount` → `transaksi.diskon`
- `transactions.total` → `transaksi.total_transaksi`
- `transactions.amount_received` → `transaksi.jumlah_dibayar`
- `transactions.change` → `transaksi.kembalian`
- `transactions.payment_type` → `transaksi.jenis_transaksi`
- `transactions.payment_method` → `transaksi.metode_pembayaran`

**transaction_items → detail_transaksi**
- `transaction_items.id` → `detail_transaksi.id_detail_transaksi`
- `transaction_items.transaction_id` → `detail_transaksi.id_transaksi`
- `transaction_items.product_id` → `detail_transaksi.id_produk`
- `transaction_items.product_name` → `detail_transaksi.nama_produk`
- `transaction_items.qty` → `detail_transaksi.jumlah`
- `transaction_items.price` → `detail_transaksi.harga_jual`
- `transaction_items.subtotal` → `detail_transaksi.subtotal`
- NEW: `detail_transaksi.id_satuan`, `nama_satuan`, `jumlah_per_satuan`, `harga_pokok`

**pivots**
- `product_tag.product_id/tag_id` → `produk_tag.id_produk/id_tag`
- `discount_product.discount_id/product_id` → `diskon_produk.id_diskon/id_produk`
- `discount_tag.discount_id/tag_id` → `diskon_tag.id_diskon/id_tag`

---

## 5) Daftar Pekerjaan Kode (file yang perlu diubah)

> Prinsip: pindahkan schema di backend terlebih dulu, lalu rapikan output/payload bertahap.
> Di project ini, beberapa endpoint admin sengaja mempertahankan key legacy (compatibility layer) untuk menghindari perubahan UI.

### 5.1. Models (Eloquent)

Update minimal:
- `app/Models/Product.php` → arahkan ke tabel `produk`, PK `id_produk`, relasi ke `produk_satuan`.
- `app/Models/Tag.php` → tabel `tag`, PK `id_tag`.
- `app/Models/Discount.php` → tabel `diskon`, PK `id_diskon`.
- `app/Models/Transaction.php` → tabel `transaksi`, PK `id_transaksi`.
- `app/Models/TransactionItem.php` → tabel `detail_transaksi`, PK `id_detail_transaksi`.
- Tambah model baru: `ProdukSatuan` (mis. `app/Models/ProdukSatuan.php`).

Yang harus kamu set di model-model di atas:
- `$table`
- `$primaryKey`
- relasi `hasMany/belongsTo/belongsToMany` dengan nama FK Indonesia

### 5.2. Services

- `app/Services/DiscountService.php`
  - Ganti semua referensi tabel pivot `product_tag` → `produk_tag`.
  - Ganti kolom `product_id/tag_id` → `id_produk/id_tag`.
  - Pastikan diskon target produk/tag tetap berjalan.

### 5.3. Controllers

Hotspot umum:
- Semua join/reporting yang memakai `DB::table('transactions')`, `transaction_items`, `products`, `tags`, `product_tag`, `discounts`.
- Semua endpoint kasir yang membaca `price/cost_price/wholesale_qty_per_unit` dari produk: pindahkan ke `produk_satuan`.

Lokasi yang biasanya perlu audit:
- `app/Http/Controllers/Admin/*`
- `app/Http/Controllers/Api/*`
- `app/Http/Controllers/AuthController.php` (pastikan email auth tetap)

Catatan khusus:
- Admin Products/Discounts: boleh return key “lama” untuk UI (mis. `name`, `price`, dst), tapi **wajib** write/read ke tabel Indonesia (`produk`, `produk_satuan`, `diskon`, dst).
- Reports: gunakan `payment_method` sebagai nama filter untuk `metode_pembayaran`.

### 5.4. Requests / Validation

Update semua rule `exists:*` dan `unique:*` agar sesuai tabel+PK baru.
Folder:
- `app/Http/Requests/*`

Contoh pola yang harus berubah:
- `exists:products,id` → `exists:produk,id_produk`
- `exists:tags,id` → `exists:tag,id_tag`

### 5.5. Resources (API JSON)

Jika kamu pakai `app/Http/Resources/*`, pastikan field output berubah sesuai field Indonesia:
- `id_produk`, `nama_produk`, `stok`, `gambar`
- `satuan[]`: daftar `produk_satuan` untuk UI kasir

### 5.6. Front-end (JS/Blade)

- `public/js/kasir.js`
  - Update agar produk memiliki daftar satuan.
  - Payload item transaksi harus mengirim `id_satuan`.
  - Kalkulasi subtotal memakai `harga_jual` dari satuan terpilih.

- `resources/views/*`
  - Semua tempat yang menampilkan field produk/tag/diskon/transaksi harus diubah ke nama baru.

---

## 6) Strategi Migrasi (pilih salah satu)

### Opsi A — Fresh start (paling cepat, data hilang)

1. Backup DB.
2. Buat migration schema baru.
3. `php artisan migrate:fresh --seed`
4. Pastikan semua seeders disesuaikan dengan schema baru.

### Opsi B — Preserve data (disarankan)

1. Buat schema baru berdampingan (tabel baru dengan nama Indonesia).
2. Backfill data dari tabel lama → tabel baru (sekali jalan).
3. Switch kode agar read/write ke tabel baru.
4. Jalankan verifikasi (ceklist di bawah).
5. Setelah stabil, decommission tabel lama.

---

## 7) Checklist Verifikasi (wajib sebelum go-live)

- Auth
  - Login via email berhasil
  - Register berhasil, email unique
  - Remember me bekerja

- Produk
  - Produk tampil
  - Bisa pilih satuan (pcs/pack/dus)
  - Harga sesuai satuan
  - Stok berkurang sesuai konversi

- Diskon
  - Diskon target produk bekerja
  - Diskon target tag bekerja
  - Persen/nominal konsisten

- Transaksi
  - Nomor transaksi terbentuk
  - Total belanja/diskon/total transaksi benar
  - Detail transaksi menyimpan snapshot (harga, satuan)

- Laporan
  - Laporan penjualan tidak error
  - Statistik profit (jika ada) tidak error

---

## 8) Catatan Risiko

- Rename kolom/PK/FK akan memecahkan:
  - join `DB::table()`
  - validation `exists/unique`
  - relasi pivot Eloquent
  - JS yang konsumsi JSON key lama

- `ON DELETE CASCADE` pada transaksi → user/produk berisiko menghapus histori. Gunakan `RESTRICT` untuk aman.

---

## 9) Next step

1. Putuskan strategi migrasi: **Fresh** atau **Preserve data**.
2. Setelah itu, eksekusi implementasi dimulai dari: migrations → models/relations → transaksi flow → discount service → admin reports → JS/Blade.
