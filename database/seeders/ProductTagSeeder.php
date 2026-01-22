<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class ProductTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Assign realistic tags to each product based on their category
     */
    public function run()
    {
        // Clear existing product-tag relationships
        DB::table('produk_tag')->truncate();

        // Get all products and tags
        $products = Product::all();
        $tags = Tag::all()->keyBy('nama_tag');

        // Tag mapping based on product name patterns
        $tagMappings = [
            // Snacks
            'Chitato' => ['Snack', 'Makanan Ringan', 'Keripik'],
            'Pringles' => ['Snack', 'Makanan Ringan', 'Keripik', 'Premium'],
            'Taro' => ['Snack', 'Makanan Ringan', 'Keripik'],
            'Lays' => ['Snack', 'Makanan Ringan', 'Keripik'],
            'Cheetos' => ['Snack', 'Makanan Ringan'],
            'Oreo' => ['Snack', 'Biskuit', 'Makanan Ringan'],
            
            // Beverages
            'Coca Cola' => ['Minuman', 'Minuman Soda', 'Dingin'],
            'Pepsi' => ['Minuman', 'Minuman Soda', 'Dingin'],
            'Sprite' => ['Minuman', 'Minuman Soda', 'Dingin'],
            'Fanta' => ['Minuman', 'Minuman Soda', 'Dingin'],
            'Aqua' => ['Minuman', 'Air Mineral', 'Sehat'],
            'Le Minerale' => ['Minuman', 'Air Mineral', 'Sehat'],
            'Teh Botol' => ['Minuman', 'Teh', 'Dingin'],
            'Fruit Tea' => ['Minuman', 'Teh', 'Dingin'],
            'Frestea' => ['Minuman', 'Teh', 'Dingin'],
            
            // Instant Noodles
            'Indomie' => ['Makanan Instan', 'Mie Instan', 'Sarapan'],
            'Mie Sedaap' => ['Makanan Instan', 'Mie Instan', 'Sarapan'],
            'Sarimi' => ['Makanan Instan', 'Mie Instan', 'Sarapan'],
            'Supermie' => ['Makanan Instan', 'Mie Instan', 'Sarapan'],
            
            // Dairy
            'Susu Ultra' => ['Minuman', 'Susu', 'Sehat', 'Sarapan'],
            'Yakult' => ['Minuman', 'Probiotik', 'Sehat'],
            'Cimory' => ['Minuman', 'Susu', 'Yogurt', 'Sehat'],
            
            // Bread & Bakery
            'Roti Tawar' => ['Roti', 'Sarapan', 'Makanan Pokok'],
            'Roti Sobek' => ['Roti', 'Camilan', 'Makanan Ringan'],
            'Donat' => ['Roti', 'Camilan', 'Dessert'],
            
            // Ice Cream
            'Walls' => ['Es Krim', 'Dessert', 'Dingin'],
            'Aice' => ['Es Krim', 'Dessert', 'Dingin'],
            
            // Condiments
            'Kecap' => ['Bumbu', 'Saus', 'Makanan Pokok'],
            'Saos' => ['Bumbu', 'Saus', 'Pelengkap'],
            
            // Coffee
            'Kopi' => ['Minuman', 'Kopi', 'Panas'],
            'Good Day' => ['Minuman', 'Kopi', 'Sarapan'],
            'Kapal Api' => ['Minuman', 'Kopi', 'Sarapan'],
            
            // Default for unknown products
            'default' => ['Produk Umum']
        ];

        foreach ($products as $product) {
            $productName = $product->nama_produk;
            $assignedTags = [];

            // Find matching tags based on product name
            foreach ($tagMappings as $pattern => $tagNames) {
                if ($pattern === 'default') continue;
                
                if (stripos($productName, $pattern) !== false) {
                    foreach ($tagNames as $tagName) {
                        if (isset($tags[$tagName])) {
                            $assignedTags[] = $tags[$tagName]->id;
                        }
                    }
                    break; // Use first match
                }
            }

            // If no tags assigned, use default
            if (empty($assignedTags)) {
                // Try to assign based on generic keywords
                if (stripos($productName, 'snack') !== false || 
                    stripos($productName, 'keripik') !== false) {
                    $assignedTags[] = $tags['Snack']->id ?? null;
                    $assignedTags[] = $tags['Makanan Ringan']->id ?? null;
                } elseif (stripos($productName, 'drink') !== false || 
                          stripos($productName, 'minuman') !== false) {
                    $assignedTags[] = $tags['Minuman']->id ?? null;
                } elseif (stripos($productName, 'mie') !== false || 
                          stripos($productName, 'noodle') !== false) {
                    $assignedTags[] = $tags['Makanan Instan']->id ?? null;
                    $assignedTags[] = $tags['Mie Instan']->id ?? null;
                } else {
                    $assignedTags[] = $tags['Produk Umum']->id ?? 1; // Fallback to first tag
                }
            }

            // Filter out nulls and attach tags
            $assignedTags = array_filter($assignedTags);
            if (!empty($assignedTags)) {
                $product->tags()->sync($assignedTags);
                $this->command->info("✓ {$product->nama_produk} tagged with " . count($assignedTags) . " tags");
            }
        }

        $this->command->info("Product tags seeded successfully!");
    }
}
