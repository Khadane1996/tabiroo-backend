<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupère ou crée le rôle "Admin"
        $adminRole = Role::firstOrCreate(['libelle' => 'Admin']);

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'firstNameOrPseudo' => 'Admin',
                'lastName' => 'Tabiroo',
                'phone' => null,
                'biographie' => null,
                'photo_url' => null,
                'role_id' => $adminRole->id,
                'confirmation_code' => null,
                'etat' => null,
                'password' => Hash::make('azertyui'),
                'is_admin' => true,
            ]
        );
    }
}

