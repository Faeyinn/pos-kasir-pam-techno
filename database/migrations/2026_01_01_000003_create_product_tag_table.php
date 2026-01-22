<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table for product-tag many-to-many relationship
     */
    public function up(): void
    {
        Schema::create('produk_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_produk')->constrained('produk', 'id_produk')->cascadeOnDelete();
            $table->foreignId('id_tag')->constrained('tag', 'id_tag')->cascadeOnDelete();
            $table->timestamps();
            
            // Composite unique key to prevent duplicates
            $table->unique(['id_produk', 'id_tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_tag');
    }
};
