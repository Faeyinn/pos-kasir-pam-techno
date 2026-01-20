<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionDiscountSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dapatkan User Utama (Admin)
        $user = User::first() ?? User::factory()->create();

        // 2. Siapkan Produk dan Tag
        // Pastikan ada produk untuk dijual
        if (Product::count() < 10) {
            $this->call(ProductTagSeeder::class);
        }
        
        $products = Product::all();
        $tags = Tag::all();

        // 3. Buat Diskon Riil (Jika belum ada yang cocok)
        $discounts = [];
        
        // Diskon Persentase (Efektif)
        $discounts[] = Discount::updateOrCreate(
            ['name' => 'Promo Gajian 10%'],
            [
                'type' => 'percentage',
                'value' => 10,
                'target_type' => 'tag',
                'start_date' => Carbon::now()->subDays(60),
                'end_date' => Carbon::now()->addDays(30),
                'is_active' => true,
                'auto_activate' => true
            ]
        );
        $discounts[0]->tags()->sync($tags->pluck('id')->random(min(2, $tags->count())));

        // Diskon Fixed (Biasa)
        $discounts[] = Discount::updateOrCreate(
            ['name' => 'Potongan Langsung 5rb'],
            [
                'type' => 'fixed',
                'value' => 5000,
                'target_type' => 'product',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(15),
                'is_active' => true,
                'auto_activate' => true
            ]
        );
        $discounts[1]->products()->sync($products->pluck('id')->random(min(5, $products->count())));

        // 4. Buat Data Transaksi (30 Hari Terakhir)
        $this->command->info('Membuat data transaksi... Harap tunggu.');
        
        DB::beginTransaction();
        try {
            for ($i = 0; $i < 400; $i++) {
                $date = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                
                // Randomly choose if this transaction has a discount (30% chance)
                $hasDiscount = rand(1, 10) <= 3;
                $discount = $hasDiscount ? $discounts[array_rand($discounts)] : null;
                
                // Pick random items (1-5 items)
                $numItems = rand(1, 5);
                $selectedProducts = $products->random($numItems);
                
                $subtotal = 0;
                $itemsData = [];
                
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 3);
                    $itemSubtotal = $product->price * $qty;
                    $subtotal += $itemSubtotal;
                    
                    $itemsData[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'qty' => $qty,
                        'price' => $product->price,
                        'subtotal' => $itemSubtotal,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }

                // Calculate Discount Amount
                $discountAmount = 0;
                if ($discount) {
                    if ($discount->type === 'percentage') {
                        $discountAmount = ($subtotal * $discount->value) / 100;
                    } else {
                        // Fixed discount, but shouldn't exceed subtotal
                        $discountAmount = min($discount->value, $subtotal);
                    }
                }

                $total = $subtotal - $discountAmount;
                $received = ceil($total / 5000) * 5000; // Round up to nearest 5000
                if ($received < $total) $received += 5000;

                $transaction = Transaction::create([
                    'transaction_number' => 'TRX-' . $date->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'user_id' => $user->id,
                    'discount_id' => $discount ? $discount->id : null,
                    'discount_amount' => $discountAmount,
                    'payment_type' => 'retail',
                    'payment_method' => ['tunai', 'qris', 'ewallet'][rand(0, 2)],
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'amount_received' => $received,
                    'change' => $received - $total,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                foreach ($itemsData as $item) {
                    $item['transaction_id'] = $transaction->id;
                    TransactionItem::create($item);
                }
            }
            DB::commit();
            $this->command->info('Berhasil membuat 400 data transaksi dengan variasi diskon.');
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Gagal membuat data: ' . $e->getMessage());
        }
    }
}
