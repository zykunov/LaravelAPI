# Contact Form API Documentation

## Overview
REST API для формы обратной связи с AI-интеграцией, rate limiting и логированием.

## Endpoints

### 1. POST /api/contact
Отправка формы обратной связи.

**Request:**
```json
{
  "name": "Иван Иванов",
  "phone": "+79001234567",
  "email": "user@example.com",
  "comment": "Ваш комментарий здесь..."
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `phone`: required, string, max:20
- `email`: nullable, email, max:255
- `comment`: required, string, min:10, max:5000

**Response (Success 200):**
```json
{
  "success": true,
  "message": "Обращение успешно отправлено",
  "data": {
    "name": "Иван Иванов",
    "phone": "+79001234567",
    "email": "user@example.com",
    "comment": "Ваш комментарий здесь...",
    "ip": "127.0.0.1",
    "user_agent": "Mozilla/5.0...",
    "timestamp": "2026-07-17 12:00:00"
  },
  "ai_enabled": false
}
```

**Errors:**
- 422 - Invalid input data
- 429 - Rate limit exceeded
- 500 - Server error

---

### 2. GET /api/health
Проверка статуса сервиса.

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

---

### 3. GET /api/metrics
Статистика обращений.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_requests": 125,
    "today_requests": 23,
    "error_rate": 0,
    "avg_processing_time": 0,
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

---

## AI Integration

### Features:
1. **Sentiment Analysis** - Анализ тональности комментария
2. **Request Classification** - Классификация типов запросов
3. **Auto Response Generation** - Автоматическая генерация ответа

### Configuration (.env):
```env
OPENAI_ENABLED=false
OPENAI_API_KEY=your_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
```

### Fallback Behavior:
Если AI недоступен, сервис продолжает работать в штатном режиме без AI-анализа.

---

## Rate Limiting

### Default Configuration:
- Max attempts: 5
- Decay time: 15 minutes

### Configuration (.env):
```env
CONTACT_RATE_LIMIT=5
CONTACT_RATE_LIMIT_DECAY=15
```

### Headers (on rate limit):
- `X-RateLimit-Remaining`: Оставшиеся попытки
- `X-RateLimit-Reset`: Время сброса (секунды)
- `Retry-After`: Время ожидания (секунды)

---

## Email Notifications

### Emails Sent:
1. **Administrator Email** - Новое обращение с данными и AI-анализом
2. **User Copy** - Копия письма пользователю

### Configuration (.env):
```env
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_FROM_ADDRESS=hello@example.com
```

---

## Logging

### Log File:
`storage/logs/contact.log`

### Log Format:
```json
{
  "name": "Иван Иванов",
  "phone": "+79001234567",
  "email": "user@example.com",
  "comment": "Ваш комментарий...",
  "ip": "127.0.0.1",
  "user_agent": "Mozilla/5.0...",
  "timestamp": "2026-07-17 12:00:00"
}
```

---

## Database Schema

### Contacts Table:
- id
- name (string)
- phone (string)
- email (nullable string)
- comment (text)
- ip_address (nullable string)
- user_agent (nullable text)
- ai_analysis (nullable JSON)
- sentiment (nullable string)
- meta (nullable JSON)
- created_at
- updated_at

---

## Testing

### Using cURL:
```bash
# Test contact form
curl -X POST http://localhost/api/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Иван Иванов",
    "phone": "+79001234567",
    "email": "user@example.com",
    "comment": "Тестовое сообщение для проверки работы API."
  }'

# Health check
curl http://localhost/api/health

# Metrics
curl http://localhost/api/metrics
```

### Using JavaScript (fetch):
```javascript
fetch('/api/contact', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    name: 'Иван Иванов',
    phone: '+79001234567',
    email: 'user@example.com',
    comment: 'Тестовое сообщение'
  })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

---

## Environment Variables

Complete .env configuration:
```env
# Application
APP_NAME=Laravel
APP_ENV=local
APP_KEY=your_app_key
APP_DEBUG=true
APP_URL=http://localhost

# Contact Form
CONTACT_RATE_LIMIT=5
CONTACT_RATE_LIMIT_DECAY=15

# OpenAI (optional)
OPENAI_ENABLED=false
OPENAI_API_KEY=your_api_key_here
OPENAI_MODEL=gpt-3.5-turbo

# Mail
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Project Structure
```
app/
├── Http/
│   └── Controllers/
│       └── ContactController.php
├── Services/
│   ├── AiService.php
│   └── RateLimiter.php
├── Jobs/
│   └── SendContactEmails.php
└── Models/
    └── Contact.php

resources/
└── views/
    └── emails/
        ├── contact_admin.blade.php
        └── contact_user.blade.php

routes/
└── api.php

config/
├── services.php
└── logging.php
```

---

## Security Features
- Input validation
- Rate limiting (prevents spam)
- IP tracking
- User agent logging
- AI analysis (optional)

---

## Future Enhancements
- Webhook support
- SMS notifications
- Email queue processing
- AI model fine-tuning
- Analytics dashboard
- CSV export
