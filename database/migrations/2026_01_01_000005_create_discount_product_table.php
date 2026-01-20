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
        Schema::create('discount_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // Composite unique key to prevent duplicates
            $table->unique(['discount_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_product');
    }
};
