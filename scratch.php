<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$expenseId = 38; // or the parent id, wait the screenshot shows bill no #EXP-38 and #EXP-39.
// If EXP-38 is the parent, let's just find EXP-39.
$splits = \App\Models\Expense::whereIn('id', [38, 39])->get();
foreach ($splits as $split) {
    echo "ID: " . $split->id . "\n";
    echo "Planned: " . $split->planned_amount . "\n";
    echo "Actual: " . $split->actual_amount . "\n";
    echo "Status: " . $split->status . "\n";
    echo "Settle Notes: " . $split->settle_notes . "\n";
    echo "Balance Amount: " . $split->balance_amount . "\n";
    echo "------------------\n";
}
