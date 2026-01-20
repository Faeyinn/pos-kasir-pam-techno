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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Transaction identifier
            $table->string('transaction_number')->unique();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discount_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('discount_amount')->default(0);
            
            // Payment info
            $table->enum('payment_type', ['retail', 'wholesale'])->default('retail');
            $table->enum('payment_method', ['tunai', 'kartu', 'qris', 'ewallet'])->default('tunai');
            
            // Amounts
            $table->integer('subtotal');
            $table->integer('total');
            $table->integer('amount_received');
            $table->integer('change');
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('transaction_number');
            $table->index('created_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
