<?php

echo "Testing Laravel API routes...\n\n";

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;

echo "Loaded " . Route::getRoutes()->count() . " total routes\n";
echo "API routes:\n";

foreach (Route::getRoutes()->getIterator() as $route) {
    if (str_starts_with($route->uri(), 'api/')) {
        echo sprintf("%-10s %s\n", implode('|', $route->methods()), $route->uri());
    }
}
