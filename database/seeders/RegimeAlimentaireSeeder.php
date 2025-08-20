<?php

namespace Database\Seeders;

use App\Models\RegimeAlimentaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegimeAlimentaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regimes = [
            'Végétarien',
            'Végan',
            'Halal',
            'Casher',
            'Sans gluten',
            'Sans lactose',
            'Faible en sucre',
            'Faible en sel',
            'Hyper protéiné',
            'Keto',
            'Dukan',
            'Sportif',
            'aucun'
        ];

        foreach ($regimes as $desc) {
            if (!RegimeAlimentaire::where('description', $desc)->exists()) {
                RegimeAlimentaire::create([
                    'description' => $desc,
                ]);
            }
        }
    }
}
