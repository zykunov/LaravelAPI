<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('api health returns healthy status', function () {
    $response = $this->getJson('/api/health');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'timestamp',
        'ai' => [
            'enabled',
            'status',
            'error'
        ],
        'services' => [
            'mail',
            'cache',
            'queue'
        ]
    ]);
});

test('api metrics returns statistics', function () {
    $response = $this->getJson('/api/metrics');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'total_requests',
            'today_requests',
            'sentiment_distribution',
            'categories'
        ]
    ]);
});

test('contact form validates required fields', function () {
    $response = $this->postJson('/api/contact', []);

    $response->assertStatus(422);
    $response->assertJsonStructure([
        'error',
        'details'
    ]);
});

test('contact form requires valid comment', function () {
    $response = $this->postJson('/api/contact', [
        'name' => 'Test',
        'phone' => '1234567890',
        'comment' => 'Short'
    ]);

    $response->assertStatus(422);
});

test('contact form accepts valid data', function () {
    $response = $this->postJson('/api/contact', [
        'name' => 'Тест Иванов',
        'phone' => '+79001234567',
        'email' => 'test@example.com',
        'comment' => 'Это тестовое сообщение для проверки работы API формы обратной связи.'
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'data'
    ]);
});
