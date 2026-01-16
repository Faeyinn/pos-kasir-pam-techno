<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_number')->unique();

            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->enum('payment_type', ['retail', 'wholesale'])
                  ->default('retail');

            $table->enum('payment_method', ['tunai', 'kartu', 'qris', 'ewallet'])
                  ->default('tunai');

            $table->integer('subtotal');
            $table->integer('total');
            $table->integer('amount_received');
            $table->integer('change');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
