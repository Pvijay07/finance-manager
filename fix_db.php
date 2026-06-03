<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$income = App\Models\Income::find(7);
if ($income && $income->amount == 249.99) {
    $income->amount = 250.00;
    $income->balance_amount = 250.00;
    $income->save();
    
    $gst = $income->taxes()->where('tax_type', 'gst')->first();
    if ($gst) {
        $gst->tax_amount = 41.67;
        $gst->save();
    }
    echo "Fixed Income ID 7 to 250.00\n";
} else {
    echo "Income ID 7 not found or already fixed.\n";
}
