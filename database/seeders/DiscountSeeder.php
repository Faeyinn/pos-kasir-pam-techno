<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('diskon_tag')->truncate();
        DB::table('diskon_produk')->truncate();
        Discount::truncate();
        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $tags = Tag::all()->keyBy('nama_tag');
        $products = Product::all();

        // If there is no master data yet, skip silently.
        if ($tags->isEmpty() || $products->isEmpty()) {
            return;
        }

        // 10% for some tags
        $promoTag = Discount::create([
            'nama_diskon' => 'Promo Tag 10%',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'target' => 'tag',
            'tanggal_mulai' => $now->copy()->subDays(30),
            'tanggal_selesai' => $now->copy()->addDays(30),
            'is_active' => true,
            'auto_active' => true,
        ]);

        $targetTagNames = collect(['Minuman', 'Makanan', 'Sembako', 'Instan'])
            ->filter(fn ($name) => isset($tags[$name]))
            ->values();

        if ($targetTagNames->isNotEmpty()) {
            $promoTag->tags()->sync($targetTagNames->map(fn ($name) => $tags[$name]->id_tag)->all());
        }

        // Nominal discount for some products
        $promoProduk = Discount::create([
            'nama_diskon' => 'Potongan 5rb Produk',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 5000,
            'target' => 'produk',
            'tanggal_mulai' => $now->copy()->subDays(14),
            'tanggal_selesai' => $now->copy()->addDays(14),
            'is_active' => true,
            'auto_active' => true,
        ]);

        $promoProduk->products()->sync(
            $products->pluck('id_produk')->random(min(5, $products->count()))->all()
        );
    }

}
