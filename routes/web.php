<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// API Documentation
Route::get('/api/docs', function () {
    return response()->file(base_path('CONTACT_API_README.md'));
});

// Test endpoint
Route::get('/api/test', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'API is running',
        'timestamp' => now()->toDateTimeString()
    ]);
});
