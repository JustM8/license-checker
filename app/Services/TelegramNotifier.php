<?php

namespace App\Services;

class TelegramNotifier
{
    public static function send(string $message): void
    {
        $chatId = '386327020'; // <- твій chat_id
        $token = '8141281031:AAFYNfq2BhHgYM6tcywIykSuTIC3JxYQLxE'; // <- заміни на правильний

        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ];

        try {
            file_get_contents($url . '?' . http_build_query($payload));
        } catch (\Exception $e) {
            \Log::error('Помилка надсилання повідомлення в Telegram: ' . $e->getMessage());
        }
    }
}

