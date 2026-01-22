<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transactions table - consolidated from multiple migrations
     * Includes: base schema + discount fields
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            
            // Transaction identifier
            $table->string('nomor_transaksi')->unique();
            
            // Relationships
            $table->foreignId('id_user')->constrained('users', 'id')->restrictOnDelete();
            
            // Payment info
            $table->enum('jenis_transaksi', ['eceran', 'grosir'])->default('eceran');
            $table->enum('metode_pembayaran', ['tunai', 'kartu', 'qris', 'ewallet'])->default('tunai');
            
            // Amounts
            $table->unsignedBigInteger('total_belanja');
            $table->unsignedBigInteger('diskon')->default(0);
            $table->unsignedBigInteger('total_transaksi');
            $table->unsignedBigInteger('jumlah_dibayar');
            $table->unsignedBigInteger('kembalian');
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('nomor_transaksi');
            $table->index('created_at');
            $table->index('id_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
