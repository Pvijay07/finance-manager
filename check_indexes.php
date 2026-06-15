<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$indexes = DB::select("SHOW INDEX FROM invoices");
foreach($indexes as $index) {
    echo "Key: {$index->Key_name}, Column: {$index->Column_name}, Non_unique: {$index->Non_unique}\n";
}
