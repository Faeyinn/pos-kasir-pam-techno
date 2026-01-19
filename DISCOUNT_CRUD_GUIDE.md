# 🎨 Panduan CRUD Buttons - Fitur Diskon

## ✅ Fitur Yang Sudah Tersedia

Setiap row dalam tabel diskon memiliki **3 action buttons**:

### 1. **Toggle Status** (Badge Aktif/Nonaktif)
- Klik badge "Aktif" atau "Nonaktif"
- Toggle on/off tanpa modal
- Warna berubah otomatis (hijau/abu)

### 2. **Edit** (Icon Pensil Biru)
- Icon: 📝 (edit)
- Warna: Blue (#3B82F6)
- Action: Buka modal edit dengan data terisi

### 3. **Delete** (Icon Tempat Sampah Merah)
- Icon: 🗑️ (trash-2)
- Warna: Red (#EF4444)
- Action: Konfirmasi → Hapus data

---

## 📸 Visual Guide

### Tabel Diskon - Row Actions

```
┌──────────────────────────────────────────────────────────────────┐
│ Nama      │ Tipe   │ Nilai │ Target │ Periode  │ Status  │ Aksi  │
├──────────────────────────────────────────────────────────────────┤
│ Diskon    │ %      │ 10%   │ Produk │ 01/01 -  │ [Aktif] │ 📝 🗑️│
│ Ramadhan  │        │       │ Snack  │ 31/01    │         │       │
└──────────────────────────────────────────────────────────────────┘
                                                      ↑     ↑
                                                    Edit  Delete
```

### Kolom "Aksi" - Detail

```html
<div class="flex items-center justify-end gap-2">
    <!-- EDIT BUTTON -->
    <button 
        @click="openModal('edit', discount)"
        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
        title="Edit"
    >
        <i data-lucide="edit" class="w-4 h-4"></i>
    </button>
    
    <!-- DELETE BUTTON -->
    <button 
        @click="deleteDiscount(discount.id)"
        class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
        title="Hapus"
    >
        <i data-lucide="trash-2" class="w-4 h-4"></i>
    </button>
</div>
```

---

## 🔧 Cara Kerja Edit

### Flow Edit Discount:

```
1. User klik icon EDIT (📝)
   ↓
2. Alpine.js call openModal('edit', discount)
   ↓
3. Form data populated dengan data discount:
   - name: "Diskon Ramadhan"
   - type: "percentage"
   - value: 10
   - target_type: "product"
   - target_ids: [1, 3, 5]
   - start_date: "2026-01-01"
   - end_date: "2026-01-31"
   - is_active: true
   ↓
4. Modal terbuka dalam mode "edit"
   ↓
5. User ubah data yang diinginkan
   ↓
6. User klik "Simpan Perubahan"
   ↓
7. Alpine.js send PUT request ke:
   /api/admin/discounts/{id}
   ↓
8. Backend update data
   ↓
9. Success → Reload page
   Error → Show alert
```

### Code Implementation - Edit:

```javascript
// Method openModal di discounts.blade.php
openModal(mode, discount = null) {
    this.modalMode = mode;
    
    if (mode === 'edit' && discount) {
        // POPULATE FORM DENGAN DATA EXISTING
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
    this.$nextTick(() => lucide.createIcons());
}

// Method saveDiscount
async saveDiscount() {
    const url = this.modalMode === 'create' 
        ? '/api/admin/discounts'
        : `/api/admin/discounts/${this.formData.id}`; // PUT ke ID spesifik
    
    const method = this.modalMode === 'create' ? 'POST' : 'PUT';

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
        location.reload(); // Refresh untuk update table
    }
}
```

---

## 🗑️ Cara Kerja Delete

### Flow Delete Discount:

```
1. User klik icon DELETE (🗑️)
   ↓
2. Alpine.js call deleteDiscount(discount.id)
   ↓
3. Konfirmasi muncul:
   "Yakin ingin menghapus diskon ini?"
   ↓
4. User klik OK
   ↓
5. Alpine.js send DELETE request ke:
   /api/admin/discounts/{id}
   ↓
6. Backend cek:
   - Jika discount sudah dipakai → Error
   - Jika belum dipakai → Delete success
   ↓
7. Show alert message
   ↓
8. Success → Reload page
   Error → Tetap di page
```

### Code Implementation - Delete:

```javascript
async deleteDiscount(id) {
    // KONFIRMASI DULU
    if (!confirm('Yakin ingin menghapus diskon ini?')) return;

    try {
        const res = await fetch(`/api/admin/discounts/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await res.json();
        alert(data.message); // Show success/error message
        
        if (data.success) {
            location.reload(); // Refresh table
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}
```

### Backend Protection (DiscountController.php):

```php
public function destroy($id)
{
    $discount = Discount::findOrFail($id);

    // PROTEKSI: Cek apakah sudah dipakai
    if ($discount->transactions()->count() > 0) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak dapat menghapus diskon yang sudah digunakan dalam transaksi'
        ], 400);
    }

    $discount->delete();

    return response()->json([
        'success' => true,
        'message' => 'Diskon berhasil dihapus'
    ]);
}
```

---

## 🎨 Styling Details

### Button Styles:

```css
/* Edit Button */
.text-blue-600        /* Icon color: Blue-600 */
.hover:bg-blue-50     /* Hover background: Light blue */
.rounded-lg           /* Rounded corners */
.p-2                  /* Padding: 8px */
.transition-colors    /* Smooth color transition */

/* Delete Button */
.text-red-600         /* Icon color: Red-600 */
.hover:bg-red-50      /* Hover background: Light red */
.rounded-lg           /* Rounded corners */
.p-2                  /* Padding: 8px */
.transition-colors    /* Smooth color transition */
```

### Icon Sizes:
- Width: 16px (w-4)
- Height: 16px (h-4)
- Lucide icons for consistency

---

## ✅ Testing Checklist

### Test Edit:
- [ ] Klik icon edit
- [ ] Modal terbuka dengan data terisi
- [ ] Semua field editable
- [ ] Checkbox produk/tag sesuai
- [ ] Tanggal ter-parse dengan benar
- [ ] Simpan perubahan berhasil
- [ ] Table ter-update setelah reload

### Test Delete:
- [ ] Klik icon delete
- [ ] Konfirmasi muncul
- [ ] Cancel → tidak ada perubahan
- [ ] OK → data terhapus
- [ ] Error jika sudah dipakai dalam transaksi
- [ ] Success message muncul

### Test Protection:
- [ ] Buat discount
- [ ] Gunakan di transaksi kasir
- [ ] Coba delete → harus ERROR
- [ ] Message: "Tidak dapat menghapus diskon yang sudah digunakan"

---

## 🐛 Troubleshooting

### Edit Modal Tidak Muncul
**Problem**: Klik edit tapi modal tidak terbuka  
**Solution**: 
1. Check browser console untuk error
2. Pastikan Alpine.js sudah loaded
3. Pastikan icons sudah di-create (`lucide.createIcons()`)

### Data Tidak Terisi Saat Edit
**Problem**: Modal terbuka tapi form kosong  
**Solution**:
1. Check data `discount` di browser console
2. Pastikan eager loading di controller: `Discount::with(['products', 'tags'])`
3. Check relasi di model Discount

### Delete Tidak Berfungsi
**Problem**: Klik delete tapi tidak ada response  
**Solution**:
1. Check CSRF token ada di meta tag
2. Check route API terdaftar
3. Check method `destroy` di DiscountController
4. Check browser network tab untuk response

### Icons Tidak Muncul
**Problem**: Hanya muncul kotak kosong  
**Solution**:
1. Pastikan Lucide CDN sudah di-load
2. Call `lucide.createIcons()` setelah DOM update
3. Check di `init()` dan setelah modal open

---

## 📊 API Endpoints Summary

| Method | Endpoint | Action | Protection |
|--------|----------|--------|-----------|
| GET | `/admin/discounts` | Show page | role:admin |
| POST | `/api/admin/discounts` | Create | role:admin |
| PUT | `/api/admin/discounts/{id}` | Update | role:admin |
| POST | `/api/admin/discounts/{id}/toggle` | Toggle status | role:admin |
| DELETE | `/api/admin/discounts/{id}` | Delete | role:admin + transaction check |

---

## 🎯 Quick Reference

### Edit Discount:
1. Klik icon 📝 (edit)
2. Ubah data
3. Klik "Simpan Perubahan"

### Delete Discount:
1. Klik icon 🗑️ (trash)
2. Konfirmasi OK
3. Data terhapus (jika belum dipakai)

### Toggle Status:
1. Klik badge "Aktif" atau "Nonaktif"
2. Status langsung berubah

**Semua fitur CRUD sudah READY dan FUNCTIONAL!** ✅
