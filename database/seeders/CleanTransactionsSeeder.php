<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('⚠️  Menghapus semua data transaksi...');
        
        $transactionCount = DB::table('transaksi')->count();
        $itemCount = DB::table('detail_transaksi')->count();
        
        if ($transactionCount == 0) {
            $this->command->info('✅ Tidak ada data transaksi untuk dihapus.');
            return;
        }
        
        $this->command->info("📊 Ditemukan {$transactionCount} transaksi dengan {$itemCount} items.");
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('detail_transaksi')->truncate();
        DB::table('transaksi')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ Semua data transaksi telah dihapus!');
        $this->command->info('💡 Jalankan TransactionSeeder untuk generate data baru.');
    }
}
