<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InvoiceManagementController;
use App\Http\Controllers\Admin\StandardExpensesController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExpenseTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\CA\StatementController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\InvoiceController;
use App\Http\Controllers\Manager\ExpensesController;
use App\Http\Controllers\Manager\ExpenseTypeController as ManagerExpenseTypeController;
use App\Http\Controllers\Manager\IncomeController;
use App\Http\Controllers\Manager\LoansController;
use App\Http\Controllers\Manager\NonStandardExpensesController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\Manager\StandardExpensesController as ManagerStandardExpensesController;
use App\Http\Controllers\Manager\SalaryController;
use App\Http\Controllers\CA\DashboardController as CADashboardController;
use App\Http\Controllers\CA\InvoiceController as CAInvoiceController;
use App\Http\Controllers\Manager\GstController;
use App\Http\Controllers\Manager\TDSController;

// Authentication Routes
Route::get('/login', function () {
    return redirect('/manager/login');
})->name('login'); // Fallback to manager

Route::get('/{role}/login', [LoginController::class, 'showLoginForm'])
    ->where('role', 'admin|manager|ca')
    ->name('role.login');
Route::post('/{role}/login', [LoginController::class, 'login'])
    ->where('role', 'admin|manager|ca');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');

        Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
        Route::post('/companies/settings', [CompanyController::class, 'updateSettings'])->name('companies.settings.update');

        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');

        Route::get('/expensetypes', [ExpenseTypeController::class, 'index'])->name('expensetypes');
        Route::post('/expensetypes', [ExpenseTypeController::class, 'store'])->name('expensetypes.store');

        Route::get('expensetypes/{id}/edit', [ExpenseTypeController::class, 'edit'])->name('admin.expensetypes.edit');
        Route::put('expensetypes/{id}', [ExpenseTypeController::class, 'update'])->name('admin.expensetypes.update');

        Route::get('/standard-expenses', [StandardExpensesController::class, 'index'])->name('standard-expenses');
        Route::post('/standard-expenses/store', [StandardExpensesController::class, 'store'])->name('standard-expenses.store');
        Route::get('/standard-expenses/{id}', [StandardExpensesController::class, 'show'])->name('standard-expenses.show');
        Route::put('/standard-expenses/{id}', [StandardExpensesController::class, 'update'])->name('standard-expenses.update');
        Route::delete('/standard-expenses/{id}', [StandardExpensesController::class, 'destroy'])->name('standard-expenses.destroy');
        Route::post('/generate-expenses', [StandardExpensesController::class, 'generateExpenses'])->name('generate-expenses');
        Route::post('/standard-expenses/get-categories', [StandardExpensesController::class, 'getCategories'])->name('standard-expenses.get-categories');
        Route::get('/standard-expenses/{id}/taxes', [StandardExpensesController::class, 'getTaxDetails']);
        Route::post('/taxes/{id}/pay', [StandardExpensesController::class, 'markTaxAsPaid']);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');

        Route::prefix('invoices')->group(function () {
            Route::get('/', [InvoiceManagementController::class, 'index'])->name('invoices');
            Route::post('/', [InvoiceManagementController::class, 'store'])->name('invoices.store');
            Route::post('/partial-payment', [InvoiceManagementController::class, 'processPartialPayment'])->name('invoices.partial-payment');
            Route::get('/{id}', [InvoiceManagementController::class, 'getInvoiceDetails'])->name('invoices.details');
        });
        Route::put('/invoices/{id}/update', [InvoiceManagementController::class, 'update'])->name('invoices.update');
        Route::get('/invoices/{id}/view', [InvoiceManagementController::class, 'view'])->name('invoices.view');
        Route::get('/invoices/{id}/download', [InvoiceManagementController::class, 'download'])->name('invoices.download');
        Route::post('/invoices/send-email', [InvoiceManagementController::class, 'sendEmail'])->name('invoices.send-email');
        Route::get('/invoices/{id}/edit', [InvoiceManagementController::class, 'edit'])->name('invoices.edit');


        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
        Route::post('/activity-logs/clear', [ActivityLogController::class, 'clear'])->name('activity-logs.clear');
        Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');

            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/role-permissions', [UserController::class, 'saveRolePermissions'])->name('users.role-permissions');
            Route::get('/role-permissions/{role}', [UserController::class, 'getUserPermissions'])->name('users.get-role-permissions');
        });
        Route::get('/system-settings', [SystemSettingsController::class, 'index'])->name('system-settings');

        Route::post('/system-settings/save', [SystemSettingsController::class, 'save'])->name('settings.save');
        Route::post('/system-settings/test-email', [SystemSettingsController::class, 'testEmail'])->name('settings.test-email');
        Route::post('/system-settings/backup/run', [SystemSettingsController::class, 'runBackup'])->name('settings.backup.run');
        Route::get('/system-settings/backup/download', [SystemSettingsController::class, 'downloadBackup'])->name('settings.backup.download');
        Route::post('/system-settings/clear-cache', [SystemSettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/system-settings/optimize-db', [SystemSettingsController::class, 'optimizeDatabase'])->name('settings.optimize-db');
        Route::post('/system-settings/clear-logs', [SystemSettingsController::class, 'clearLogs'])->name('settings.clear-logs');

        // Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories/save', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/settings', [CategoryController::class, 'saveSettings'])->name('settings.save');
        Route::post('/categories/reset', [CategoryController::class, 'resetToDefaults'])->name('categories.reset');
        // routes/web.php (admin section)
        Route::post('/categories/assign', [CategoryController::class, 'assign'])
            ->name('categories.assign');
        Route::post('/categories/bulk-update', [CategoryController::class, 'bulkUpdate'])->name('categories.bulk-update');
    });



// Manager Routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('manager.dashboard');

    // Expenses
    Route::get('/expenses', [ExpensesController::class, 'index'])->name('manager.expenses');
    Route::post('/expenses', [ExpensesController::class, 'store'])->name('manager.expenses.store');
    Route::post('/expenses/{expense}/mark-paid', [ExpensesController::class, 'markAsPaid'])->name('manager.expenses.mark-paid');
    Route::get('/standard-expenses', [ManagerStandardExpensesController::class, 'index'])->name('manager.standard-expenses');
    Route::get('/expensetypes', [ManagerExpenseTypeController::class, 'index'])->name('expense-types.index');
    Route::get('/expenses/{expense}/edit', [ExpensesController::class, 'edit'])->name('manager.expenses.edit');
    Route::get('/expense/{id}/view', [ExpensesController::class, 'showExpense'])->name('manager.expense.view');
    Route::put('/expenses/{id}', [ExpensesController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{id}', [ExpensesController::class, 'destroy'])->name('expenses.destroy');
    Route::get('/expenses/{id}/receipts', [ExpensesController::class, 'getReceipts'])->name('expenses.receipts');
    Route::delete('/receipts/{id}', [ExpensesController::class, 'deleteReceipt'])->name('expenses.receipts');
    Route::post('/gst/attach-receipt', [GstController::class, 'attachReceipt'])->name('manager.gst.attach-receipt');
    Route::get('/gst-collected/export/{type}', [GstController::class, 'exportGstCollected'])->name('manager.gst-collected.export');
    Route::get('/taxes/export/{type}', [GstController::class, 'exportTaxes'])->name('manager.taxes.export');
    Route::get('/gst/export/{type}', [GstController::class, 'exportTaxes'])->name('manager.taxes.export');

    Route::get('/expenses/{id}/split-history', [ExpensesController::class, 'splitHistory'])->name('manager.expenses.split');
    Route::get('/income/{id}/split-history', [IncomeController::class, 'splitHistory'])->name('manager.income.split');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('manager.invoices');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('manager.invoices.store');
    Route::put('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('manager.invoices.mark-paid');
    Route::get('/invoices/{id}', [IncomeController::class, 'getInvoiceDetails'])->name('invoices.view');

    // Standard Expenses Routes
    Route::prefix('standard-expenses')->group(function () {
        Route::get('/', [ManagerStandardExpensesController::class, 'index'])->name('standard-expenses.index');
        Route::post('/generate', [ManagerStandardExpensesController::class, 'generateExpenses'])->name('standard-expenses.generate');
        Route::post('/{id}/update-amount', [ManagerStandardExpensesController::class, 'updateAmount'])->name('standard-expenses.update-amount');
        Route::post('/{id}/mark-paid', [ManagerStandardExpensesController::class, 'markAsPaid'])->name('standard-expenses.mark-paid');
        Route::post('/{id}/settle', [ManagerStandardExpensesController::class, 'settleBalance'])->name('standard-expenses.settle');
        Route::post('/{id}/keep-balance', [ManagerStandardExpensesController::class, 'keepBalance'])->name('standard-expenses.keep-balance');
        Route::get('/logs', [ManagerStandardExpensesController::class, 'viewLogs'])->name('standard-expenses.logs');
    });

    // Non-Standard Expenses Routes
    Route::prefix('non-standard-expenses')->group(function () {
        Route::get('/', [NonStandardExpensesController::class, 'index'])->name('non-standard-expenses.index');
        Route::post('/', [NonStandardExpensesController::class, 'store'])->name('non-standard-expenses.store');
        Route::get('/{id}/edit', [NonStandardExpensesController::class, 'edit'])->name('non-standard-expenses.edit');
        Route::post('/{id}', [NonStandardExpensesController::class, 'update'])->name('non-standard-expenses.update');
        Route::post('/{id}/mark-paid', [NonStandardExpensesController::class, 'markAsPaid'])->name('non-standard-expenses.mark-paid');
        Route::delete('/{id}', [NonStandardExpensesController::class, 'destroy'])->name('non-standard-expenses.destroy');
    });

    Route::prefix('income')->group(function () {
        Route::get('/', [IncomeController::class, 'index'])->name('income.index');
        Route::post('/', [IncomeController::class, 'store'])->name('income.store');
        Route::put('/{id}', [IncomeController::class, 'update'])->name('income.update');
        Route::delete('/{id}', [IncomeController::class, 'destroy'])->name('income.destroy');
        Route::post('/{id}/mark-received', [IncomeController::class, 'markAsReceived'])->name('income.mark-received');
        Route::get('/{id}/edit', [IncomeController::class, 'edit'])->name('income.edit');

        Route::get('/upcoming', [IncomeController::class, 'upcoming'])->name('income.upcoming');
        Route::get('/balances', [IncomeController::class, 'balance'])->name('income.balance');
    });
    // Income routes
    Route::get('/income/{id}/view', [IncomeController::class, 'showIncome'])->name('manager.income.view');
    Route::get('/income/{id}/details', [IncomeController::class, 'getIncomeDetails'])->name('manager.income.details');
    Route::post('/income/{id}/receive-payment', [IncomeController::class, 'receivePayment'])->name('manager.income.receive-payment');
    Route::get('/income/{id}/edit', [IncomeController::class, 'edit'])->name('manager.income.edit');
    Route::post('/income', [IncomeController::class, 'store'])->name('manager.income.store');
    Route::put('/income/{id}', [IncomeController::class, 'update'])->name('manager.income.update');
    Route::get('/income/{id}/download', [IncomeController::class, 'downloadFromIncome'])->name('manager.income.download');
    Route::post('/income/send-email', [IncomeController::class, 'sendEmail'])->name('income.send-email');
    Route::get('/balances', [IncomeController::class, 'index'])->name('manager.balances.index');
    Route::get('/companies/{id}/dues-details', [IncomeController::class, 'companyDuesDetails']);
    Route::get('/companies/{id}/balance-summary', [IncomeController::class, 'balanceSummary']);
    Route::get('/companies/{id}', [IncomeController::class, 'viewCompanyDetails'])->name('manager.companies.show');
    Route::get('/transactions', [IncomeController::class, 'viewTransactions'])->name('manager.transactions');
    Route::post('/settlements', [IncomeController::class, 'storeSettlement'])->name('manager.settlements.store');
    Route::get('/balances/export', [IncomeController::class, 'export'])->name('manager.balances.export');
    Route::get('/reports', [ReportController::class, 'index'])->name('manager.reports');
    Route::get('/manager/getIncome/{id}', [IncomeController::class, 'getIncomeDetails']);
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('manager.reports.export.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('manager.reports.export.pdf');

    Route::get('/manager/expenses/summary', [ExpensesController::class, 'getSummary'])->name('manager.expenses.summary');
    Route::get('/manager/expenses/table', [ExpensesController::class, 'getTable'])->name('manager.expenses.table');
    Route::get('/gst', [GstController::class, 'index'])->name('manager.gst');
    Route::get('/gst-collected', [GstController::class, 'gstCollected'])->name('manager.gst-collected');
    Route::get('/gst-settlements', [GstController::class, 'settlement'])->name('manager.gst-settlements');
    Route::get('/gst-returns', [GstController::class, 'returns'])->name('manager.gst-returns');

    Route::get('/expense-taxes', [GstController::class, 'taxes'])->name('manager.taxes');
    Route::post('/filter', [GSTController::class, 'filter'])->name('filter');
    Route::post('/invoices/filter', [GSTController::class, 'filterInvoices'])->name('invoices.filter');
    Route::post('/taxes/filter', [GSTController::class, 'filterTaxes'])->name('taxes.filter');
    Route::get('/tds', [TDSController::class, 'index'])->name('manager.tds');
    Route::get('/tds/expense', [TDSController::class, 'tdsExpense'])->name('manager.tdsExpense');
    Route::get('/gst-settlements', [GstController::class, 'settlement'])->name('manager.gst-settlements');
    Route::post('/gst/settlement/store', [GstController::class, 'storeSettlement'])->name('manager.gst.settlement.store');
    Route::get('/gst/settlement/{id}', [GstController::class, 'showSettlement'])->name('manager.gst.settlement.show');
    Route::get('/gst/settlement/export', [GstController::class, 'exportSettlements'])->name('manager.gst.settlement.export');

    Route::post('/gst/task/store', [GstController::class, 'storeTask'])->name('manager.gst.task.store');
    Route::post('/gst/task/{id}/update-status', [GstController::class, 'updateTaskStatus'])->name('manager.gst.task.update-status');
    Route::post('/gst/task/send-reminders', [GstController::class, 'sendReminders'])->name('manager.gst.task.send-reminders');
    Route::get('/gst/task/export', [GstController::class, 'exportTasks'])->name('manager.gst.task.export');
    // Actions
    Route::post('/attach-invoice', [GSTController::class, 'attachInvoice'])->name('attach-invoice');
    Route::post('/taxes/store', [GSTController::class, 'storeTaxEntry'])->name('taxes.store');
    Route::post('/sync-invoices', [GSTController::class, 'syncInvoices'])->name('sync-invoices');
    Route::post('/sync-taxes', [GSTController::class, 'syncTaxes'])->name('taxes.sync');

    // Export routes
    Route::get('/export/{type}', [GSTController::class, 'export'])->name('export');
    Route::get('/invoices/export/{type}', [GSTController::class, 'exportInvoices'])->name('invoices.export');
    Route::get('/taxes/export/{type}', [GSTController::class, 'exportTaxes'])->name('taxes.export');

    Route::post('/filter', [GstController::class, 'filter'])->name('manager.gst.filter');
    Route::post('/invoices/filter', [GstController::class, 'filterInvoices'])->name('manager.gst.invoices.filter');
    Route::post('/attach-invoice', [GstController::class, 'attachInvoice'])->name('manager.gst.attach-invoice');
    Route::get('/export/{type}', [GstController::class, 'export'])->name('manager.gst.export');
    Route::get('/invoices/export/{type}', [GstController::class, 'exportInvoices'])->name('manager.gst.invoices.export');


    Route::get('/expense', [TDSController::class, 'tdsExpense'])->name('manager.tdsExpense');
    Route::post('/attach', [TDSController::class, 'attachInvoice'])->name('manager.tds.attach');
    Route::post('/sync', [TDSController::class, 'syncInvoices'])->name('manager.tds.sync');
    Route::get('/tds/download-invoice/{id}', [TDSController::class, 'downloadInvoice'])->name('manager.tds.download-invoice');
    Route::get('/tds/attachments/{id}', [TDSController::class, 'viewAttachments'])->name('manager.tds.attachments');

    Route::get('/tds/download-bill/{id}', [TDSController::class, 'downloadBill'])->name('manager.tds.download-bill');
    Route::get('/tds/bill-attachments/{id}', [TDSController::class, 'viewBillAttachments'])->name('manager.tds.bill-attachments');
    Route::post('/tds/sync-expenses', [TDSController::class, 'syncExpenses'])->name('manager.tds.sync.expenses');
    Route::post('/mark-paid/{id}', [TDSController::class, 'markTDSPaid'])->name('manager.tds.mark-paid');
    Route::get('/getIncome/{id}', [IncomeController::class, 'show']);
    
    // routes/web.php or routes/manager.php
    // Loans/Advances Routes
    Route::prefix('loans')->group(function () {
        Route::get('/', [LoansController::class, 'index'])->name('manager.loans.index');
        Route::post('/', [LoansController::class, 'store'])->name('manager.loans.store');
        Route::get('/{id}', [LoansController::class, 'show'])->name('manager.loans.show');
        Route::post('/{id}', [LoansController::class, 'update'])->name('manager.loans.update');
        Route::delete('/{id}', [LoansController::class, 'destroy'])->name('manager.loans.destroy');

        // Recovery routes
        Route::post('/{id}/recovery', [LoansController::class, 'storeRecovery'])->name('manager.loans.recovery.store');

        // Stats
        Route::get('/stats', [LoansController::class, 'getStats'])->name('manager.loans.stats');
    });

    // Salary Module Routes
    Route::prefix('salary')->group(function () {
        Route::get('/dashboard', [SalaryController::class, 'dashboard'])->name('manager.salary.dashboard');
        
        Route::get('/employees', [SalaryController::class, 'employees'])->name('manager.salary.employees');
        Route::post('/employees', [SalaryController::class, 'storeEmployee'])->name('manager.salary.employees.store');
        
        Route::get('/settings', [SalaryController::class, 'settings'])->name('manager.salary.settings');
        Route::post('/settings/component', [SalaryController::class, 'storeComponent'])->name('manager.salary.components.store');
        Route::post('/settings/update', [SalaryController::class, 'updateSettings'])->name('manager.salary.settings.update');
        
        Route::get('/sheets', [SalaryController::class, 'sheets'])->name('manager.salary.sheets');
        Route::post('/sheets', [SalaryController::class, 'createSheet'])->name('manager.salary.sheets.create');
        Route::get('/sheets/{id}/edit', [SalaryController::class, 'editSheet'])->name('manager.salary.editSheet');
        Route::post('/sheets/{id}/save', [SalaryController::class, 'saveSheet'])->name('manager.salary.saveSheet');
        Route::post('/sheets/{id}/lock', [SalaryController::class, 'lockSheet'])->name('manager.salary.lockSheet');
        
        Route::get('/payments', [SalaryController::class, 'payments'])->name('manager.salary.payments');
        Route::post('/payments/mark', [SalaryController::class, 'markPayment'])->name('manager.salary.payments.mark');
        
        Route::get('/reports', [SalaryController::class, 'reports'])->name('manager.salary.reports');
        Route::post('/reports/generate', [SalaryController::class, 'generateReport'])->name('manager.salary.reports.generate');

        Route::get('/payslip/{item}/download', [SalaryController::class, 'downloadPayslip'])->name('manager.salary.payslip.download');
        Route::post('/payslip/{item}/email', [SalaryController::class, 'sendPayslipEmail'])->name('manager.salary.payslip.email');
    });

    // Attachment routes
    Route::post('/manager/tds/attach-document', [TDSController::class, 'attachTaxProof'])->name('manager.tds.attach-document');
    Route::get('/manager/tds/attachments/{invoiceId}', [TDSController::class, 'getAttachments']);
    Route::delete('/manager/tds/delete-attachment/{id}', [TDSController::class, 'deleteAttachment']);

    // Download TDS proof
    Route::get('/taxes/{id}/download-tds-proof', [TDSController::class, 'downloadTdsProof'])->name('manager.tds.download-tds-proof');

    // View TDS proof in browser
    Route::get('/taxes/{id}/view-tds-proof', [TDSController::class, 'viewTdsProof']);

    // Get TDS taxes for an invoice
    Route::get('/invoices/{id}/tds-taxes', [TDSController::class, 'getInvoiceTdsTaxes']);

    // Dedicated TDS Export routes to avoid conflicts
    Route::get('/tds/export/{type}', [TDSController::class, 'exportData'])->name('manager.tds.export');
    Route::get('/tds/expense/export/{type}', [TDSController::class, 'exportExpenseData'])->name('manager.tds.expense.export');
});





// CA Routes
Route::middleware(['auth', 'role:ca'])->prefix('ca')->group(function () {
    Route::get('/dashboard', [CADashboardController::class, 'index'])->name('ca.dashboard');
    Route::get('/statements', [\App\Http\Controllers\CA\StatementController::class, 'index'])->name('ca.statements');
    Route::get('/statements/download-attachments', [\App\Http\Controllers\CA\StatementController::class, 'downloadAttachments'])->name('ca.statements.download-attachments');
    Route::get('/invoices', [CAInvoiceController::class, 'index'])->name('ca.invoices');
    Route::get('/invoices/download-attachments', [CAInvoiceController::class, 'downloadAttachments'])->name('ca.invoices.download-attachments');
    Route::get('/records', [\App\Http\Controllers\CA\RecordController::class, 'index'])->name('ca.records');
    Route::get('/records/download-attachments', [\App\Http\Controllers\CA\RecordController::class, 'downloadAttachments'])->name('ca.records.download-attachments');
    Route::get('/expense-taxes', [\App\Http\Controllers\CA\ExpenseTaxController::class, 'index'])->name('ca.expense-taxes');
    Route::get('/expense-taxes/download-attachments', [\App\Http\Controllers\CA\ExpenseTaxController::class, 'downloadAttachments'])->name('ca.expense-taxes.download-attachments');
    Route::get('/loans-issued', [\App\Http\Controllers\CA\LoanController::class, 'issued'])->name('ca.loans-issued');
    Route::get('/loans-issued/download-attachments', [\App\Http\Controllers\CA\LoanController::class, 'downloadIssuedAttachments'])->name('ca.loans-issued.download-attachments');
    Route::get('/loan-recovery', [\App\Http\Controllers\CA\LoanController::class, 'recovery'])->name('ca.loan-recovery');
    Route::get('/loan-recovery/download-attachments', [\App\Http\Controllers\CA\LoanController::class, 'downloadRecoveryAttachments'])->name('ca.loan-recovery.download-attachments');
    Route::get('/salary-packs', [\App\Http\Controllers\CA\SalaryPackController::class, 'index'])->name('ca.salary-packs');
    Route::get('/salary-packs/download', [\App\Http\Controllers\CA\SalaryPackController::class, 'downloadAllPacks'])->name('ca.salary-packs.download-all');
    Route::get('/salary-packs/{id}/download', [\App\Http\Controllers\CA\SalaryPackController::class, 'downloadPack'])->name('ca.salary-packs.download');
    Route::get('/tasks', [\App\Http\Controllers\CA\TaskController::class, 'index'])->name('ca.tasks');
    Route::post('/tasks/{task}/status', [\App\Http\Controllers\CA\TaskController::class, 'updateStatus'])->name('ca.tasks.update');

    // Route::get('/statements/export', [CA\StatementController::class, 'export'])->name('ca.statements.export');
});



//USD to INR


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

Route::get('/conversion-rate', function (Illuminate\Http\Request $request) {
    $from = $request->get('from', 'USD');
    $to = $request->get('to', 'INR');
    $cacheKey = "rate_{$from}_{$to}";

    return Cache::remember($cacheKey, 3600, function () use ($from, $to) {
        try {
            $response = Http::get('https://api.frankfurter.app/latest', [
                'from' => $from,
                'to'   => $to
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'API Unavailable'], 500);
            }

            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
})->name('conversion.rate');

