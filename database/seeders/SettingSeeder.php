<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'owner_report_email',
                'value' => env('OWNER_EMAIL', 'admin@example.com'),
                'description' => 'Email owner untuk menerima laporan rutin (PDF & Excel).'
            ],
            // Tambahkan setting lain di sini jika diperlukan di masa depan
        ];

        foreach ($settings as $item) {
            Setting::updateOrCreate(
                ['key' => $item['key']],
                [
                    'value' => $item['value'],
                    'description' => $item['description']
                ]
            );
        }
    }
}
