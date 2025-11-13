<?php

namespace Database\Seeders;

use App\Models\TypeDeCuisine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeDeCuisineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        {
            $types = [
                'Française',
                'Terroirs',
                'Méditerranéenne',
                'Italienne',
                'Orientale',
                'Marocaine',
                'Libanaise',
                'Africaine',
                'Sénégalaise',
                'Ivoirienne',
                'Antillaise',
                'Créole',
                'Asiatique',
                'Chinoise',
                'Thaïlandaise',
                'Vietnamienne',
                'Japonaise',
                'Coréenne',
                'Indienne',
                'Américaine',
                'Mexicaine',
                'Turque',
                'Réunionnaise',
                'Fusion',
                'Autre'
            ];
    
            foreach ($types as $desc) {
                if (!TypeDeCuisine::where('description', $desc)->exists()) {
                    TypeDeCuisine::create([
                        'description' => $desc,
                    ]);
                }
            }
        }
    }
}
