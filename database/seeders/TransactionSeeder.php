<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProdukSatuan;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\DiscountService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::active()->with(['satuan' => fn ($q) => $q->where('is_active', true)])->get();
        $users = User::all();

        if ($products->isEmpty() || $users->isEmpty()) {
            return;
        }

        $discountService = app(DiscountService::class);

        $paymentMethods = ['tunai', 'kartu', 'qris', 'ewallet'];
        $paymentMethodWeights = [60, 20, 15, 5];
        $busyHours = [10, 11, 12, 13, 14, 15, 16];

        // Keep seed fast: last 14 days, moderate counts
        for ($dayOffset = 13; $dayOffset >= 0; $dayOffset--) {
            $targetDate = Carbon::now()->subDays($dayOffset);
            if ($targetDate->isSunday()) {
                continue;
            }

            $isWeekend = $targetDate->isSaturday();
            $dailyTransactionCount = $isWeekend ? rand(6, 10) : rand(4, 8);

            // Boost for today (prioritize today's data)
            if ($dayOffset === 0) {
                $dailyTransactionCount = rand(15, 25);
            }

            for ($i = 0; $i < $dailyTransactionCount; $i++) {
                $hour = (rand(1, 100) <= 70)
                    ? $busyHours[array_rand($busyHours)]
                    : rand(9, 20);

                $createdAt = $targetDate->copy()->setTime($hour, rand(0, 59), rand(0, 59));

                $metodePembayaran = $this->weightedRandom($paymentMethods, $paymentMethodWeights);

                // Select 1-4 random products
                $selectedProducts = $products->random(min(rand(1, 4), $products->count()));

                $itemsForCreate = [];
                $cartItemsForDiscount = [];
                $totalBelanja = 0;
                $jenisTransaksi = 'eceran';

                foreach ($selectedProducts as $product) {
                    /** @var Product $product */
                    $defaultSatuan = $product->satuan->firstWhere('is_default', true)
                        ?? $product->satuan->first();

                    if (!$defaultSatuan) {
                        continue;
                    }

                    // 20% chance choose non-default unit if exists
                    $nonDefaultSatuan = $product->satuan->firstWhere('is_default', false);
                    $useWholesaleUnit = $nonDefaultSatuan && rand(1, 100) <= 20;

                    /** @var ProdukSatuan $satuan */
                    $satuan = $useWholesaleUnit ? $nonDefaultSatuan : $defaultSatuan;

                    $jumlah = rand(1, 5);
                    $qtyBaseToDeduct = $jumlah * (int) $satuan->jumlah_per_satuan;

                    // Skip stock check to ensure transactions are created even if low stock
                    // if ((int) $product->stok < $qtyBaseToDeduct) {
                    //    continue;
                    // }

                    if ((int) $satuan->jumlah_per_satuan > 1) {
                        $jenisTransaksi = 'grosir';
                    }

                    $hargaJual = (int) $satuan->harga_jual;
                    $subtotalItem = $jumlah * $hargaJual;

                    $totalBelanja += $subtotalItem;

                    $itemsForCreate[] = [
                        'id_produk' => $product->id_produk,
                        'id_satuan' => $satuan->id_satuan,
                        'nama_produk' => $product->nama_produk,
                        'nama_satuan' => $satuan->nama_satuan,
                        'jumlah_per_satuan' => (int) $satuan->jumlah_per_satuan,
                        'jumlah' => $jumlah,
                        'harga_pokok' => (int) $satuan->harga_pokok,
                        'harga_jual' => $hargaJual,
                        'subtotal' => $subtotalItem,
                        'qty_base_to_deduct' => $qtyBaseToDeduct,
                    ];

                    $cartItemsForDiscount[] = [
                        'product_id' => $product->id_produk,
                        'qty' => $jumlah,
                        'price' => $hargaJual,
                    ];
                }

                if (empty($itemsForCreate)) {
                    continue;
                }

                $discountData = $discountService->findApplicableDiscount(collect($cartItemsForDiscount));
                $discountAmount = (int) ($discountData['amount'] ?? 0);
                $discountAmount = min($discountAmount, $totalBelanja);

                $totalTransaksi = $totalBelanja - $discountAmount;

                // Realistic payment
                $jumlahDibayar = $metodePembayaran === 'tunai'
                    ? (int) (ceil($totalTransaksi / 1000) * 1000 + (rand(0, 2) * 1000))
                    : (int) $totalTransaksi;

                $kembalian = $jumlahDibayar - $totalTransaksi;
                if ($kembalian < 0) {
                    $jumlahDibayar = (int) $totalTransaksi;
                    $kembalian = 0;
                }

                DB::beginTransaction();
                try {
                    $transaction = Transaction::create([
                        'nomor_transaksi' => 'TRX-' . $createdAt->format('Ymd') . '-' . str_pad(
                            Transaction::whereDate('created_at', $createdAt->toDateString())->count() + 1,
                            4,
                            '0',
                            STR_PAD_LEFT
                        ),
                        'id_user' => $users->random()->id,
                        'jenis_transaksi' => $jenisTransaksi,
                        'metode_pembayaran' => $metodePembayaran,
                        'total_belanja' => $totalBelanja,
                        'diskon' => $discountAmount,
                        'id_diskon' => $discountData['discount']?->id_diskon,
                        'total_transaksi' => $totalTransaksi,
                        'jumlah_dibayar' => $jumlahDibayar,
                        'kembalian' => $kembalian,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    foreach ($itemsForCreate as $item) {
                        TransactionItem::create([
                            'id_transaksi' => $transaction->id_transaksi,
                            'id_produk' => $item['id_produk'],
                            'id_satuan' => $item['id_satuan'],
                            'nama_produk' => $item['nama_produk'],
                            'nama_satuan' => $item['nama_satuan'],
                            'jumlah_per_satuan' => $item['jumlah_per_satuan'],
                            'jumlah' => $item['jumlah'],
                            'harga_pokok' => $item['harga_pokok'],
                            'harga_jual' => $item['harga_jual'],
                            'subtotal' => $item['subtotal'],
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);

                        // Product::where('id_produk', $item['id_produk'])->decrement('stok', $item['qty_base_to_deduct']);
                    }

                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    // Keep seeding resilient: skip the failed record
                }
            }
        }
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
