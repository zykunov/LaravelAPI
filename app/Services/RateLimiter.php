<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Exception;

class RateLimiter
{
    protected int $maxAttempts;
    protected int $decayMinutes;

    public function __construct()
    {
        $this->maxAttempts = config('services.rate_limit.max_attempts', 5);
        $this->decayMinutes = config('services.rate_limit.decay_minutes', 15);
    }

    /**
     * Проверка, превысил ли пользователь лимит
     */
    public function isRateLimited(string $key): bool
    {
        $attempts = $this->getAttempts($key);
        
        return $attempts >= $this->maxAttempts;
    }

    /**
     * Запись попытки
     */
    public function hit(string $key): void
    {
        $attempts = $this->getAttempts($key);
        $attempts++;
        
        Cache::put("rate_limit.{$key}", $attempts, $this->decayMinutes * 60);
        
        // Дополнительная запись в файл для надежности
        $this->writeToFile($key, $attempts);
    }

    /**
     * Получение количества попыток
     */
    protected function getAttempts(string $key): int
    {
        $cached = Cache::get("rate_limit.{$key}");
        
        if ($cached !== null) {
            return (int) $cached;
        }

        // Если в кеше нет, проверяем файл
        return $this->readFromFile($key);
    }

    /**
     * Запись в файл (резервный метод)
     */
    protected function writeToFile(string $key, int $attempts): void
    {
        try {
            $file = storage_path("app/rate_limit/{$key}.json");
            $data = [
                'attempts' => $attempts,
                'first_attempt' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];
            
            // Создаем папку если нет
            $dir = storage_path('app/rate_limit');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            
            file_put_contents($file, json_encode($data));
        } catch (Exception $e) {
            //quiet fail
        }
    }

    /**
     * Чтение из файла (резервный метод)
     */
    protected function readFromFile(string $key): int
    {
        try {
            $file = storage_path("app/rate_limit/{$key}.json");
            
            if (!file_exists($file)) {
                return 0;
            }

            $data = json_decode(file_get_contents($file), true);
            $firstAttempt = $data['first_attempt'] ?? null;
            
            if ($firstAttempt) {
                $firstDate = \DateTime::createFromFormat('Y-m-d H:i:s', $firstAttempt);
                $now = new \DateTime();
                $diff = $firstDate->diff($now);
                
                // Если прошло больше времени, чем decayMinutes, сбрасываем счетчик
                if ($diff->days * 24 * 60 + $diff->h * 60 + $diff->i > $this->decayMinutes) {
                    return 0;
                }
            }

            return (int) ($data['attempts'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Сброс лимита для ключа
     */
    public function reset(string $key): void
    {
        Cache::forget("rate_limit.{$key}");
        
        try {
            $file = storage_path("app/rate_limit/{$key}.json");
            if (file_exists($file)) {
                unlink($file);
            }
        } catch (Exception $e) {
            //quiet fail
        }
    }

    /**
     * Получение оставшихся попыток
     */
    public function getRemaining(string $key): int
    {
        $attempts = $this->getAttempts($key);
        
        return max(0, $this->maxAttempts - $attempts);
    }

    /**
     * Получить время до сброса
     */
    public function getResetTime(string $key): int
    {
        $cached = Cache::get("rate_limit.{$key}");
        $firstAttempt = null;

        if ($cached !== null) {
            $file = storage_path("app/rate_limit/{$key}.json");
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
                $firstAttempt = $data['first_attempt'] ?? null;
            }
        }

        if ($firstAttempt) {
            $firstDate = \DateTime::createFromFormat('Y-m-d H:i:s', $firstAttempt);
            $resetTime = clone $firstDate;
            $resetTime->modify("{$this->decayMinutes} minutes");
            $now = new \DateTime();
            
            $diff = $now->diff($resetTime);
            $seconds = $diff->days * 24 * 60 * 60 + $diff->h * 60 * 60 + $diff->i * 60 + $diff->s;
            
            return max(0, $seconds);
        }

        return $this->decayMinutes * 60;
    }
}
