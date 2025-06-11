<?php

namespace Database\Seeders;

use App\Models\AmbianceAnimation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmbianceAnimationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            'Soirée jeux de société',
            'Soirée quiz',
            'Cinéma maison',
            'Soirée foot',
            'Club de lecture',
            'Débat (politique / société)',
            'Spiritualité & échanges',
            'Manga / Anime',
            'Cosplay',
            'Atelier créatif (dessin, peinture…)',
            'Business & réseautage',
            'Job dating',
            'Astuces beauté',
            'Mode, tendances & relooking'
        ];

        foreach ($themes as $desc) {
            if (!AmbianceAnimation::where('description', $desc)->exists()) {
                AmbianceAnimation::create([
                    'description' => $desc,
                ]);
            }
        }
    }
}
