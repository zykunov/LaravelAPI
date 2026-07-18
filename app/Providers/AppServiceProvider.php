<?php

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Настройка логирования для формы обратной связи
        \Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/contact.log'),
            'level' => 'debug',
        ], 'contact');
    }
}
