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
            'Brunch',
            'Déjeuner',
            'Pause gourmande',
            'Dîner'
        ];

        foreach ($types as $desc) {
            if (!TypeDeRepas::where('description', $desc)->exists()) {
                TypeDeRepas::create([
                    'description' => $desc
                ]);
            }
        }
    }
}
