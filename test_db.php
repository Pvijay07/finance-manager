<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$incomes = App\Models\Income::where('party_name', 'like', '%nikhitha%')->get();
foreach ($incomes as $inc) {
    echo "ID: {$inc->id} | Name: {$inc->party_name} | Amount: {$inc->amount} | Actual: {$inc->actual_amount} | Status: {$inc->status}\n";
    $taxes = $inc->taxes;
    foreach ($taxes as $tax) {
        echo "  Tax: {$tax->tax_type} | Amount: {$tax->tax_amount} | Taxable: {$tax->taxable_amount}\n";
    }
}
