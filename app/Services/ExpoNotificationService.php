<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoNotificationService
{
    const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Envoyer une notification push à un utilisateur via Expo
     */
    public static function sendToUser($userId, string $title, string $body, array $data = [])
    {
        $user = User::find($userId);

        if (!$user || !$user->expo_push_token) {
            return null;
        }

        return self::sendPush($user->expo_push_token, $title, $body, $data);
    }

    /**
     * Envoyer une notification push à plusieurs utilisateurs
     */
    public static function sendToUsers(array $userIds, string $title, string $body, array $data = [])
    {
        $users = User::whereIn('id', $userIds)
            ->whereNotNull('expo_push_token')
            ->get();

        $messages = [];
        foreach ($users as $user) {
            $messages[] = [
                'to' => $user->expo_push_token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ];
        }

        if (empty($messages)) {
            return null;
        }

        return self::sendBatch($messages);
    }

    /**
     * Envoyer une notification push unique
     */
    protected static function sendPush(string $token, string $title, string $body, array $data = [])
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post(self::EXPO_PUSH_URL, [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);

            if ($response->failed()) {
                Log::error('Expo push notification failed', [
                    'token' => $token,
                    'response' => $response->body(),
                ]);
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Expo push notification error', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Envoyer un batch de notifications
     */
    protected static function sendBatch(array $messages)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post(self::EXPO_PUSH_URL, $messages);

            if ($response->failed()) {
                Log::error('Expo push batch notification failed', [
                    'response' => $response->body(),
                ]);
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Expo push batch notification error', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
