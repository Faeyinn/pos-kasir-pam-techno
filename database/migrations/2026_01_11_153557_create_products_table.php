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
        // Tabel products - menyimpan data produk
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Informasi dasar produk
            $table->string('name');
            $table->json('tags')->nullable(); // Tag untuk filter produk (Consolidated from later migrations)
            $table->string('image')->nullable(); // Path atau URL gambar produk
            
            // Harga eceran (retail)
            $table->integer('price');
            
            // Harga grosir (wholesale)
            $table->integer('wholesale')->default(0);
            $table->string('wholesale_unit')->nullable(); // Contoh: Dus, Karung, Pack
            $table->integer('wholesale_qty_per_unit')->default(1); // Jumlah pcs per unit grosir
            
            // Stok produk
            $table->integer('stock')->default(0);
            
            // Status aktif/non-aktif
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
