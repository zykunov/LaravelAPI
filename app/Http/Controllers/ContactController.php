<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\RateLimiter;
use App\Services\AiService;
use App\Services\EmailService;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class ContactController extends Controller
{
    protected $rateLimiter;
    protected $aiService;

    public function __construct()
    {
        $this->rateLimiter = new RateLimiter();
        $this->aiService = new AiService();
    }

    /**
     * Отправка формы обратной связи
     */
    public function store(Request $request)
    {
        // Проверка лимитов
        $ip = $request->ip();
        if ($this->rateLimiter->isRateLimited($ip)) {
            return response()->json([
                'error' => 'Слишком много запросов. Попробуйте позже.'
            ], 429);
        }

        // Валидация
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'comment' => 'required|string|min:10|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Неверные данные',
                'details' => $validator->errors()
            ], 422);
        }

        try {
            // Логирование всех запросов в файл
            $logData = $this->logRequest($request->all());

            // Получение данных для email
            $emailData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'comment' => $request->comment,
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ];

            // AI-анализ, если доступен
            $aiAnalysis = null;
            if ($this->aiService && !empty($request->comment)) {
                $aiAnalysis = $this->aiService->analyzeContact(
                    $request->name,
                    $request->email ?? '',
                    $request->phone,
                    $request->comment
                );
            }

            // Запуск отправки email
            $emailService = new EmailService();
            $emailService->sendContactEmails($emailData, $aiAnalysis?->ai_analysis ?? null);

            // Сохранение в базу данных
            $contact = Contact::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'comment' => $request->comment,
                'ip_address' => $ip,
//                'user_agent' => $request->userAgent(),
                'ai_analysis' => $aiAnalysis?->ai_analysis ?? null,
                'sentiment' => $aiAnalysis?->ai_analysis['sentiment']['sentiment'] ?? null,
                'meta' => [
                    'ai_enabled' => $aiAnalysis?->is_ai_enabled,
                    'fallback' => $aiAnalysis?->fallback
                ]
            ]);

            // Обновление лимита
            $this->rateLimiter->hit($ip);

            return response()->json([
                'success' => true,
                'message' => 'Обращение успешно отправлено',
                'data' => $logData,
                'ai_enabled' => $this->aiService->isEnabled()
            ], 200);

        } catch (Exception $e) {
            Log::error('Contact form error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Возникла ошибка при обработке обращения'
            ], 500);
        }
    }

    /**
     * Логирование запроса
     */
    protected function logRequest(array $data): array
    {
        $logData = [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'comment' => $data['comment'],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString()
        ];

        Log::channel('contact')->info('Contact form submission', $logData);

        return $logData;
    }

    /**
     * Проверка статуса сервиса
     */
    public function health()
    {
        $aiStatus = 'disabled';
        $aiError = null;

        try {
            if ($this->aiService->isEnabled()) {
                if ($this->aiService->canUseAI()) {
                    $aiStatus = 'connected';
                } else {
                    $aiStatus = 'error';
                    $aiError = 'Не удалось подключиться к AI сервису';
                }
            }
        } catch (\Exception $e) {
            $aiStatus = 'error';
            $aiError = $e->getMessage();
        }

        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toDateTimeString(),
            'ai' => [
                'enabled' => $this->aiService->isEnabled(),
                'status' => $aiStatus,
                'error' => $aiError
            ],
            'services' => [
                'mail' => config('mail.default'),
                'cache' => config('cache.default'),
                'queue' => config('queue.default')
            ]
        ]);
    }

    /**
     * Статистика обращений
     */
    public function metrics()
    {
        try {
            $stats = [
                'total_requests' => Contact::count(),
                'today_requests' => Contact::whereDate('created_at', now()->toDateString())->count(),
                'error_rate' => 0,
                'avg_processing_time' => 0,
                'ai_analyzed' => Contact::whereNotNull('ai_analysis')->count(),
                'sentiment_distribution' => [],
                'categories' => []
            ];

            // Получение статистики из базы данных
            $sentiments = Contact::select('sentiment', \Illuminate\Database\Eloquent\Builder::raw('count(*) as count'))
                ->whereNotNull('sentiment')
                ->groupBy('sentiment')
                ->pluck('count', 'sentiment')
                ->toArray();

            $stats['sentiment_distribution'] = $sentiments;

            // Получение категорий из ai_analysis JSON
            $categories = Contact::whereNotNull('ai_analysis')
                ->whereJsonContains('ai_analysis->classification->category', '%')
                ->pluck('ai_analysis')
                ->toArray();

            $categoryCounts = [];
            foreach ($categories as $analysis) {
                if (isset($analysis['classification']['category'])) {
                    $cat = $analysis['classification']['category'];
                    $categoryCounts[$cat] = ($categoryCounts[$cat] ?? 0) + 1;
                }
            }
            $stats['categories'] = $categoryCounts;

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Metrics error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Не удалось получить статистику'
            ], 500);
        }
    }
}
