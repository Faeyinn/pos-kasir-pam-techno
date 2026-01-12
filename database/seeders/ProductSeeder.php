<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Aqua 600ml', 'price' => 3500, 'wholesale' => 36000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 12, 'category' => 'minuman', 'stock' => 120],
            ['name' => 'Indomie Goreng', 'price' => 3500, 'wholesale' => 128000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 40, 'category' => 'makanan', 'stock' => 200],
            ['name' => 'Susu Ultra Milk 250ml', 'price' => 5000, 'wholesale' => 108000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 24, 'category' => 'minuman', 'stock' => 80],
            ['name' => 'Teh Botol', 'price' => 4000, 'wholesale' => 84000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 24, 'category' => 'minuman', 'stock' => 80],
            ['name' => 'Roti Tawar Sari Roti', 'price' => 15000, 'wholesale' => 130000, 'wholesale_unit' => 'Pack', 'wholesale_qty_per_unit' => 10, 'category' => 'makanan', 'stock' => 45],
            ['name' => 'Mie Sedaap Goreng', 'price' => 3500, 'wholesale' => 128000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 40, 'category' => 'makanan', 'stock' => 150],
            ['name' => 'Kopi Kapal Api', 'price' => 2500, 'wholesale' => 110000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 50, 'category' => 'minuman', 'stock' => 200],
            ['name' => 'Gula Pasir 1kg', 'price' => 18000, 'wholesale' => 160000, 'wholesale_unit' => 'Karung', 'wholesale_qty_per_unit' => 10, 'category' => 'sembako', 'stock' => 60],
            ['name' => 'Beras Premium 5kg', 'price' => 75000, 'wholesale' => 350000, 'wholesale_unit' => 'Karung', 'wholesale_qty_per_unit' => 5, 'category' => 'sembako', 'stock' => 30],
            ['name' => 'Minyak Goreng 2L', 'price' => 35000, 'wholesale' => 192000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 6, 'category' => 'sembako', 'stock' => 40],
            ['name' => 'Sabun Lifebuoy', 'price' => 5000, 'wholesale' => 108000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 24, 'category' => 'kebutuhan', 'stock' => 100],
            ['name' => 'Shampoo Pantene 170ml', 'price' => 22000, 'wholesale' => 240000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 12, 'category' => 'kebutuhan', 'stock' => 50],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
