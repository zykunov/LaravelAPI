<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

echo "=== API Routes ===\n\n";

foreach (Route::getRoutes()->getIterator() as $route) {
    if (str_starts_with($route->uri(), 'api/')) {
        printf("%-10s %s\n", implode('|', $route->methods()), $route->uri());
    }
}
