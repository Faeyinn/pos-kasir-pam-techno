<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table for discount-tag many-to-many relationship
     */
    public function up(): void
    {
        Schema::create('diskon_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_diskon')->constrained('diskon', 'id_diskon')->cascadeOnDelete();
            $table->foreignId('id_tag')->constrained('tag', 'id_tag')->cascadeOnDelete();
            $table->timestamps();
            
            // Composite unique key to prevent duplicates
            $table->unique(['id_diskon', 'id_tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diskon_tag');
    }
};
