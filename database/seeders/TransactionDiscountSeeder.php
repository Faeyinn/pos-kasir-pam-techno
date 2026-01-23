<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\ProdukSatuan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionDiscountSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dapatkan User Utama (Admin)
        $user = User::where('role', 'admin')->first() ?? User::first();
        if (!$user) {
            $this->call(UserSeeder::class);
            $user = User::first();
        }

        // 2. Siapkan Produk dan Tag
        if (Tag::count() == 0) {
            $this->call(TagSeeder::class);
        }

        if (Product::count() == 0) {
            $this->call(ProductSeeder::class);
        }
        
        $products = Product::with(['tags', 'satuan'])->get();
        $tags = Tag::all();

        if ($products->isEmpty() || $tags->isEmpty()) {
            $this->command->error('Gagal memuat produk atau tag. Pastikan seeder produk dan tag berfungsi.');
            return;
        }

        // 3. Buat Diskon Riil (Jika belum ada yang cocok)
        $discounts = [];
        
        // Diskon Persentase
        $promoGajian = Discount::updateOrCreate(
            ['nama_diskon' => 'Promo Gajian 10%'],
            [
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 10,
                'target' => 'tag',
                'tanggal_mulai' => Carbon::now()->subDays(60),
                'tanggal_selesai' => Carbon::now()->addDays(30),
                'is_active' => true,
                'auto_active' => true
            ]
        );
        $promoGajian->tags()->sync($tags->pluck('id_tag')->random(min(3, $tags->count())));
        $discounts[] = $promoGajian;

        // Diskon Nominal
        $potonganLangsung = Discount::updateOrCreate(
            ['nama_diskon' => 'Potongan Langsung 5rb'],
            [
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 5000,
                'target' => 'produk',
                'tanggal_mulai' => Carbon::now()->subDays(30),
                'tanggal_selesai' => Carbon::now()->addDays(15),
                'is_active' => true,
                'auto_active' => true
            ]
        );
        $potonganLangsung->products()->sync($products->pluck('id_produk')->random(min(5, $products->count())));
        $discounts[] = $potonganLangsung;

        // 4. Buat Data Transaksi (30 Hari Terakhir)
        $this->command->info('Membuat data transaksi... Harap tunggu.');
        
        DB::beginTransaction();
        try {
            // Kita buat 200 transaksi saja agar lebih cepat tapi cukup untuk laporan
            for ($i = 0; $i < 200; $i++) {
                $date = Carbon::now()
                    ->subDays(rand(0, 30))
                    ->subHours(rand(0, 23))
                    ->subMinutes(rand(0, 59))
                    ->subSeconds(rand(0, 59));
                
                // 50% transaksi pakai diskon
                $hasDiscount = rand(1, 10) <= 5;
                $discount = $hasDiscount ? $discounts[array_rand($discounts)] : null;
                
                // Pick random items (1-4 items)
                $numItems = rand(1, 4);
                $selectedProducts = $products->random(min($numItems, $products->count()));
                
                $totalBelanja = 0;
                $itemsData = [];
                
                foreach ($selectedProducts as $product) {
                    $satuan = $product->satuan->firstWhere('is_default', true) ?? $product->satuan->first();
                    if (!$satuan) continue;

                    $qty = rand(1, 3);
                    $itemSubtotal = $satuan->harga_jual * $qty;
                    $totalBelanja += $itemSubtotal;
                    
                    $itemsData[] = [
                        'id_produk' => $product->id_produk,
                        'id_satuan' => $satuan->id_satuan,
                        'nama_produk' => $product->nama_produk,
                        'nama_satuan' => $satuan->nama_satuan,
                        'jumlah_per_satuan' => $satuan->jumlah_per_satuan,
                        'jumlah' => $qty,
                        'harga_pokok' => $satuan->harga_pokok,
                        'harga_jual' => $satuan->harga_jual,
                        'subtotal' => $itemSubtotal,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }

                if (empty($itemsData)) continue;

                // Calculate Discount Amount
                $discountAmount = 0;
                if ($discount) {
                    if ($discount->tipe_diskon === 'persen') {
                        $discountAmount = ($totalBelanja * $discount->nilai_diskon) / 100;
                    } else {
                        $discountAmount = min($discount->nilai_diskon, $totalBelanja);
                    }
                }

                $totalTransaksi = max(0, $totalBelanja - $discountAmount);
                $received = ceil($totalTransaksi / 1000) * 1000;
                if ($received < $totalTransaksi) $received += 1000;

                $transaction = Transaction::create([
                    'nomor_transaksi' => 'TRX-' . $date->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'id_user' => $user->id,
                    'id_diskon' => $discount ? $discount->id_diskon : null,
                    'diskon' => $discountAmount,
                    'jenis_transaksi' => rand(1, 10) > 8 ? 'grosir' : 'eceran',
                    'metode_pembayaran' => ['tunai', 'qris', 'ewallet', 'kartu'][rand(0, 3)],
                    'total_belanja' => $totalBelanja,
                    'total_transaksi' => $totalTransaksi,
                    'jumlah_dibayar' => $received,
                    'kembalian' => $received - $totalTransaksi,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                foreach ($itemsData as $item) {
                    $item['id_transaksi'] = $transaction->id_transaksi;
                    TransactionItem::create($item);
                }
            }
            DB::commit();
            $this->command->info('Berhasil membuat data transaksi dengan link ke diskon.');
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Gagal membuat data: ' . $e->getMessage());
            throw $e;
        }
    }
}
