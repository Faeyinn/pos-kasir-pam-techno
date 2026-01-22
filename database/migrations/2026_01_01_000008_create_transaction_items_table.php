<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transaction items table - stores individual items in a transaction
     */
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail_transaksi');
            
            // Relationships
            $table->foreignId('id_transaksi')->constrained('transaksi', 'id_transaksi')->cascadeOnDelete();
            $table->foreignId('id_produk')->constrained('produk', 'id_produk')->restrictOnDelete();
            $table->foreignId('id_satuan')->constrained('produk_satuan', 'id_satuan')->restrictOnDelete();
            
            // Denormalized product name (for historical record)
            $table->string('nama_produk');

            // Denormalized unit snapshot (for historical record)
            $table->string('nama_satuan', 100);
            $table->unsignedInteger('jumlah_per_satuan');
            
            // Item details
            $table->unsignedInteger('jumlah');
            $table->unsignedBigInteger('harga_pokok');
            $table->unsignedBigInteger('harga_jual');
            $table->unsignedBigInteger('subtotal');
            
            $table->timestamps();
            
            // Indexes
            $table->index('id_transaksi');
            $table->index('id_produk');
            $table->index('id_satuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
