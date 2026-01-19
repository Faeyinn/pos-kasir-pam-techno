# 📦 POS Kasir Pam Techno - Project Documentation

## 📋 Project Overview

**Nama Proyek:** POS Kasir Pam Techno  
**Tipe:** Web-based Point of Sale System  
**Target:** Minimarket & Kafe  
**Platform:** Tablet-optimized (Responsive)  
**Pendekatan:** Minimum Viable Product (MVP)

### Tujuan Sistem
Sistem POS yang dirancang untuk mempermudah proses transaksi penjualan sekaligus membantu pemilik usaha dalam memantau dan merencanakan strategi bisnis melalui data penjualan.

### Fokus Utama
- ✅ Kemudahan penggunaan
- ✅ Kejelasan informasi
- ✅ Efisiensi operasional
- ✅ Proses transaksi yang cepat dan sederhana

---

## 🛠️ Technology Stack

### Backend
- **Framework:** Laravel 12
- **PHP Version:** 8.2+
- **Database:** MySQL/SQLite
- **ORM:** Eloquent
- **Authentication:** Laravel Session-based Auth

### Frontend
- **Template Engine:** Blade Templates
- **JavaScript Framework:** Alpine.js 3.x
- **CSS Framework:** Tailwind CSS 4.0
- **Icons:** Lucide Icons
- **Build Tool:** Vite 7.0

### Additional Libraries
- **Barcode Scanner:** HTML5-QRCode
- **HTTP Client:** Axios 1.11.0
- **Fonts:** Google Fonts (Inter)

---

## 👥 User Roles & Access Control

### 1. **Kasir (Cashier)**
- Akses halaman kasir untuk transaksi
- Dapat melihat produk dan stok
- Melakukan transaksi penjualan
- Mencetak struk
- Melihat riwayat transaksi

### 2. **Admin (Owner)**
- Akses dashboard admin
- Melihat laporan penjualan
- Manajemen produk
- Manajemen user
- Visualisasi data grafik

### 3. **Master (Super Admin)**
- Dapat switch antara role Kasir dan Admin
- Full access ke semua fitur
- Role selection page untuk memilih mode kerja

### Default User Credentials

```
Master Account:
Email: masterpam@gmail.com
Password: masterspirit45

Admin Account:
Email: adminpam@gmail.com
Password: adminspirit45

Kasir Account:
Email: kasirpam@gmail.com
Password: kasirspirit45

Kasir (Jaeyi):
Email: jaeyi@gmail.com
Password: jaeyispirit45
```

---

## 🗄️ Database Schema

### **Users Table**
```sql
- id (bigint, primary key)
- name (string)
- username (string, unique)
- email (string, unique)
- password (string, hashed)
- role (string: 'kasir', 'admin', 'master')
- email_verified_at (timestamp, nullable)
- remember_token (string, nullable)
- created_at, updated_at (timestamps)
```

### **Products Table**
```sql
- id (bigint, primary key)
- name (string)
- image (string, nullable)
- price (integer) -- harga retail dalam rupiah
- wholesale (integer, default: 0) -- harga grosir per unit
- wholesale_unit (string, nullable) -- satuan grosir (Dus, Karung, Pack)
- wholesale_qty_per_unit (integer, default: 1) -- jumlah pcs per unit grosir
- stock (integer, default: 0) -- stok dalam pcs
- is_active (boolean, default: true)
- tags (json, nullable) -- array tags untuk filtering
- created_at, updated_at (timestamps)
```

**Contoh Data Produk:**
```json
{
  "name": "Aqua 600ml",
  "price": 3500,
  "wholesale": 36000,
  "wholesale_unit": "Dus",
  "wholesale_qty_per_unit": 12,
  "stock": 120,
  "tags": ["Botol", "Minuman"]
}
```

### **Transactions Table**
```sql
- id (bigint, primary key)
- transaction_number (string, unique) -- Format: TRX-YYYYMMDD-XXXX
- user_id (foreign key -> users.id, cascade on delete)
- payment_type (enum: 'retail', 'wholesale')
- payment_method (enum: 'tunai', 'kartu', 'qris', 'ewallet')
- subtotal (integer)
- total (integer)
- amount_received (integer)
- change (integer)
- created_at, updated_at (timestamps)
```

### **Transaction Items Table**
```sql
- id (bigint, primary key)
- transaction_id (foreign key -> transactions.id, cascade on delete)
- product_id (foreign key -> products.id, cascade on delete)
- product_name (string) -- snapshot nama produk saat transaksi
- qty (integer) -- jumlah unit yang dibeli
- price (integer) -- harga per unit saat transaksi
- subtotal (integer) -- qty * price
- created_at, updated_at (timestamps)
```

---

## 📁 Project Structure

```
pos-kasir-pam-techno/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── AdminController.php
│   │   │   └── Api/
│   │   │       ├── ProductController.php
│   │   │       └── TransactionController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Transaction.php
│   │   └── TransactionItem.php
│   └── Providers/
│
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_01_11_153557_create_products_table.php
│   │   ├── 2026_01_11_153609_create_transactions_table.php
│   │   └── 2026_01_11_153704_create_transaction_items_table.php
│   └── seeders/
│       ├── UserSeeder.php
│       └── ProductSeeder.php
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php (Kasir Layout)
│   │   │   └── admin.blade.php (Admin Layout)
│   │   ├── pages/
│   │   │   ├── kasir.blade.php
│   │   │   ├── login.blade.php
│   │   │   ├── register.blade.php
│   │   │   ├── profile.blade.php
│   │   │   ├── role-selection.blade.php
│   │   │   └── admin/
│   │   │       ├── dashboard.blade.php
│   │   │       ├── products.blade.php
│   │   │       └── users.blade.php
│   │   └── components/
│   │       ├── header.blade.php
│   │       ├── sidebar.blade.php
│   │       └── kasir/
│   │           ├── search-bar.blade.php
│   │           ├── product-grid.blade.php
│   │           ├── product-card.blade.php
│   │           ├── cart-sidebar.blade.php
│   │           ├── cart-item.blade.php
│   │           ├── payment-modal.blade.php
│   │           ├── receipt-modal.blade.php
│   │           ├── history-modal.blade.php
│   │           ├── scanner-modal.blade.php
│   │           ├── confirm-clear-modal.blade.php
│   │           └── toast.blade.php
│   │
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── kasir/
│   │       ├── index.js
│   │       └── modules/
│   │           ├── notifications.js
│   │           ├── products.js
│   │           ├── cart.js
│   │           ├── transactions.js
│   │           └── print.js
│   │
│   └── css/
│       └── app.css
│
├── routes/
│   ├── web.php
│   └── console.php
│
├── public/
│   ├── js/
│   │   └── kasir.js (Compiled Alpine.js modules)
│   └── assets/
│
├── composer.json
├── package.json
└── vite.config.js
```

---

## 🔐 Authentication & Authorization

### Middleware: CheckRole

**File:** `app/Http/Middleware/CheckRole.php`

**Logika:**
1. Cek apakah user sudah login
2. Jika user adalah **Master**:
   - Cek session `active_role`
   - Jika belum ada, redirect ke role selection
   - Validasi akses berdasarkan `active_role`
3. Jika user adalah **Admin** atau **Kasir**:
   - Validasi akses berdasarkan role langsung

### Route Protection

```php
// Public Routes
Route::get('/', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);

// Authenticated Routes
Route::middleware('auth')->group(function () {
    
    // Role Selection (Master only)
    Route::get('/role-selection', [AdminController::class, 'roleSelection']);
    Route::post('/role-selection', [AdminController::class, 'setRole']);
    
    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/products', [AdminController::class, 'products']);
        Route::get('/users', [AdminController::class, 'users']);
    });
    
    // Kasir Routes
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir', fn() => view('pages.kasir'));
        
        // API Routes
        Route::prefix('api')->group(function () {
            Route::get('/products', [ProductController::class, 'index']);
            Route::get('/products/{id}', [ProductController::class, 'show']);
            Route::get('/transactions', [TransactionController::class, 'index']);
            Route::post('/transactions', [TransactionController::class, 'store']);
            Route::get('/transactions/{id}', [TransactionController::class, 'show']);
        });
    });
    
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

---

## 🎯 Core Features

### 1. **Sistem Kasir (Cashier System)**

#### A. Product Display & Search
- **Grid View:** Menampilkan produk dalam grid card
- **Search:** Pencarian berdasarkan nama produk atau tags
- **Tag Filtering:** Filter produk berdasarkan multiple tags
- **Popular Tags:** Menampilkan 5 tags paling populer
- **Stock Info:** Menampilkan informasi stok real-time
- **Wholesale Badge:** Indikator produk yang support harga grosir

#### B. Shopping Cart
- **Add to Cart:** Tambah produk dengan validasi stok
- **Update Quantity:** Increase/decrease qty dengan tombol +/-
- **Remove Item:** Hapus item dari keranjang
- **Clear Cart:** Kosongkan seluruh keranjang (dengan konfirmasi)
- **Stock Validation:** Real-time validation stok sebelum add/update
- **Price Calculation:** 
  - Retail: Harga normal per pcs
  - Wholesale: Harga grosir jika qty >= wholesale_qty_per_unit
- **Mobile Cart:** Floating button untuk akses cart di mobile

#### C. Payment System
- **Payment Methods:**
  - Tunai (Cash)
  - Kartu (Card)
  - QRIS
  - E-Wallet
- **Payment Type:**
  - Retail (Eceran)
  - Wholesale (Grosir/Partai)
- **Amount Validation:** Validasi uang diterima >= total
- **Change Calculation:** Auto-calculate kembalian
- **Quick Amount Buttons:** Tombol cepat untuk nominal uang pas

#### D. Transaction Processing
1. User pilih produk → Add to cart
2. Review cart → Adjust qty jika perlu
3. Klik "Bayar" → Payment modal muncul
4. Input amount received → Pilih payment method
5. Konfirmasi → Backend process:
   - Validate request data
   - Begin database transaction
   - Create transaction record
   - Create transaction items
   - Update product stock
   - Commit transaction
6. Show receipt modal
7. Option to print receipt
8. Clear cart & refresh products

#### E. Receipt/Struk
- **Format:** Thermal receipt 80mm
- **Content:**
  - Header: Nama toko, alamat
  - Transaction info: No, tanggal, waktu, kasir
  - Payment info: Tipe, metode pembayaran
  - Items: Nama, qty, harga, subtotal
  - Totals: Total, bayar, kembalian
  - Grand total highlight
  - Footer: Terima kasih, contact info
- **Print Function:** Window.print() untuk thermal printer
- **Reprint:** Bisa reprint dari transaction history

#### F. Transaction History
- **List View:** Semua transaksi dengan info lengkap
- **Detail View:** Klik untuk lihat detail transaksi
- **Reprint:** Reprint struk dari history
- **Filter:** (Future: by date, cashier, payment method)

#### G. Barcode Scanner
- **Technology:** HTML5-QRCode
- **Camera Access:** Request camera permission
- **Scan Feedback:** Audio beep saat sukses
- **Auto Add:** Produk otomatis masuk cart setelah scan
- **Error Handling:** Notifikasi jika produk tidak ditemukan

### 2. **Admin Panel**

#### A. Dashboard (Placeholder)
- Welcome message
- (Future: Sales statistics, charts, KPIs)

#### B. Products Management (Placeholder)
- (Future: CRUD products, stock management, categories)

#### C. Users Management (Placeholder)
- (Future: CRUD users, role assignment)

#### D. Reports (Future)
- Sales reports
- Profit analysis
- Product performance
- Excel export

---

## 🔄 Data Flow

### Product Fetching Flow
```
Frontend (Alpine.js) → API GET /api/products
                    ← JSON response with products
Store in Alpine state → Filter & display in grid
```

### Transaction Flow
```
1. User adds products to cart (Frontend state)
2. User clicks "Bayar" → Payment modal
3. User inputs amount & selects method
4. Frontend validates amount >= total
5. Frontend POST /api/transactions with:
   {
     payment_type: "retail" | "wholesale",
     payment_method: "tunai" | "kartu" | "qris" | "ewallet",
     amount_received: number,
     items: [
       { product_id, qty, price }
     ]
   }
6. Backend (TransactionController):
   - Validate request
   - Begin DB transaction
   - Calculate totals
   - Create Transaction record
   - For each item:
     * Get product
     * Calculate qty to deduct (consider wholesale)
     * Check stock availability
     * Create TransactionItem
     * Update product stock
   - Commit DB transaction
   - Return transaction with items
7. Frontend receives response:
   - Store receipt data
   - Show receipt modal
   - Clear cart
   - Refresh products
   - Add to history
```

### Stock Management Flow
```
Product Stock (in pcs)
├─ Retail Sale: stock -= qty
└─ Wholesale Sale: stock -= (qty * wholesale_qty_per_unit)

Example:
Product: Aqua 600ml
- Stock: 120 pcs
- Wholesale: 12 pcs per Dus

Retail Sale (5 pcs):
  stock = 120 - 5 = 115 pcs

Wholesale Sale (2 Dus):
  stock = 120 - (2 * 12) = 96 pcs
```

---

## 🎨 Frontend Architecture

### Alpine.js Module System

**Main Component:** `kasirSystem`

**Modules:**
1. **notifications.js** - Toast notification system
2. **products.js** - Product fetching & filtering
3. **cart.js** - Cart management & calculations
4. **transactions.js** - Payment & transaction history
5. **print.js** - Receipt printing

**State Management:**
```javascript
{
  // Products
  products: [],
  loading: false,
  searchQuery: "",
  selectedTags: [],
  
  // Cart
  cart: [],
  paymentType: "retail",
  
  // Modals
  showPaymentModal: false,
  showReceiptModal: false,
  showHistoryModal: false,
  showClearCartModal: false,
  mobileCartOpen: false,
  
  // Data
  receiptData: null,
  transactionHistory: [],
  notifications: []
}
```

**Computed Properties:**
- `filteredProducts` - Filter by search & tags
- `uniqueTags` - All unique tags from products
- `popularTags` - Top 5 most used tags
- `cartTotal` - Total harga keranjang
- `canApplyWholesale` - Apakah ada produk wholesale di cart

**Methods:**
- `fetchProducts()` - Load products from API
- `addToCart(product)` - Add product with stock validation
- `updateQty(productId, delta)` - Update item quantity
- `removeFromCart(productId)` - Remove item
- `clearCart()` - Clear all items
- `getItemPrice(item)` - Get price (retail/wholesale)
- `isWholesale(item)` - Check if item qualifies for wholesale
- `confirmPayment()` - Process payment
- `printReceipt()` - Print thermal receipt
- `fetchTransactionHistory()` - Load history
- `viewTransactionDetail(transaction)` - View detail
- `handleBarcodeScan(code)` - Handle barcode scan

---

## 🎨 UI/UX Design

### Design Principles
- **Tablet-First:** Optimized for tablet usage
- **Clean & Simple:** Minimal clutter, focus on functionality
- **Fast Interaction:** Quick add to cart, fast checkout
- **Clear Feedback:** Toast notifications for all actions
- **Touch-Friendly:** Large buttons, adequate spacing

### Color Scheme
- **Primary:** Blue (#3B82F6) - Retail mode
- **Secondary:** Purple (#9333EA) - Wholesale mode
- **Success:** Green (#10B981)
- **Error:** Red (#EF4444)
- **Background:** Slate (#F8FAFC)
- **Text:** Slate (#1E293B)

### Typography
- **Font Family:** Inter (Google Fonts)
- **Weights:** 400 (Regular), 500 (Medium), 600 (Semibold), 700 (Bold)

### Components
- **Cards:** Rounded corners, subtle shadows
- **Buttons:** Rounded, with hover/active states
- **Modals:** Backdrop blur, smooth transitions
- **Inputs:** Clean borders, focus states
- **Icons:** Lucide icons throughout

### Responsive Breakpoints
- **Mobile:** < 768px
- **Tablet:** 768px - 1024px (Primary target)
- **Desktop:** > 1024px

---

## 🔧 API Endpoints

### Products API

#### GET `/api/products`
**Description:** Get all active products

**Query Parameters:**
- `category` (optional) - Filter by category
- `search` (optional) - Search by name

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Aqua 600ml",
      "image": null,
      "price": 3500,
      "wholesale": 36000,
      "wholesale_unit": "Dus",
      "wholesale_qty_per_unit": 12,
      "stock": 120,
      "tags": ["Botol", "Minuman"],
      "is_active": true,
      "created_at": "2026-01-17T10:00:00.000000Z",
      "updated_at": "2026-01-17T10:00:00.000000Z"
    }
  ]
}
```

#### GET `/api/products/{id}`
**Description:** Get single product by ID

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Aqua 600ml",
    ...
  }
}
```

### Transactions API

#### GET `/api/transactions`
**Description:** Get all transactions with items

**Query Parameters:**
- `user_id` (optional) - Filter by cashier
- `date` (optional) - Filter by date

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "transaction_number": "TRX-20260117-0001",
      "user_id": 1,
      "payment_type": "retail",
      "payment_method": "tunai",
      "subtotal": 10500,
      "total": 10500,
      "amount_received": 15000,
      "change": 4500,
      "created_at": "2026-01-17T10:30:00.000000Z",
      "user": {
        "id": 1,
        "name": "Kasir Staff",
        "email": "kasirpam@gmail.com"
      },
      "items": [
        {
          "id": 1,
          "transaction_id": 1,
          "product_id": 1,
          "product_name": "Aqua 600ml",
          "qty": 3,
          "price": 3500,
          "subtotal": 10500
        }
      ]
    }
  ]
}
```

#### POST `/api/transactions`
**Description:** Create new transaction

**Request Body:**
```json
{
  "payment_type": "retail",
  "payment_method": "tunai",
  "amount_received": 15000,
  "items": [
    {
      "product_id": 1,
      "qty": 3,
      "price": 3500
    }
  ]
}
```

**Validation Rules:**
- `payment_type`: required, in:retail,wholesale
- `payment_method`: required, in:tunai,kartu,qris,ewallet
- `amount_received`: required, integer, min:0
- `items`: required, array, min:1
- `items.*.product_id`: required, exists:products,id
- `items.*.qty`: required, integer, min:1
- `items.*.price`: required, integer, min:0

**Response (Success):**
```json
{
  "success": true,
  "message": "Transaksi berhasil",
  "data": {
    "id": 1,
    "transaction_number": "TRX-20260117-0001",
    ...
    "user": {...},
    "items": [...]
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Stok Aqua 600ml tidak mencukupi"
}
```

#### GET `/api/transactions/{id}`
**Description:** Get single transaction with items

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "transaction_number": "TRX-20260117-0001",
    ...
    "user": {...},
    "items": [...]
  }
}
```

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & npm
- MySQL/MariaDB or SQLite
- Web server (Apache/Nginx) or Laravel Artisan

### Installation Steps

1. **Clone Repository**
```bash
git clone <repository-url>
cd pos-kasir-pam-techno
```

2. **Install PHP Dependencies**
```bash
composer install
```

3. **Install Node Dependencies**
```bash
npm install
```

4. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure Database**
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_kasir
DB_USERNAME=root
DB_PASSWORD=
```

6. **Run Migrations**
```bash
php artisan migrate
```

7. **Seed Database**
```bash
php artisan db:seed
```

8. **Build Assets**
```bash
npm run build
# or for development
npm run dev
```

9. **Start Development Server**
```bash
php artisan serve
```

10. **Access Application**
- URL: `http://localhost:8000`
- Login with default credentials (see User Roles section)

### Development Mode

Run all services concurrently:
```bash
composer run dev
```

This will start:
- Laravel server (port 8000)
- Queue listener
- Vite dev server (hot reload)

---

## 📝 Business Logic

### Pricing Logic

#### Retail Pricing
- Harga per pcs sesuai field `price`
- Tidak ada minimum qty
- Contoh: Aqua 600ml = Rp 3.500/pcs

#### Wholesale Pricing
- Harga per unit grosir sesuai field `wholesale`
- Minimum qty = `wholesale_qty_per_unit`
- Harga per pcs = `wholesale / wholesale_qty_per_unit`
- Contoh: Aqua 600ml
  - Wholesale: Rp 36.000/Dus
  - Qty per Dus: 12 pcs
  - Harga per pcs: Rp 3.000/pcs
  - Hemat: Rp 500/pcs (14% discount)

#### Auto Wholesale Detection
Sistem otomatis menerapkan harga grosir jika:
```javascript
item.wholesale > 0 && 
item.wholesaleQtyPerUnit > 0 && 
item.qty >= item.wholesaleQtyPerUnit
```

### Stock Management

#### Stock Calculation
```
Stock disimpan dalam satuan terkecil (pcs)

Retail Sale:
  stock_after = stock_before - qty

Wholesale Sale:
  qty_to_deduct = qty * wholesale_qty_per_unit
  stock_after = stock_before - qty_to_deduct
```

#### Stock Validation
- Validasi dilakukan saat add to cart
- Validasi dilakukan saat update qty
- Validasi dilakukan saat create transaction
- Error jika stok tidak mencukupi

### Transaction Number Generation

Format: `TRX-YYYYMMDD-XXXX`

```php
public static function generateTransactionNumber()
{
    return 'TRX-' . date('Ymd') . '-' . str_pad(
        self::whereDate('created_at', today())->count() + 1,
        4,
        '0',
        STR_PAD_LEFT
    );
}
```

Contoh:
- `TRX-20260117-0001` (Transaksi pertama hari ini)
- `TRX-20260117-0002` (Transaksi kedua hari ini)
- `TRX-20260118-0001` (Transaksi pertama besok)

---

## 🔒 Security Considerations

### Implemented
- ✅ CSRF Protection (Laravel default)
- ✅ Password Hashing (bcrypt)
- ✅ SQL Injection Protection (Eloquent ORM)
- ✅ XSS Protection (Blade escaping)
- ✅ Role-based Access Control
- ✅ Session Management
- ✅ Database Transactions (data integrity)

### Recommendations
- 🔲 Rate Limiting for API endpoints
- 🔲 Input Sanitization
- 🔲 File Upload Validation (for product images)
- 🔲 HTTPS in production
- 🔲 Environment variable protection
- 🔲 Regular security audits
- 🔲 Backup strategy

---

## 🧪 Testing

### Manual Testing Checklist

#### Authentication
- [ ] Login with valid credentials
- [ ] Login with invalid credentials
- [ ] Register new user
- [ ] Logout
- [ ] Role-based redirect
- [ ] Master role selection

#### Kasir - Products
- [ ] Load products
- [ ] Search products by name
- [ ] Filter by tags
- [ ] View product details
- [ ] Check stock display

#### Kasir - Cart
- [ ] Add product to cart
- [ ] Update quantity
- [ ] Remove from cart
- [ ] Clear cart
- [ ] Stock validation
- [ ] Price calculation (retail)
- [ ] Price calculation (wholesale)

#### Kasir - Transaction
- [ ] Open payment modal
- [ ] Select payment method
- [ ] Input amount received
- [ ] Validate insufficient amount
- [ ] Confirm payment
- [ ] View receipt
- [ ] Print receipt
- [ ] Transaction saved to database
- [ ] Stock updated correctly

#### Kasir - History
- [ ] View transaction history
- [ ] View transaction detail
- [ ] Reprint receipt

#### Kasir - Scanner
- [ ] Open scanner
- [ ] Scan barcode/QR
- [ ] Product added to cart
- [ ] Handle invalid code

#### Admin
- [ ] Access dashboard
- [ ] Access products page
- [ ] Access users page
- [ ] Switch to kasir (master)

---

## 📊 Sample Data

### Products (12 items)
1. Aqua 600ml - Rp 3.500 (Wholesale: Rp 36.000/Dus)
2. Indomie Goreng - Rp 3.500 (Wholesale: Rp 128.000/Dus)
3. Susu Ultra Milk 250ml - Rp 5.000 (Wholesale: Rp 108.000/Dus)
4. Teh Botol - Rp 4.000 (Wholesale: Rp 84.000/Dus)
5. Roti Tawar Sari Roti - Rp 15.000 (Wholesale: Rp 130.000/Pack)
6. Mie Sedaap Goreng - Rp 3.500 (Wholesale: Rp 128.000/Dus)
7. Kopi Kapal Api - Rp 2.500 (Wholesale: Rp 110.000/Dus)
8. Gula Pasir 1kg - Rp 18.000 (Wholesale: Rp 160.000/Karung)
9. Beras Premium 5kg - Rp 75.000 (Wholesale: Rp 350.000/Karung)
10. Minyak Goreng 2L - Rp 35.000 (Wholesale: Rp 192.000/Dus)
11. Sabun Lifebuoy - Rp 5.000 (Wholesale: Rp 108.000/Dus)
12. Shampoo Pantene 170ml - Rp 22.000 (Wholesale: Rp 240.000/Dus)

### Tags
- Botol, Minuman, Instan, Mie, Susu, Kotak
- Roti, Sarapan, Kopi, Bubuk, Pokok, Sembako
- Kebersihan, Perawatan

---

## 🚧 Future Enhancements

### Phase 2 Features
- [ ] Product CRUD in admin panel
- [ ] User CRUD in admin panel
- [ ] Product categories
- [ ] Product images upload
- [ ] Advanced filtering (by category, price range)
- [ ] Pagination for products
- [ ] Sales dashboard with charts
- [ ] Date range filter for reports
- [ ] Export to Excel/PDF
- [ ] Low stock alerts
- [ ] Product performance analytics

### Phase 3 Features
- [ ] Multi-store support
- [ ] Inventory management
- [ ] Purchase orders
- [ ] Supplier management
- [ ] Customer management
- [ ] Loyalty program
- [ ] Discount/Promo system
- [ ] Shift management
- [ ] Cash drawer tracking
- [ ] Email notifications
- [ ] SMS notifications
- [ ] Mobile app (React Native/Flutter)

### Technical Improvements
- [ ] API versioning
- [ ] Unit tests
- [ ] Integration tests
- [ ] API documentation (Swagger/OpenAPI)
- [ ] Caching (Redis)
- [ ] Queue jobs for heavy tasks
- [ ] Real-time updates (WebSockets/Pusher)
- [ ] Progressive Web App (PWA)
- [ ] Offline mode
- [ ] Docker containerization
- [ ] CI/CD pipeline

---

## 📞 Support & Contact

**Developer:** Pam Techno Team  
**Location:** Jalan Raya Gadut, Lubuk Kilangan, Padang, Sumatera Barat  
**Phone:** 0812-3456-7890

---

## 📄 License

This project is proprietary software developed for Pam Techno.

---

## 📝 Changelog

### Version 1.0.0 (2026-01-17)
- ✅ Initial release
- ✅ Authentication system (Login, Register, Logout)
- ✅ Role-based access control (Kasir, Admin, Master)
- ✅ Kasir interface with product grid
- ✅ Shopping cart with stock validation
- ✅ Transaction processing
- ✅ Receipt printing (thermal 80mm)
- ✅ Transaction history
- ✅ Barcode/QR scanner
- ✅ Tag-based filtering
- ✅ Wholesale pricing support
- ✅ Mobile responsive design
- ✅ Admin panel structure (placeholder)

---

**Last Updated:** 2026-01-17  
**Documentation Version:** 1.0.0
