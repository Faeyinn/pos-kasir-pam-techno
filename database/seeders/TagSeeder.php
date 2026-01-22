<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Tag::truncate();
        Schema::enableForeignKeyConstraints();

        $tags = [
            ['nama_tag' => 'Minuman', 'color' => '#3b82f6'], // Blue
            ['nama_tag' => 'Makanan', 'color' => '#f59e0b'], // Amber
            ['nama_tag' => 'Sembako', 'color' => '#10b981'], // Green
            ['nama_tag' => 'Instan', 'color' => '#ef4444'], // Red
            ['nama_tag' => 'Susu', 'color' => '#8b5cf6'], // Purple
            ['nama_tag' => 'Botol', 'color' => '#06b6d4'], // Cyan
            ['nama_tag' => 'Kotak', 'color' => '#f97316'], // Orange
            ['nama_tag' => 'Roti', 'color' => '#eab308'], // Yellow
            ['nama_tag' => 'Mie', 'color' => '#ec4899'], // Pink
            ['nama_tag' => 'Kopi', 'color' => '#78350f'], // Brown
            ['nama_tag' => 'Bubuk', 'color' => '#6b7280'], // Gray
            ['nama_tag' => 'Pokok', 'color' => '#059669'], // Emerald
            ['nama_tag' => 'Kebersihan', 'color' => '#0ea5e9'], // Sky
            ['nama_tag' => 'Perawatan', 'color' => '#a855f7'], // Violet
            ['nama_tag' => 'Sarapan', 'color' => '#f59e0b'], // Amber
        ];

        foreach ($tags as $tag) {
            Tag::create([
                ...$tag,
                'slug' => Str::slug($tag['nama_tag']),
            ]);
        }
    }
}
