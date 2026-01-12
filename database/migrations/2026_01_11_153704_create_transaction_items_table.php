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
        // Tabel transaction_items - menyimpan detail item dalam transaksi
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel transactions
            $table->foreignId('transaction_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Relasi ke tabel products
            $table->foreignId('product_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Nama produk (disimpan untuk keperluan history)
            $table->string('product_name');
            
            // Jumlah item yang dibeli
            $table->integer('qty');
            
            // Harga satuan saat transaksi (bisa berbeda dengan harga saat ini)
            $table->integer('price');
            
            // Subtotal (qty * price)
            $table->integer('subtotal');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
