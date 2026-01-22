<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProdukSatuan;
use App\Models\Tag;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('produk_tag')->truncate();
        ProdukSatuan::truncate();
        Product::truncate();
        Schema::enableForeignKeyConstraints();

        $tagsByName = Tag::all()->keyBy('nama_tag');

        $products = [
            ['nama_produk' => 'Aqua 600ml', 'stok' => 120, 'harga_jual' => 3500, 'harga_pokok' => 2800, 'grosir_harga' => 36000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 12, 'tags' => ['Botol', 'Minuman']],
            ['nama_produk' => 'Indomie Goreng', 'stok' => 200, 'harga_jual' => 3500, 'harga_pokok' => 2700, 'grosir_harga' => 128000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 40, 'tags' => ['Instan', 'Mie', 'Makanan']],
            ['nama_produk' => 'Susu Ultra Milk 250ml', 'stok' => 80, 'harga_jual' => 5000, 'harga_pokok' => 3800, 'grosir_harga' => 108000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 24, 'tags' => ['Susu', 'Kotak', 'Minuman']],
            ['nama_produk' => 'Teh Botol', 'stok' => 80, 'harga_jual' => 4000, 'harga_pokok' => 3000, 'grosir_harga' => 84000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 24, 'tags' => ['Botol', 'Minuman']],
            ['nama_produk' => 'Roti Tawar Sari Roti', 'stok' => 45, 'harga_jual' => 15000, 'harga_pokok' => 11000, 'grosir_harga' => 130000, 'grosir_satuan' => 'Pack', 'grosir_jumlah_per_satuan' => 10, 'tags' => ['Roti', 'Sarapan', 'Makanan']],
            ['nama_produk' => 'Mie Sedaap Goreng', 'stok' => 150, 'harga_jual' => 3500, 'harga_pokok' => 2600, 'grosir_harga' => 128000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 40, 'tags' => ['Instan', 'Mie', 'Makanan']],
            ['nama_produk' => 'Kopi Kapal Api', 'stok' => 200, 'harga_jual' => 2500, 'harga_pokok' => 1800, 'grosir_harga' => 110000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 50, 'tags' => ['Kopi', 'Bubuk', 'Minuman']],
            ['nama_produk' => 'Gula Pasir 1kg', 'stok' => 60, 'harga_jual' => 18000, 'harga_pokok' => 14000, 'grosir_harga' => 160000, 'grosir_satuan' => 'Karung', 'grosir_jumlah_per_satuan' => 10, 'tags' => ['Pokok', 'Sembako', 'Makanan']],
            ['nama_produk' => 'Beras Premium 5kg', 'stok' => 30, 'harga_jual' => 75000, 'harga_pokok' => 58000, 'grosir_harga' => 350000, 'grosir_satuan' => 'Karung', 'grosir_jumlah_per_satuan' => 5, 'tags' => ['Pokok', 'Sembako', 'Makanan']],
            ['nama_produk' => 'Minyak Goreng 2L', 'stok' => 40, 'harga_jual' => 35000, 'harga_pokok' => 27000, 'grosir_harga' => 192000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 6, 'tags' => ['Pokok', 'Sembako', 'Makanan']],
            ['nama_produk' => 'Sabun Lifebuoy', 'stok' => 100, 'harga_jual' => 5000, 'harga_pokok' => 3700, 'grosir_harga' => 108000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 24, 'tags' => ['Kebersihan', 'Perawatan']],
            ['nama_produk' => 'Shampoo Pantene 170ml', 'stok' => 50, 'harga_jual' => 22000, 'harga_pokok' => 17000, 'grosir_harga' => 240000, 'grosir_satuan' => 'Dus', 'grosir_jumlah_per_satuan' => 12, 'tags' => ['Kebersihan', 'Perawatan']],
        ];

        foreach ($products as $productData) {
            // Extract tags from product data
            $productTags = $productData['tags'];
            unset($productData['tags']);

            $hargaJualRetail = $productData['harga_jual'];
            $hargaPokokRetail = $productData['harga_pokok'];
            $grosirHarga = $productData['grosir_harga'];
            $grosirSatuan = $productData['grosir_satuan'];
            $grosirJumlahPerSatuan = $productData['grosir_jumlah_per_satuan'];

            unset($productData['harga_jual'], $productData['harga_pokok'], $productData['grosir_harga'], $productData['grosir_satuan'], $productData['grosir_jumlah_per_satuan']);

            // Create product
            $product = Product::create($productData);

            // Create default retail unit (pcs)
            ProdukSatuan::create([
                'id_produk' => $product->id_produk,
                'nama_satuan' => 'Pcs',
                'jumlah_per_satuan' => 1,
                'harga_pokok' => $hargaPokokRetail,
                'harga_jual' => $hargaJualRetail,
                'is_default' => true,
                'is_active' => true,
            ]);

            // Create wholesale unit if configured
            if (!empty($grosirSatuan) && $grosirJumlahPerSatuan > 1) {
                ProdukSatuan::create([
                    'id_produk' => $product->id_produk,
                    'nama_satuan' => $grosirSatuan,
                    'jumlah_per_satuan' => $grosirJumlahPerSatuan,
                    'harga_pokok' => (int) ($hargaPokokRetail * $grosirJumlahPerSatuan),
                    'harga_jual' => $grosirHarga,
                    'is_default' => false,
                    'is_active' => true,
                ]);
            }

            // Attach tags via relationship
            $tagIds = collect($productTags)
                ->map(function ($tagName) use (&$tagsByName) {
                    if (!isset($tagsByName[$tagName])) {
                        // Fallback: create missing tags
                        $tagsByName[$tagName] = Tag::create([
                            'nama_tag' => $tagName,
                            'slug' => Str::slug($tagName),
                            'color' => '#6b7280',
                        ]);
                    }
                    return $tagsByName[$tagName]->id_tag;
                })
                ->filter()
                ->values()
                ->all();
            
            if (!empty($tagIds)) {
                $product->tags()->attach($tagIds);
            }
        }
    }
}
