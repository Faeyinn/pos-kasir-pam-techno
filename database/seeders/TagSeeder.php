<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Facades\Schema;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Tag::truncate();
        Schema::enableForeignKeyConstraints();

        $tags = [
            ['name' => 'Minuman', 'color' => '#3b82f6'], // Blue
            ['name' => 'Makanan', 'color' => '#f59e0b'], // Amber
            ['name' => 'Sembako', 'color' => '#10b981'], // Green
            ['name' => 'Instan', 'color' => '#ef4444'], // Red
            ['name' => 'Susu', 'color' => '#8b5cf6'], // Purple
            ['name' => 'Botol', 'color' => '#06b6d4'], // Cyan
            ['name' => 'Kotak', 'color' => '#f97316'], // Orange
            ['name' => 'Roti', 'color' => '#eab308'], // Yellow
            ['name' => 'Mie', 'color' => '#ec4899'], // Pink
            ['name' => 'Kopi', 'color' => '#78350f'], // Brown
            ['name' => 'Bubuk', 'color' => '#6b7280'], // Gray
            ['name' => 'Pokok', 'color' => '#059669'], // Emerald
            ['name' => 'Kebersihan', 'color' => '#0ea5e9'], // Sky
            ['name' => 'Perawatan', 'color' => '#a855f7'], // Violet
            ['name' => 'Sarapan', 'color' => '#f59e0b'], // Amber
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
