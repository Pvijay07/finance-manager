<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$expense = \App\Models\Expense::with(['taxes'])->find(31);
if ($expense) {
    $family = \App\Models\Expense::with(['taxes'])->where('expense_number', $expense->expense_number)->get();
    file_put_contents(__DIR__.'/debug-out.json', json_encode([
        'expense' => $expense->toArray(),
        'family' => $family->toArray()
    ], JSON_PRETTY_PRINT));
    echo "OK";
} else {
    echo "NOT FOUND";
}
