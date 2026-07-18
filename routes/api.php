<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Проверка статуса сервиса
Route::get('/health', [App\Http\Controllers\ContactController::class, 'health']);

// Статистика обращений
Route::get('/metrics', [App\Http\Controllers\ContactController::class, 'metrics']);

// Форма обратной связи
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'store']);
