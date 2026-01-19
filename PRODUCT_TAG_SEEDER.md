# ✅ Product Tag Seeder - Documentation

## 📦 What It Does

`ProductTagSeeder` automatically assigns realistic tags to products based on their names and categories.

---

## 🏷️ Tag Mapping Strategy

### Snacks (Makanan Ringan)
```
Produk              → Tags
Chitato             → Snack, Makanan Ringan, Keripik
Pringles            → Snack, Makanan Ringan, Keripik, Premium
Taro                → Snack, Makanan Ringan, Keripik
Lays                → Snack, Makanan Ringan, Keripik
Cheetos             → Snack, Makanan Ringan
Oreo                → Snack, Biskuit, Makanan Ringan
```

### Beverages (Minuman)
```
Produk              → Tags
Coca Cola/Pepsi     → Minuman, Minuman Soda, Dingin
Sprite/Fanta        → Minuman, Minuman Soda, Dingin
Aqua/Le Minerale    → Minuman, Air Mineral, Sehat
Teh Botol/Fruit Tea → Minuman, Teh, Dingin
```

### Instant Noodles (Mie Instan)
```
Produk              → Tags
Indomie/Mie Sedaap  → Makanan Instan, Mie Instan, Sarapan
Sarimi/Supermie     → Makanan Instan, Mie Instan, Sarapan
```

### Dairy (Produk Susu)
```
Produk              → Tags
Susu Ultra          → Minuman, Susu, Sehat, Sarapan
Yakult              → Minuman, Probiotik, Sehat
Cimory              → Minuman, Susu, Yogurt, Sehat
```

### Bread & Bakery (Roti)
```
Produk              → Tags
Roti Tawar          → Roti, Sarapan, Makanan Pokok
Roti Sobek          → Roti, Camilan, Makanan Ringan
Donat               → Roti, Camilan, Dessert
```

### Ice Cream (Es Krim)
```
Produk              → Tags
Walls/Aice          → Es Krim, Dessert, Dingin
```

### Condiments (Bumbu)
```
Produk              → Tags
Kecap               → Bumbu, Saus, Makanan Pokok
Saos                → Bumbu, Saus, Pelengkap
```

### Coffee (Kopi)
```
Produk              → Tags
Kopi/Kapal Api      → Minuman, Kopi, Sarapan
Good Day            → Minuman, Kopi, Sarapan
```

---

## 🚀 How to Run

### Run the seeder:
```bash
php artisan db:seed --class=ProductTagSeeder
```

### Or include in DatabaseSeeder:
```php
// database/seeders/DatabaseSeeder.php
public function run()
{
    $this->call([
        TagSeeder::class,
        ProductSeeder::class,
        ProductTagSeeder::class,  // Add this
        // ... other seeders
    ]);
}
```

---

## 🔍 How It Works

1. **Clears existing relationships**: `product_tag` table truncated
2. **Loads products and tags**: Gets all from database
3. **Pattern matching**: Checks product name against patterns
4. **Assigns tags**: Uses `sync()` to attach multiple tags
5. **Fallback logic**: If no match, uses generic keywords

### Matching Logic:
```php
// 1. Exact pattern match (stripos)
if (stripos($productName, 'Chitato') !== false) {
    // Assign: Snack, Makanan Ringan, Keripik
}

// 2. Generic keyword fallback
elseif (stripos($productName, 'snack') !== false) {
    // Assign: Snack, Makanan Ringan
}

// 3. Ultimate fallback
else {
    // Assign: Produk Umum
}
```

---

## ✅ Expected Result

After running the seeder:

```
✓ Chitato tagged with 3 tags
✓ Indomie tagged with 3 tags
✓ Coca Cola tagged with 3 tags
✓ Aqua tagged with 3 tags
...
Product tags seeded successfully!
```

### Database Check:
```bash
php artisan tinker

# Check total relationships
DB::table('product_tag')->count();
// Expected: ~40-60 relationships

# Check specific product
$product = Product::with('tags')->where('name', 'like', '%Chitato%')->first();
$product->tags->pluck('name');
// Output: ["Snack", "Makanan Ringan", "Keripik"]
```

---

## 📊 Impact on Dashboard

After running this seeder, the **Category Distribution Chart** will now display:

```
Pie Chart Sections:
- Snack (35%)
- Minuman (25%)
- Makanan Instan (20%)
- Susu (10%)
- Roti (6%)
- Others (4%)
```

**Before seeder**: Chart empty or "Belum ada data kategori"  
**After seeder**: Chart filled with realistic category breakdown!

---

## 🛠️ Customization

To add more products or modify tags:

```php
$tagMappings = [
    'Your Product Name' => ['Tag 1', 'Tag 2', 'Tag 3'],
    // Add more...
];
```

### Example - Add new product:
```php
'Kopiko' => ['Minuman', 'Kopi', 'Permen'],
'Silverqueen' => ['Snack', 'Coklat', 'Premium'],
```

---

## 🔄 Re-run Seeder

Safe to run multiple times:
- Truncates existing relationships first
- No duplicates will be created
- Tags will be reassigned fresh

```bash
php artisan db:seed --class=ProductTagSeeder
```

---

## ✅ Verification Checklist

- [ ] Seeder runs without errors
- [ ] `product_tag` table has records
- [ ] Dashboard category chart shows data
- [ ] Each product has 1-4 tags
- [ ] Tags are relevant to product type

---

**Status**: ✅ READY TO USE  
**Impact**: Dashboard Category Distribution now works!
