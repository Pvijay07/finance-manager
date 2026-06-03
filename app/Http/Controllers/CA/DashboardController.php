<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Advance;
use App\Models\GstTask;
use App\Models\Tax;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
  public function index()
  {
    // Pending Docs: Expenses and Advances missing attachments
    $pendingDocsCount = Expense::doesntHave('attachments')->count() + Advance::doesntHave('attachments')->count();

    // Open Tasks: GST Tasks not completed
    $openTasksCount = GstTask::where('status', '!=', 'completed')->count();

    // GST Input This Month: Tax amount for expenses this month
    $gstInputThisMonth = Tax::where('tax_type', 'gst')
      ->where('taxable_type', Expense::class)
      ->whereMonth('created_at', Carbon::now()->month)
      ->whereYear('created_at', Carbon::now()->year)
      ->sum('tax_amount');

    // Outstanding Loans: Advances given out that are not fully recovered
    $outstandingLoans = Advance::outstanding()
      ->where('transaction_type', 'recoverable_advance')
      ->sum('outstanding_amount');

    // Recent Activity: Fetch latest activity logs
    $recentActivities = ActivityLog::with('user')->latest()->take(5)->get();

    return view('CA.dashboard', compact(
      'pendingDocsCount',
      'openTasksCount',
      'gstInputThisMonth',
      'outstandingLoans',
      'recentActivities'
    ));
  }
}
