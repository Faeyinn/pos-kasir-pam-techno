<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Product::truncate();
        Schema::enableForeignKeyConstraints();

        $products = [
            ['name' => 'Aqua 600ml', 'price' => 3500, 'cost_price' => 2800, 'wholesale' => 36000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 12, 'stock' => 120, 'tags' => ['Botol', 'Minuman']],
            ['name' => 'Indomie Goreng', 'price' => 3500, 'cost_price' => 2700, 'wholesale' => 128000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 40, 'stock' => 200, 'tags' => ['Instan', 'Mie']],
            ['name' => 'Susu Ultra Milk 250ml', 'price' => 5000, 'cost_price' => 3800, 'wholesale' => 108000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 24, 'stock' => 80, 'tags' => ['Susu', 'Kotak']],
            ['name' => 'Teh Botol', 'price' => 4000, 'cost_price' => 3000, 'wholesale' => 84000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 24, 'stock' => 80, 'tags' => ['Botol', 'Minuman']],
            ['name' => 'Roti Tawar Sari Roti', 'price' => 15000, 'cost_price' => 11000, 'wholesale' => 130000, 'wholesale_unit' => 'Pack', 'wholesale_qty_per_unit' => 10, 'stock' => 45, 'tags' => ['Roti', 'Sarapan']],
            ['name' => 'Mie Sedaap Goreng', 'price' => 3500, 'cost_price' => 2600, 'wholesale' => 128000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 40, 'stock' => 150, 'tags' => ['Instan', 'Mie']],
            ['name' => 'Kopi Kapal Api', 'price' => 2500, 'cost_price' => 1800, 'wholesale' => 110000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 50, 'stock' => 200, 'tags' => ['Kopi', 'Bubuk']],
            ['name' => 'Gula Pasir 1kg', 'price' => 18000, 'cost_price' => 14000, 'wholesale' => 160000, 'wholesale_unit' => 'Karung', 'wholesale_qty_per_unit' => 10, 'stock' => 60, 'tags' => ['Pokok', 'Sembako']],
            ['name' => 'Beras Premium 5kg', 'price' => 75000, 'cost_price' => 58000, 'wholesale' => 350000, 'wholesale_unit' => 'Karung', 'wholesale_qty_per_unit' => 5, 'stock' => 30, 'tags' => ['Pokok', 'Sembako']],
            ['name' => 'Minyak Goreng 2L', 'price' => 35000, 'cost_price' => 27000, 'wholesale' => 192000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 6, 'stock' => 40, 'tags' => ['Pokok', 'Sembako']],
            ['name' => 'Sabun Lifebuoy', 'price' => 5000, 'cost_price' => 3700, 'wholesale' => 108000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 24, 'stock' => 100, 'tags' => ['Kebersihan', 'Perawatan']],
            ['name' => 'Shampoo Pantene 170ml', 'price' => 22000, 'cost_price' => 17000, 'wholesale' => 240000, 'wholesale_unit' => 'Dus', 'wholesale_qty_per_unit' => 12, 'stock' => 50, 'tags' => ['Kebersihan', 'Perawatan']],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
