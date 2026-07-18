<?php

namespace App\Services;

use Exception;

class AiService
{
    protected ?\OpenAIClient $client;
    protected bool $enabled;
    protected string $model;

    public function __construct()
    {
        $this->enabled = config('services.openai.enabled', false);
        $this->model = config('services.openai.model', 'gpt-3.5-turbo');

        if ($this->enabled && config('services.openai.api_key')) {
            try {
                $this->client = new \OpenAI\Client(config('services.openai.api_key'));
            } catch (Exception $e) {
                $this->client = null;
            }
        } else {
            $this->client = null;
        }
    }

    /**
     * Анализ тональности комментария
     */
    public function analyzeSentiment(string $text): ?array
    {
        if (!$this->canUseAI()) {
            return null;
        }

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Ты - эксперт по анализу текстов. Ответь в формате JSON: {"sentiment": "positive|neutral|negative|mixed", "score": -100 до 100, "keywords": []}'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Проанализируй тональность этого текста: {$text}"
                    ]
                ],
                'temperature' => 0.3
            ]);

            $content = $response->choices[0]->message->content;
            $data = json_decode($content, true);

            return $data ?? [
                'sentiment' => 'neutral',
                'score' => 0,
                'keywords' => []
            ];
        } catch (Exception $e) {
            \Log::warning('AI Sentiment Analysis failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Классификация типа запроса
     */
    public function classifyRequest(string $text, string $comment = ''): ?array
    {
        if (!$this->canUseAI()) {
            return null;
        }

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Классифицируй запрос. Ответь в формате JSON: {"category": "question|complaint|feedback|support|other", " urgency": "low|medium|high", "summary": "краткое описание"}'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Тип запроса: {$text} {$comment}"
                    ]
                ],
                'temperature' => 0.3
            ]);

            $content = $response->choices[0]->message->content;
            $data = json_decode($content, true);

            return $data ?? [
                'category' => 'other',
                'urgency' => 'medium',
                'summary' => substr($text, 0, 100)
            ];
        } catch (Exception $e) {
            \Log::warning('AI Request Classification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Автоматическая генерация ответа
     */
    public function generateResponse(string $text, string $type = 'general'): ?string
    {
        if (!$this->canUseAI()) {
            return null;
        }

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Ты - профессиональный менеджер по работе с клиентами. Напиши вежливый и краткий ответ на обращение клиента. Не добавляй приветствия и подписи.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Ответь на обращение клиента:\n{$text}\nТип обращения: {$type}"
                    ]
                ],
                'temperature' => 0.7
            ]);

            return $response->choices[0]->message->content ?? null;
        } catch (Exception $e) {
            \Log::warning('AI Response Generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Комплексный анализ обращения
     */
    public function analyzeContact(string $name, string $email, string $phone, string $comment): array
    {
        $aiAnalysis = null;

        if ($this->canUseAI() && !empty($comment)) {
            $sentiment = $this->analyzeSentiment($comment);
            $classification = $this->classifyRequest($comment);

            $aiAnalysis = [
                'sentiment' => $sentiment,
                'classification' => $classification,
                'auto_response' => $this->generateResponse($comment, $classification['category'] ?? 'general')
            ];
        }

        return [
            'is_ai_enabled' => $this->enabled,
            'ai_analysis' => $aiAnalysis,
            'fallback' => !$this->canUseAI()
        ];
    }

    /**
     * Проверка доступности AI
     */
    protected function canUseAI(): bool
    {
        return $this->enabled && $this->client !== null;
    }

    /**
     * Проверка включено ли AI
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
