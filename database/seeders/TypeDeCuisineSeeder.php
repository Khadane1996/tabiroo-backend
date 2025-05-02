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
                'Cuisine africaine',
                'Cuisine asiatique',
                'Cuisine européenne',
                'Cuisine américaine',
                'Cuisine indienne',
                'Cuisine italienne',
                'Cuisine sénégalaise',
                'Cuisine méditerranéenne',
                'Cuisine orientale',
                'Cuisine fusion',
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
