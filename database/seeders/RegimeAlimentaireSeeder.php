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
            'Végétalien',
            'Sans gluten',
            'Halal',
            'Casher',
            'Sans lactose',
            'Paléo',
            'Keto',
            'Flexitarien',
            'Diabétique',
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
