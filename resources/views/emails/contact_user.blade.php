<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ваше обращение принято</title>
</head>
<body>
    <h2>Спасибо за обращение!</h2>
    
    <p>Мы получили ваше сообщение и ответим вам в ближайшее время.</p>
    
    <h3>Ваши данные:</h3>
    <p><strong>Имя:</strong> {{ $data['name'] }}</p>
    <p><strong>Телефон:</strong> {{ $data['phone'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    <p><strong>Комментарий:</strong></p>
    <p>{{ $data['comment'] }}</p>
    
    <p>Мы ценим ваше обращение и сделаем всё возможное, чтобы помочь вам.</p>
    
    <hr>
    <p><small>Время отправки: {{ now()->toDateTimeString() }}</small></p>
</body>
</html>
