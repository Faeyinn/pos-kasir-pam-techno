<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabel transactions - menyimpan data transaksi penjualan
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Nomor transaksi unik
            $table->string('transaction_number')->unique();
            
            // Relasi ke user (kasir yang melakukan transaksi)
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Tipe pembayaran: retail (eceran) atau wholesale (grosir)
            $table->enum('payment_type', ['retail', 'wholesale'])
                  ->default('retail');
            
            // Metode pembayaran
            $table->enum('payment_method', ['tunai', 'kartu', 'qris', 'ewallet'])
                  ->default('tunai');
            
            // Detail pembayaran
            $table->integer('subtotal');
            $table->integer('total');
            $table->integer('amount_received');
            $table->integer('change');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
