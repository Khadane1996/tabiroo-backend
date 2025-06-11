<?php

namespace Database\Seeders;

use App\Models\TypeDeRepas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeDeRepasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'description' => 'Brunch',
                'start_time' => '09:00',
                'end_time' => '13:00',
            ],
            [
                'description' => 'Déjeuner',
                'start_time' => '11:00',
                'end_time' => '15:00',
            ],
            [
                'description' => 'Pause gourmande',
                'start_time' => '15:00',
                'end_time' => '18:00',
            ],
            [
                'description' => 'Dîner',
                'start_time' => '18:00',
                'end_time' => '02:00',
            ],
        ];

        foreach ($types as $type) {
            if (!TypeDeRepas::where('description', $type['description'])->exists()) {
                TypeDeRepas::create($type);
            }
        }
    }
}
