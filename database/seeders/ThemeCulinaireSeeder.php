<?php

namespace Database\Seeders;

use App\Models\ThemeCulinaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThemeCulinaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            'Cuisine de fête',
            'Cuisine rapide',
            'Cuisine santé',
            'Cuisine traditionnelle',
            'Cuisine de saison',
            'Cuisine gastronomique',
            'Cuisine économique',
            'Cuisine du monde',
            'Cuisine fusion',
            'Cuisine locale',
        ];

        foreach ($themes as $desc) {
            if (!ThemeCulinaire::where('description', $desc)->exists()) {
                ThemeCulinaire::create([
                    'description' => $desc,
                ]);
            }
        }
    }
}
