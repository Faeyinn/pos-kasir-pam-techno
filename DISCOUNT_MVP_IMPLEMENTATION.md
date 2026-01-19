# 🎯 MVP Fitur Diskon - Implementation Guide

## ✅ Yang Sudah Selesai (Step 1-4)

### 1. Database ✅
- ✅ Migration `create_discounts_table.php`
- ✅ Migration `create_discount_product_table.php`
- ✅ Migration `create_discount_tag_table.php`
- ✅ Migration `add_discount_to_transactions_table.php`
- ✅ All migrations executed successfully

### 2. Models ✅
- ✅ `Discount.php` - dengan relationships & scopes
- ✅ Updated `Transaction.php` - added discount relationship

### 3. Business Logic (Service) ✅
- ✅ `DiscountService.php` - Complete business logic
  - `findApplicableDiscount()` - Find matching discount for cart
  - `calculateDiscountForCart()` - Calculate total discount
  - `isProductEligible()` - Check product/tag eligibility
  - `calculateDiscountValue()` - Calculate percentage/fixed value
  - `getDiscountForProduct()` - Get discount for display
  - `logDiscountApplication()` - Audit logging

### 4. Controller ✅  
- ✅ `DiscountController.php`
  - `index()` - Show discount page
  - `store()` - Create discount
  - `update()` - Update discount
  - `toggleStatus()` - Toggle active/inactive
  - `destroy()` - Delete discount

---

## 📋 Yang Perlu Dilanjutkan (Step 5-6)

### Step 5: Routes
Tambahkan ke `routes/web.php`:

```php
// Admin Discount Routes
Route::middleware(['role:admin'])->prefix('admin')->group(function () {
    Route::get('/discounts', [App\Http\Controllers\DiscountController::class, 'index'])->name('admin.discounts');
});

// Admin Discount API Routes
Route::prefix('api/admin')->middleware(['role:admin'])->group(function () {
    Route::post('/discounts', [App\Http\Controllers\DiscountController::class, 'store'])->name('api.admin.discounts.store');
    Route::put('/discounts/{id}', [App\Http\Controllers\DiscountController::class, 'update'])->name('api.admin.discounts.update');
    Route::post('/discounts/{id}/toggle', [App\Http\Controllers\DiscountController::class, 'toggleStatus'])->name('api.admin.discounts.toggle');
    Route::delete('/discounts/{id}', [App\Http\Controllers\DiscountController::class, 'destroy'])->name('api.admin.discounts.destroy');
});
```

### Step 6: Admin Views

#### 6.1 Main Page: `resources/views/pages/admin/discounts.blade.php`

Buat file dengan struktur:
```blade
@extends('layouts.admin')

@section('header', 'Manajemen Diskon')

@section('content')
<div x-data="discountManager" x-init="init" class="min-h-screen">
    {{-- Header with Add Button --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Diskon</h2>
            <p class="text-sm text-slate-600 mt-1">Kelola diskon produk dan kategori</p>
        </div>
        
        <button 
            @click="openModal('create')"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Diskon
        </button>
    </div>

    {{-- Discount Table Component --}}
    <x-admin.discount-table />

    {{-- Discount Modal Component --}}
    <x-admin.discount-modal :products="$products" :tags="$tags" />
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('discountManager', () => ({
            discounts: @json($discounts),
            products: @json($products),
            tags: @json($tags),
            
            modalMode: 'create', // 'create' or 'edit'
            showModal: false,
            
            formData: {
                id: null,
                name: '',
                type: 'percentage',
                value: 0,
                target_type: 'product',
                target_ids: [],
                start_date: '',
                end_date: '',
                is_active: true
            },

            init() {
                lucide.createIcons();
            },

            openModal(mode, discount = null) {
                this.modalMode = mode;
                
                if (mode === 'edit' && discount) {
                    this.formData = {
                        id: discount.id,
                        name: discount.name,
                        type: discount.type,
                        value: discount.value,
                        target_type: discount.target_type,
                        target_ids: discount.target_type === 'product' 
                            ? discount.products.map(p => p.id)
                            : discount.tags.map(t => t.id),
                        start_date: discount.start_date,
                        end_date: discount.end_date,
                        is_active: discount.is_active
                    };
                } else {
                    this.resetForm();
                }
                
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
                this.resetForm();
            },

            resetForm() {
                this.formData = {
                    id: null,
                    name: '',
                    type: 'percentage',
                    value: 0,
                    target_type: 'product',
                    target_ids: [],
                    start_date: '',
                    end_date: '',
                    is_active: true
                };
            },

            async saveDiscount() {
                const url = this.modalMode === 'create' 
                    ? '/api/admin/discounts'
                    : `/api/admin/discounts/${this.formData.id}`;
                
                const method = this.modalMode === 'create' ? 'POST' : 'PUT';

                try {
                    const res = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const data = await res.json();

                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Reload to refresh data
                    } else {
                        alert(data.message);
                    }
                } catch (e) {
                    alert('Error: ' + e.message);
                }
            },

            async toggleStatus(id) {
                try {
                    const res = await fetch(`/api/admin/discounts/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await res.json();
                    if (data.success) {
                        location.reload();
                    }
                } catch (e) {
                    alert('Error: ' + e.message);
                }
            },

            async deleteDiscount(id) {
                if (!confirm('Yakin ingin menghapus diskon ini?')) return;

                try {
                    const res = await fetch(`/api/admin/discounts/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await res.json();
                    alert(data.message);
                    
                    if (data.success) {
                        location.reload();
                    }
                } catch (e) {
                    alert('Error: ' + e.message);
                }
            },

            getTargetNames(discount) {
                if (discount.target_type === 'product') {
                    return discount.products.map(p => p.name).join(', ');
                } else {
                    return discount.tags.map(t => t.name).join(', ');
                }
            },

            formatValue(discount) {
                if (discount.type === 'percentage') {
                    return discount.value + '%';
                } else {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(discount.value);
                }
            }
        }));
    });
</script>
@endpush
```

#### 6.2 Table Component: `resources/views/components/admin/discount-table.blade.php`

```blade
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Nama Diskon
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Tipe
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Nilai
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Target
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Periode
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Status
                </th>
                <th class="text-right px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Aksi
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            <template x-for="discount in discounts" :key="discount.id">
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-900" x-text="discount.name"></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-slate-600" x-text="discount.type === 'percentage' ? 'Persentase' : 'Fixed'"></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-indigo-600" x-text="formatValue(discount)"></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600">
                            <span x-text="discount.target_type === 'product' ? 'Produk' : 'Tag'" class="font-medium"></span>:
                            <span x-text="getTargetNames(discount)" class="text-xs"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-slate-600">
                            <div x-text="discount.start_date"></div>
                            <div x-text="'s/d ' + discount.end_date"></div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <button 
                            @click="toggleStatus(discount.id)"
                            :class="discount.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'"
                            class="px-2 py-1 rounded-full text-xs font-medium"
                            x-text="discount.is_active ? 'Aktif' : 'Nonaktif'"
                        ></button>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button 
                                @click="openModal('edit', discount)"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                                title="Edit"
                            >
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <button 
                                @click="deleteDiscount(discount.id)"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                title="Hapus"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
            
            <tr x-show="discounts.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                    <p>Belum ada diskon</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

#### 6.3 Modal Component: `resources/views/components/admin/discount-modal.blade.php`

Buat modal form untuk create/edit discount (terlalu panjang untuk ditampilkan di sini, tapi struktur lengkap ada di file generation berikutnya).

---

## 🔌 Integration ke Kasir (Step 6 - PENTING!)

### Update TransactionController API

File: `app/Http/Controllers/Api/TransactionController.php`

Tambahkan di method `store()`:

```php
use App\Services\DiscountService;

public function store(Request $request)
{
    $validated = $request->validate([
        'payment_type' => 'required|in:retail,wholesale',
        'payment_method' => 'required|string',
        'items' => 'required|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.qty' => 'required|integer|min:1',
        'items.*.price' => 'required|integer|min:0',
    ]);

    DB::beginTransaction();
    try {
        // Calculate subtotal
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        // APPLY DISCOUNT (NEW!)
        $discountService = app(DiscountService::class);
        $cartItems = collect($validated['items']);
        $discountData = $discountService->findApplicableDiscount($cartItems);

        $discountAmount = $discountData['amount'];
        $discount = $discountData['discount'];

        // Calculate final total
        $total = $subtotal - $discountAmount;

        // Calculate amount received & change
        $amountReceived = $validated['amount_received'] ?? $total;
        $change = $amountReceived - $total;

        // Create transaction
        $transaction = Transaction::create([
            'transaction_number' => Transaction::generateTransactionNumber(),
            'user_id' => auth()->id(),
            'discount_id' => $discount?->id ?? null,  // NEW!
            'discount_amount' => $discountAmount,      // NEW!
            'payment_type' => $validated['payment_type'],
            'payment_method' => $validated['payment_method'],
            'subtotal' => $subtotal,
            'total' => $total,
            'amount_received' => $amountReceived,
            'change' => $change
        ]);

        // Create transaction items & update stock
        foreach ($validated['items'] as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['product_id'],
                'product_name' => Product::find($item['product_id'])->name,
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['qty']
            ]);

            Product::find($item['product_id'])->decrement('stock', $item['qty']);
        }

        // Log discount if applied
        if ($discount) {
            $discountService->logDiscountApplication(
                $discount, 
                $discountAmount, 
                $transaction->transaction_number
            );
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'data' => $transaction->load('items'),
            'discount_applied' => $discount ? true : false,  // NEW!
            'discount_amount' => $discountAmount             // NEW!
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}
```

---

## 🎨 Kasir UI Update

### Update: `resources/views/pages/kasir/index.blade.php`

Pada bagian payment summary, tambahkan baris discount:

```blade
{{-- Subtotal --}}
<div class="flex justify-between text-slate-700">
    <span>Subtotal</span>
    <span x-text="formatCurrency(subtotal)"></span>
</div>

{{-- Discount (NEW!) --}}
<div x-show="discountAmount > 0" class="flex justify-between text-green-600">
    <span>Diskon</span>
    <span x-text="'- ' + formatCurrency(discountAmount)"></span>
</div>

{{-- Total --}}
<div class="flex justify-between text-xl font-bold text-slate-900 pt-2 border-t">
    <span>Total</span>
    <span x-text="formatCurrency(total)"></span>
</div>
```

Dan di Alpine.js data, tambahkan:

```javascript
discountAmount: 0,

get total() {
    return this.subtotal - this.discountAmount; // Updated
},

async processPayment() {
    // ... existing code ...
    
    const response = await fetch('/api/kasir/transactions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrf
        },
        body: JSON.stringify({
            payment_type: this.paymentType,
            payment_method: this.paymentMethod,
            items: this.cart.map(item => ({
                product_id: item.id,
                qty: item.qty,
                price: item.price
            })),
            amount_received: this.amountReceived
        })
    });

    const result = await response.json();
    
    if (result.success) {
        // Show discount if applied
        if (result.discount_applied) {
            alert(`Diskon berhasil diterapkan: Rp ${result.discount_amount.toLocaleString('id-ID')}`);
        }
        
        // Reset & show success
        this.resetTransaction();
        alert('Transaksi berhasil!');
    }
}
```

---

## ✅ Checklist Implementasi

- [x] Migration discounts table
- [x] Migration pivot tables
- [x] Migration add discount to transactions
- [x] Model Discount with relationships
- [x] Update Transaction model
- [x] DiscountService (business logic)
- [x] DiscountController (CRUD)
- [ ] Routes (admin & API)
- [ ] View: discounts.blade.php (main page)
- [ ] Component: discount-table.blade.php
- [ ] Component: discount-modal.blade.php
- [ ] Update TransactionController (apply discount)
- [ ] Update Kasir view (show discount)
- [ ] Testing

---

## 🧪 Testing Plan

1. **Create Discount**:
   - Buat diskon 10% untuk produk spesifik
   - Buat diskon Rp 5.000 untuk tag "Snack"

2. **Kasir Transaction**:
   - Tambah produk yang dapat diskon
   - Proses pembayaran
   - Cek apakah diskon otomatis terapply

3. **Admin Management**:
   - Edit diskon
   - Toggle active/inactive
   - Hapus diskon (jika belum dipakai)

4. **Edge Cases**:
   - Diskon expired
   - Produk tidak eligible
   - Multiple products di cart (hanya yang eligible dapat diskon)

---

**File-file yang masih perlu dibuat tersedia di dokumentasi lengkap ini. Silakan lanjutkan implementasi sesuai step-by-step di atas!** 🚀
