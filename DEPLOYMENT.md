# REST API для формы обратной связи

## Запущено и готово к работе

Созданы следующие компоненты:

### Эндпоинты:
- `POST /api/contact` - отправка формы обратной связи
- `GET /api/health` - проверка статуса сервиса
- `GET /api/metrics` - статистика обращений
- `GET /api/test` - тестовый эндпоинт

### AI-интеграция:
- Анализ тональности комментария
- Классификация типов запросов
- Автоматическая генерация ответа
- Graceful fallback (работает без AI при отсутствии ключа)

### Дополнительные функции:
- Rate limiting (5 запросов за 15 минут)
- Логирование в файл `storage/logs/contact.log`
- Сохранение в базу данных `contacts`
- Отправка email-уведомлений

## База данных

Таблица `contacts` создана с полями:
- name, phone, email, comment
- ip_address, user_agent
- ai_analysis (JSON)
- sentiment, meta (JSON)

## Миграции запущены успешно ✓

## Переменные окружения (.env)

```env
# Rate limiting
CONTACT_RATE_LIMIT=5
CONTACT_RATE_LIMIT_DECAY=15

# OpenAI (опционально - для AI функций)
OPENAI_ENABLED=false
OPENAI_API_KEY=your_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
```

## Примеры запросов

### 1. Отправка обращения:
```bash
curl -X POST http://localhost/api/contact \
  -H "Content-Type: application/json" \
  -d @test_contact.json
```

### 2. Проверка статуса:
```bash
curl http://localhost/api/health
```

### 3. Статистика:
```bash
curl http://localhost/api/metrics
```

## Структура проекта

```
app/
├── Http/Controllers/ContactController.php
├── Services/
│   ├── AiService.php (AI интеграция)
│   └── RateLimiter.php (защита от спама)
├── Jobs/
│   └── SendContactEmails.php
└── Models/
    └── Contact.php

resources/views/emails/
├── contact_admin.blade.php
└── contact_user.blade.php

routes/api.php
```

## Документация

Полная документация в файле `CONTACT_API_README.md`
