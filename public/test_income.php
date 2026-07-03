<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$incomes = \App\Models\Income::orderBy('id', 'desc')->take(10)->get(['id', 'amount', 'actual_amount', 'planned_amount', 'balance_amount', 'received_amount', 'original_amount', 'status', 'is_partial', 'parent_id', 'created_at', 'updated_at']);
foreach($incomes as $inc) {
    echo json_encode($inc) . "\n";
}
