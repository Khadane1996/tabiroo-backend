<?php

namespace Database\Seeders;

use App\Models\TypeDePlat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeDePlatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Amuse bouche',
            'Entrée',
            'Plat principal',
            'Fromage',
            'Dessert',
            'Boisson'
        ];

        foreach ($types as $desc) {
            if (!TypeDePlat::where('description', $desc)->exists()) {
                TypeDePlat::create([
                    'description' => $desc
                ]);
            }
        }
    }
}
