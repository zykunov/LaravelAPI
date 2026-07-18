<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Отправка email уведомлений
     */
    public function sendContactEmails(array $data, ?array $aiResponse = null): bool
    {
        try {
            // Email администратору
            $adminEmail = config('mail.from.address', 'admin@' . parse_url(config('app.url'), PHP_URL_HOST));
            
            Mail::send('emails.contact_admin', [
                'data' => $data,
                'ai_response' => $aiResponse
            ], function ($message) use ($adminEmail, $data) {
                $message->to($adminEmail)
                    ->subject('Новое обращение с формы обратной связи');
            });

            // Копия пользователю (если указан email)
            if (!empty($data['email'])) {
                Mail::send('emails.contact_user', [
                    'data' => $data,
                    'ai_response' => $aiResponse
                ], function ($message) use ($data) {
                    $message->to($data['email'])
                        ->subject('Ваше обращение принято');
                });
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }
}
