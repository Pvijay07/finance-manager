<?php

use App\Http\Controllers\Api\ExpenseApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\ReportApiController;

Route::middleware('auth:sanctum')->group(function () {
    // Expenses API
    Route::get('/expenses', [ExpenseApiController::class, 'index']);
    Route::post('/expenses', [ExpenseApiController::class, 'store']);
    Route::put('/expenses/{expense}/mark-paid', [ExpenseApiController::class, 'markPaid']);

    // Invoices API
    Route::get('/invoices', [InvoiceApiController::class, 'index']);
    Route::post('/invoices', [InvoiceApiController::class, 'store']);
    Route::put('/invoices/{invoice}/mark-paid', [InvoiceApiController::class, 'markPaid']);

    // Reports API
    Route::get('/reports/profit-loss', [ReportApiController::class, 'profitLoss']);
    Route::get('/reports/expense-breakdown', [ReportApiController::class, 'expenseBreakdown']);
});
