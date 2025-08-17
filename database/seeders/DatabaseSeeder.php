<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Role::factory()->count(3)->create();

        $this->call(TypeDePlatSeeder::class);
        $this->call(TypeDeCuisineSeeder::class);
        $this->call(RegimeAlimentaireSeeder::class);
        $this->call(ThemeCulinaireSeeder::class);
        $this->call(TypeDeRepasTableSeeder::class);
        $this->call(AmbianceAnimationsTableSeeder::class);
        
    }
}
