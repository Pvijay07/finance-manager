<?php
// Load Laravel's autoloader and app
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Clear caches
\Artisan::call('view:clear');
\Artisan::call('cache:clear');

echo "Cache cleared successfully!";
