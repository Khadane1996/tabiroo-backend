<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\ExpoNotificationService;

class DebugPushNotifications extends Command
{
    protected $signature = 'debug:push {--user= : ID du user à tester} {--send : Envoyer un push de test}';
    protected $description = 'Diagnostiquer les push notifications : vérifier les tokens et envoyer un test';

    public function handle()
    {
        $this->info('=== DIAGNOSTIC PUSH NOTIFICATIONS ===');
        $this->newLine();

        // 1. Vérifier la colonne expo_push_token
        $this->info('1. Vérification de la colonne expo_push_token...');
        try {
            $hasColumn = \Schema::hasColumn('users', 'expo_push_token');
            if ($hasColumn) {
                $this->info('   ✓ Colonne expo_push_token existe dans la table users');
            } else {
                $this->error('   ✗ Colonne expo_push_token MANQUANTE ! Lance: php artisan migrate');
                return;
            }
        } catch (\Throwable $e) {
            $this->error('   ✗ Erreur: ' . $e->getMessage());
            return;
        }

        // 2. Lister les users avec/sans token
        $this->newLine();
        $this->info('2. État des tokens push...');

        $usersWithToken = User::whereNotNull('expo_push_token')->get(['id', 'firstNameOrPseudo', 'email', 'expo_push_token', 'role_id']);
        $usersWithoutToken = User::whereNull('expo_push_token')->get(['id', 'firstNameOrPseudo', 'email', 'role_id']);

        if ($usersWithToken->isEmpty()) {
            $this->error('   ✗ AUCUN utilisateur n\'a de token push enregistré !');
            $this->warn('   → Les apps doivent se reconnecter après le nouveau build.');
        } else {
            $this->info("   ✓ {$usersWithToken->count()} utilisateur(s) avec token push :");
            foreach ($usersWithToken as $u) {
                $role = $u->role_id == 1 ? 'client' : 'chef';
                $this->line("     - [{$role}] #{$u->id} {$u->firstNameOrPseudo} ({$u->email}) → {$u->expo_push_token}");
            }
        }

        $this->newLine();
        $this->info("   {$usersWithoutToken->count()} utilisateur(s) SANS token push :");
        foreach ($usersWithoutToken as $u) {
            $role = $u->role_id == 1 ? 'client' : 'chef';
            $this->line("     - [{$role}] #{$u->id} {$u->firstNameOrPseudo} ({$u->email})");
        }

        // 3. Envoyer un push de test si demandé
        $userId = $this->option('user');
        $shouldSend = $this->option('send');

        if ($shouldSend && $userId) {
            $this->newLine();
            $this->info("3. Envoi d'un push de test au user #{$userId}...");

            $user = User::find($userId);
            if (!$user) {
                $this->error("   ✗ Utilisateur #{$userId} introuvable");
                return;
            }

            if (!$user->expo_push_token) {
                $this->error("   ✗ Utilisateur #{$userId} n'a PAS de token push !");
                $this->warn("   → L'utilisateur doit se reconnecter dans l'app.");
                return;
            }

            $this->line("   Token: {$user->expo_push_token}");
            $this->line("   Envoi en cours...");

            $result = ExpoNotificationService::sendToUser(
                $userId,
                'Test Tabiroo',
                'Si vous voyez cette notification, les push fonctionnent !',
                ['type' => 'test']
            );

            $this->newLine();
            $this->info('   Réponse Expo:');
            $this->line('   ' . json_encode($result, JSON_PRETTY_PRINT));

            if ($result && isset($result['data']) && isset($result['data']['status'])) {
                if ($result['data']['status'] === 'ok') {
                    $this->info('   ✓ Push envoyé avec succès !');
                } else {
                    $this->error('   ✗ Erreur Expo: ' . json_encode($result['data']));
                }
            }
        } elseif ($shouldSend && !$userId) {
            $this->warn('   Spécifie un user: php artisan debug:push --send --user=1');
        }

        $this->newLine();
        $this->info('=== ACTIONS RECOMMANDÉES ===');
        if ($usersWithToken->isEmpty()) {
            $this->line('1. Vérifie que la migration a été lancée: php artisan migrate');
            $this->line('2. Reconnecte-toi dans les apps (déconnexion + reconnexion)');
            $this->line('3. Relance cette commande pour vérifier');
        } else {
            $this->line('Teste un push: php artisan debug:push --send --user=ID');
        }
    }
}
