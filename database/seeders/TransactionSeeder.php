<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Get all products and users
        $products = Product::where('is_active', true)->get();
        $users = User::all();

        if ($products->isEmpty()) {
            $this->command->error('❌ Tidak ada produk aktif! Jalankan ProductSeeder terlebih dahulu.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('❌ Tidak ada user! Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $this->command->info('🚀 Mulai generate transaksi dummy...');
        
        // Payment configurations
        $paymentTypes = ['retail', 'wholesale'];
        $paymentMethods = ['tunai', 'kartu', 'qris', 'ewallet'];
        $paymentMethodWeights = [60, 20, 15, 5]; // Tunai lebih dominan
        
        // Time patterns (simulate realistic transaction times)
        $busyHours = [10, 11, 12, 13, 14, 15, 16]; // Jam ramai

        $transactionCount = 0;
        $totalProfit = 0;

        // Generate transactions for last 30 days
        for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
            $targetDate = Carbon::now()->subDays($dayOffset);
            
            // Skip if Sunday (toko tutup)
            if ($targetDate->isSunday()) {
                continue;
            }
            
            // Determine number of transactions for this day
            $isWeekend = $targetDate->isSaturday();
            $dailyTransactionCount = $isWeekend ? rand(15, 25) : rand(8, 18);

            for ($i = 0; $i < $dailyTransactionCount; $i++) {
                // Random time with bias towards busy hours
                if (rand(1, 100) <= 70) {
                    $hour = $busyHours[array_rand($busyHours)];
                } else {
                    $hour = rand(9, 20);
                }
                
                $minute = rand(0, 59);
                $createdAt = $targetDate->copy()->setTime($hour, $minute, rand(0, 59));

                // Determine payment type (90% retail, 10% wholesale)
                $paymentType = (rand(1, 100) <= 90) ? 'retail' : 'wholesale';
                
                // Weighted random payment method
                $paymentMethod = $this->weightedRandom($paymentMethods, $paymentMethodWeights);

                // Generate unique transaction number
                $dateKey = $createdAt->format('Ymd');
                $existingCount = Transaction::whereDate('created_at', $createdAt->toDateString())->count();
                $transactionNumber = 'TRX-' . $dateKey . '-' . str_pad($existingCount + 1, 4, '0', STR_PAD_LEFT);

                // Select 1-4 random products for this transaction
                $itemCount = rand(1, 4);
                $selectedProducts = $products->random(min($itemCount, $products->count()));

                $subtotal = 0;
                $items = [];

                foreach ($selectedProducts as $product) {
                    // Check if product has stock
                    if ($product->stock <= 0) {
                        continue;
                    }

                    // Determine quantity based on stock and payment type
                    if ($paymentType === 'wholesale' && $product->wholesale > 0 && $product->wholesale_qty_per_unit > 0) {
                        // Wholesale: buy in units (1-3 units)
                        $units = rand(1, min(3, floor($product->stock / $product->wholesale_qty_per_unit)));
                        if ($units < 1) continue;
                        
                        $qty = $units;
                        $price = $product->wholesale;
                        $actualStockDeduction = $units * $product->wholesale_qty_per_unit;
                    } else {
                        // Retail: buy individual items
                        $maxQty = min($product->stock, 10);
                        $qty = rand(1, $maxQty);
                        $price = $product->price;
                        $actualStockDeduction = $qty;
                    }

                    $itemSubtotal = $price * $qty;
                    $subtotal += $itemSubtotal;

                    $items[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $itemSubtotal,
                        'stock_deduction' => $actualStockDeduction
                    ];
                }

                // Skip if no valid items
                if (empty($items)) {
                    continue;
                }

                // Calculate total and change
                $total = $subtotal;
                
                // For cash payment, generate realistic amount received
                if ($paymentMethod === 'tunai') {
                    $roundedTotal = ceil($total / 1000) * 1000; // Round up to nearest 1000
                    $amountReceived = $roundedTotal + (rand(0, 2) * 1000); // Add 0-2k extra
                } else {
                    $amountReceived = $total; // Exact amount for digital payments
                }
                
                $change = $amountReceived - $total;

                // Create transaction
                DB::beginTransaction();
                try {
                    $transaction = Transaction::create([
                        'transaction_number' => $transactionNumber,
                        'user_id' => $users->random()->id,
                        'payment_type' => $paymentType,
                        'payment_method' => $paymentMethod,
                        'subtotal' => $subtotal,
                        'total' => $total,
                        'amount_received' => $amountReceived,
                        'change' => $change,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt
                    ]);

                    // Create transaction items
                    foreach ($items as $item) {
                        TransactionItem::create([
                            'transaction_id' => $transaction->id,
                            'product_id' => $item['product']->id,
                            'product_name' => $item['product']->name,
                            'qty' => $item['qty'],
                            'price' => $item['price'],
                            'subtotal' => $item['subtotal'],
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt
                        ]);

                        // Calculate profit for this item
                        $itemProfit = ($item['price'] - $item['product']->cost_price) * $item['qty'];
                        $totalProfit += $itemProfit;

                        // Update product stock (DON'T actually deduct in seeder, just for simulation)
                        // Uncomment below if you want to actually deduct stock:
                        // $item['product']->decrement('stock', $item['stock_deduction']);
                    }

                    DB::commit();
                    $transactionCount++;

                    if ($transactionCount % 20 == 0) {
                        $this->command->info("✅ Generated {$transactionCount} transactions...");
                    }

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->command->error("❌ Error creating transaction: " . $e->getMessage());
                }
            }
        }

        $this->command->newLine();
        $this->command->info("🎉 Seeder selesai!");
        $this->command->info("📊 Total transaksi: {$transactionCount}");
        $this->command->info("💰 Total profit (estimasi): Rp " . number_format($totalProfit, 0, ',', '.'));
        $this->command->newLine();
        $this->command->warn("⚠️  Catatan: Stok produk TIDAK dikurangi oleh seeder ini.");
        $this->command->warn("    Jika ingin mengurangi stok, uncomment baris di seeder.");
    }

    /**
     * Get weighted random value
     */
    private function weightedRandom($values, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $value;
            }
        }
        
        return $values[0];
    }
}
