<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_satuan', function (Blueprint $table) {
            $table->id('id_satuan');

            $table->foreignId('id_produk')
                ->constrained('produk', 'id_produk')
                ->cascadeOnDelete();

            $table->string('nama_satuan', 100);
            $table->unsignedInteger('jumlah_per_satuan');
            $table->unsignedBigInteger('harga_pokok');
            $table->unsignedBigInteger('harga_jual');

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['id_produk', 'is_active']);
            $table->index(['id_produk', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_satuan');
    }
};
