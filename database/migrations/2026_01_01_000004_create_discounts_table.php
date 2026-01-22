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
        Schema::create('diskon', function (Blueprint $table) {
            $table->id('id_diskon');
            
            // Discount info
            $table->string('nama_diskon');
            $table->enum('tipe_diskon', ['persen', 'nominal']);
            $table->unsignedInteger('nilai_diskon');
            $table->enum('target', ['produk', 'tag']);
            
            // Validity period (datetime for precise scheduling)
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_active')->default(true);
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('is_active');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diskon');
    }
};
