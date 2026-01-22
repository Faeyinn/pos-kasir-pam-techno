<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tags table for product categorization
     */
    public function up(): void
    {
        Schema::create('tag', function (Blueprint $table) {
            $table->id('id_tag');
            $table->string('nama_tag')->unique();
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1'); // Hex color code
            $table->timestamps();
            
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag');
    }
};
