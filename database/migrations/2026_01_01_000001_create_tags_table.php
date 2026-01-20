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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1'); // Hex color code
            $table->timestamps();
            
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
