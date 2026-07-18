<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContactEmails implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $data;
    protected ?string $aiResponse;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, ?string $aiResponse = null)
    {
        $this->data = $data;
        $this->aiResponse = $aiResponse;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = $this->data;

        // Email администратору
        $adminEmail = config('mail.from.address', 'admin@' . parse_url(config('app.url'), PHP_URL_HOST));

        Mail::send('emails.contact_admin', [
            'data' => $data,
            'ai_response' => $this->aiResponse
        ], function ($message) use ($adminEmail, $data) {
            $message->to($adminEmail)
                ->subject('Новое обращение с формы обратной связи');
        });

        // Копия пользователю (если указан email)
        if (!empty($data['email'])) {
            Mail::send('emails.contact_user', [
                'data' => $data,
                'ai_response' => $this->aiResponse
            ], function ($message) use ($data) {
                $message->to($data['email'])
                    ->subject('Ваше обращение принято');
            });
        }
    }
}
