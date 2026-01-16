<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->json('tags')->nullable(); 
            $table->string('image')->nullable(); 

            $table->integer('price');

            $table->integer('wholesale')->default(0);
            $table->string('wholesale_unit')->nullable(); 
            $table->integer('wholesale_qty_per_unit')->default(1); 

            $table->integer('stock')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
