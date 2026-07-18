<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Новое обращение</title>
</head>
<body>
    <h2>Новое обращение с формы обратной связи</h2>
    
    <p><strong>Имя:</strong> {{ $data['name'] }}</p>
    <p><strong>Телефон:</strong> {{ $data['phone'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    <p><strong>Комментарий:</strong></p>
    <p>{{ $data['comment'] }}</p>
    
    @if($ai_response)
    <hr>
    <h3>AI-анализ обращения:</h3>
    <p><strong>Тональность:</strong> {{ $ai_response['sentiment']['sentiment'] ?? 'не определена' }}</p>
    <p><strong>Категория:</strong> {{ $ai_response['classification']['category'] ?? 'не определена' }}</p>
    <p><strong>Срочность:</strong> {{ $ai_response['classification']['urgency'] ?? 'не определена' }}</p>
    <p><strong>Рекомендуемый ответ:</strong></p>
    <div style="background: #f5f5f5; padding: 10px; border-radius: 4px;">
        {{ $ai_response['auto_response'] ?? 'Не удалось сгенерировать ответ' }}
    </div>
    @endif
    
    <hr>
    <p><small>Обращение логировано в системе. Время: {{ now()->toDateTimeString() }}</small></p>
</body>
</html>
