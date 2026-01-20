<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Discounts table - consolidated from multiple migrations
     * Includes: base schema + datetime fields (instead of date)
     */
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            
            // Discount info
            $table->string('name');
            $table->enum('type', ['percentage', 'fixed'])->comment('percentage = %, fixed = nominal Rp');
            $table->integer('value')->comment('Value of discount (10 for 10%, or 10000 for Rp 10.000)');
            $table->enum('target_type', ['product', 'tag'])->comment('Discount applies to product or tag');
            
            // Validity period (datetime for precise scheduling)
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_activate')->default(true)->comment('Auto-activate based on schedule');
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
