<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Company, StandardExpense, NonStandardExpense, Income, UpcomingPayment};
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditLogController extends Controller
{
  public function index(Request $request)
  {
    $companies = Company::all();
    $logs = \App\Models\ActivityLog::with('user')->latest()->get();
    return view('Admin.audit_logs', compact('companies', 'logs'));
  }
}
