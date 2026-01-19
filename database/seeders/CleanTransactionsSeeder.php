<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('⚠️  Menghapus semua data transaksi...');
        
        $transactionCount = DB::table('transactions')->count();
        $itemCount = DB::table('transaction_items')->count();
        
        if ($transactionCount == 0) {
            $this->command->info('✅ Tidak ada data transaksi untuk dihapus.');
            return;
        }
        
        $this->command->info("📊 Ditemukan {$transactionCount} transaksi dengan {$itemCount} items.");
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('transaction_items')->truncate();
        DB::table('transactions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ Semua data transaksi telah dihapus!');
        $this->command->info('💡 Jalankan TransactionSeeder untuk generate data baru.');
    }
}
