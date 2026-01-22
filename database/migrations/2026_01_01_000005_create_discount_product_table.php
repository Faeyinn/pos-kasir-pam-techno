<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table for discount-product many-to-many relationship
     */
    public function up(): void
    {
        Schema::create('diskon_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_diskon')->constrained('diskon', 'id_diskon')->cascadeOnDelete();
            $table->foreignId('id_produk')->constrained('produk', 'id_produk')->cascadeOnDelete();
            $table->timestamps();
            
            // Composite unique key to prevent duplicates
            $table->unique(['id_diskon', 'id_produk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diskon_produk');
    }
};
