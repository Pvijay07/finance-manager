<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;

$companies = Company::where('name', 'like', 'Wit%')->get();
foreach($companies as $company) {
    echo "ID: {$company->id}, Name: {$company->name}\n";
}
