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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Basic product info
            $table->string('name');
            $table->string('image')->nullable();
            
            // Pricing
            $table->integer('price');
            $table->integer('cost_price')->default(0); // Purchase/cost price for profit calculation
            
            // Wholesale pricing
            $table->integer('wholesale')->default(0);
            $table->string('wholesale_unit')->nullable();
            $table->integer('wholesale_qty_per_unit')->default(1);
            
            // Inventory
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('is_active');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
