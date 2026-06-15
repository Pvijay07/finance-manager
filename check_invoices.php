<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;

$invoices = Invoice::where('invoice_number', 'like', 'WIT-26-27-PRO-%')->get(['id', 'invoice_number', 'company_id']);
foreach($invoices as $inv) {
    echo "ID: {$inv->id}, Number: {$inv->invoice_number}, Company ID: {$inv->company_id}\n";
}
