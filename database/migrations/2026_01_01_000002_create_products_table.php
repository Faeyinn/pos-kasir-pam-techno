<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Products table - consolidated from multiple migrations
     * Includes: base schema + cost_price (removed json tags column)
     */
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id('id_produk');

            // Basic product info
            $table->string('nama_produk');
            $table->string('gambar')->nullable();

            // Inventory (stok satuan dasar)
            $table->unsignedInteger('stok')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('is_active');
            $table->index('nama_produk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
