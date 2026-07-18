# Contact Form API - Структура проекта

## Файлы кода:

### Контроллеры
```
app/Http/Controllers/ContactController.php
- POST /api/contact - отправка формы
- GET /api/health - проверка статуса
- GET /api/metrics - статистика
```

### Сервисы
```
app/Services/AiService.php
- analyzeSentiment() - анализ тональности
- classifyRequest() - классификация запросов
- generateResponse() - автогенерация ответа
- analyzeContact() - комплексный анализ

app/Services/RateLimiter.php
- isRateLimited() - проверка лимита
- hit() - запись попытки
- reset() - сброс лимита
```

### Jobs
```
app/Jobs/SendContactEmails.php
- Отправка email администратору
- Отправка копии пользователю
```

### Модели
```
app/Models/Contact.php
```

### Миграции
```
database/migrations/2026_07_17_084321_create_contacts_table.php
```

### Views (Email шаблоны)
```
resources/views/emails/contact_admin.blade.php
resources/views/emails/contact_user.blade.php
```

### Routes
```
routes/api.php - API роуты
bootstrap/app.php - конфигурация приложения (добавлен api роут)
```

### Config
```
config/services.php - настройки OpenAI и Rate Limiter
config/logging.php - настройка логирования
```

## Установка и настройка

### 1. Установка зависимостей
```bash
composer require openai-php/client
```

### 2. Настройка переменных окружения (.env)

```env
# Rate limiting
CONTACT_RATE_LIMIT=5
CONTACT_RATE_LIMIT_DECAY=15

# OpenAI (опционально)
OPENAI_ENABLED=false
OPENAI_API_KEY=your_api_key_here
OPENAI_MODEL=gpt-3.5-turbo

# Mail
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
```

### 3. Миграции

```bash
php artisan migrate:fresh --force
```

## API Эндпоинты

### 1. POST /api/contact

**Request:**
```json
{
  "name": "Иван Иванов",
  "phone": "+79001234567",
  "email": "user@example.com",
  "comment": "Ваш комментарий здесь..."
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Обращение успешно отправлено",
  "data": {...},
  "ai_enabled": false
}
```

**Errors:**
- 422 - Валидация не прошла
- 429 - Превышен лимит запросов
- 500 - Ошибка сервера

### 2. GET /api/health

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2026-07-17 12:00:00",
  "ai": {
    "enabled": false,
    "status": "disabled",
    "error": null
  },
  "services": {
    "mail": "log",
    "cache": "database",
    "queue": "database"
  }
}
```

### 3. GET /api/metrics

**Response:**
```json
{
  "success": true,
  "data": {
    "total_requests": 125,
    "today_requests": 23,
    "ai_analyzed": 0,
    "sentiment_distribution": {
      "positive": 45,
      "neutral": 70,
      "negative": 10
    },
    "categories": {
      "question": 60,
      "feedback": 50,
      "complaint": 15
    }
  }
}
```

## AI-функции

### 1. Анализ тональности
```php
$sentiment = $aiService->analyzeSentiment($text);
// Returns: ['sentiment' => 'positive|neutral|negative', 'score' => -100..100]
```

### 2. Классификация запросов
```php
$classification = $aiService->classifyRequest($text, $comment);
// Returns: ['category' => 'question|complaint|feedback', 'urgency' => 'low|medium|high']
```

### 3. Автогенерация ответа
```php
$response = $aiService->generateResponse($text, $type);
// Returns: string
```

## Rate Limiting

**Default:** 5 запросов за 15 минут

**Headers:**
- X-RateLimit-Remaining
- X-RateLimit-Reset
- Retry-After

## Логирование

**Log file:** `storage/logs/contact.log`

**Log format:**
```json
{
  "name": "Иван Иванов",
  "phone": "+79001234567",
  "email": "user@example.com",
  "comment": "Текст комментария",
  "ip": "127.0.0.1",
  "user_agent": "Mozilla/5.0...",
  "timestamp": "2026-07-17 12:00:00"
}
```

## База данных

### Таблица contacts

| Column | Type |
|--------|------|
| id | bigint |
| name | string |
| phone | string |
| email | string (nullable) |
| comment | text |
| ip_address | string (nullable) |
| user_agent | text (nullable) |
| ai_analysis | json (nullable) |
| sentiment | string (nullable) |
| meta | json (nullable) |
| created_at | timestamp |
| updated_at | timestamp |

## Graceful Fallback

Если OpenAI недоступен:
1. Сервис продолжает работать
2. AI-анализ пропускается
3. Данные сохраняются без анализа
4. Поля ai_analysis и sentiment остаются null


## Дополнительные файлы

- `CONTACT_API_README.md` - Полная документация API
- `DEPLOYMENT.md` - Документация по развертыванию
- `test_contact.json` - Тестовые данные
- `test_contact_2.json` - Тестовые данные (вопрос)
- `test_contact_3.json` - Тестовые данные (жалоба)

## Шаги после установки

1. Установить OpenAI API ключ в .env
2. Запустить тесты
3. Настроить почтовый сервер (если нужна реальная отправка email)
4. Настроить кеширование (Redis/Memcached)

