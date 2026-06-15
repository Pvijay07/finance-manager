<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$expenseId = 38;
$expense = \App\Models\Expense::with(['parent', 'taxes'])->findOrFail($expenseId);
$root = $expense;
while ($root->parent_id) {
    $root = \App\Models\Expense::with('taxes')->findOrFail($root->parent_id);
}
echo "Root ID: " . $root->id . "\n";
echo "Root Planned: " . $root->planned_amount . "\n";
echo "Root Actual: " . $root->actual_amount . "\n";
echo "Root Taxes: " . json_encode($root->taxes) . "\n";
