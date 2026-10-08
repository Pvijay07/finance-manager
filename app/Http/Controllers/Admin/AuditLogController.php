<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
  public function index(Request $request)
  {
    $query = ActivityLog::with('user');

    // 1. Search Query
    $search = $request->input('search');
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('action', 'like', "%{$search}%")
          ->orWhere('model_type', 'like', "%{$search}%")
          ->orWhere('model_id', 'like', "%{$search}%")
          ->orWhere('ip_address', 'like', "%{$search}%")
          ->orWhere('details', 'like', "%{$search}%")
          ->orWhereHas('user', function ($uq) use ($search) {
            $uq->where('name', 'like', "%{$search}%")
               ->orWhere('email', 'like', "%{$search}%");
          });
      });
    }

    // 2. Date Range Filter
    $dateRange = $request->input('date_range', 'last_7_days');
    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');
    $now       = Carbon::now();

    if ($dateRange === 'today') {
      $query->whereDate('created_at', Carbon::today());
    } elseif ($dateRange === 'yesterday') {
      $query->whereDate('created_at', Carbon::yesterday());
    } elseif ($dateRange === 'last_7_days' || $dateRange === '7_days') {
      $query->where('created_at', '>=', $now->copy()->subDays(7)->startOfDay());
    } elseif ($dateRange === 'last_30_days' || $dateRange === '30_days') {
      $query->where('created_at', '>=', $now->copy()->subDays(30)->startOfDay());
    } elseif ($dateRange === 'this_month') {
      $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
    } elseif ($dateRange === 'custom') {
      if ($startDate && $endDate) {
        $query->whereBetween('created_at', [
          Carbon::parse($startDate)->startOfDay(),
          Carbon::parse($endDate)->endOfDay()
        ]);
      } elseif ($startDate) {
        $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
      } elseif ($endDate) {
        $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
      }
    }

    // 3. User Filter
    $userId = $request->input('user_id');
    if ($userId && $userId !== 'all') {
      $query->where('user_id', $userId);
    }

    // 4. Action Type Filter
    $actionType = $request->input('action_type');
    if ($actionType && $actionType !== 'all') {
      if ($actionType === 'create') {
        $query->where(function ($q) {
          $q->where('action', 'like', '%creat%')->orWhere('action', 'like', '%store%');
        });
      } elseif ($actionType === 'update') {
        $query->where(function ($q) {
          $q->where('action', 'like', '%updat%')->orWhere('action', 'like', '%edit%');
        });
      } elseif ($actionType === 'delete') {
        $query->where(function ($q) {
          $q->where('action', 'like', '%delet%')->orWhere('action', 'like', '%destroy%');
        });
      } elseif ($actionType === 'login') {
        $query->where('action', 'like', '%login%');
      } else {
        $query->where('action', $actionType);
      }
    }

    // 5. Resource Filter
    $resource = $request->input('resource');
    if ($resource && $resource !== 'all') {
      $query->where('model_type', 'like', "%{$resource}%");
    }

    // Summary Metric Counts
    $today = Carbon::today();
    $totalLogs     = ActivityLog::count();
    $todayLogs     = ActivityLog::whereDate('created_at', $today)->count();
    $createdCount  = ActivityLog::where('action', 'like', '%creat%')->count();
    $updatedCount  = ActivityLog::where('action', 'like', '%updat%')->count();
    $deletedCount  = ActivityLog::where('action', 'like', '%delet%')->count();

    // Pagination
    $perPage = (int) $request->input('per_page', 20);
    $logs = $query->latest()->paginate($perPage)->withQueryString();

    // Data for filter selects
    $users = User::orderBy('name')->get();
    $companies = Company::where('status', 'active')->get();

    return view('Admin.audit_logs', compact(
      'logs',
      'users',
      'companies',
      'search',
      'dateRange',
      'startDate',
      'endDate',
      'userId',
      'actionType',
      'resource',
      'perPage',
      'totalLogs',
      'todayLogs',
      'createdCount',
      'updatedCount',
      'deletedCount'
    ));
  }

  public function export(Request $request)
  {
    $query = ActivityLog::with('user');

    // Apply same filters
    $search = $request->input('search');
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('action', 'like', "%{$search}%")
          ->orWhere('model_type', 'like', "%{$search}%")
          ->orWhere('model_id', 'like', "%{$search}%")
          ->orWhere('ip_address', 'like', "%{$search}%")
          ->orWhere('details', 'like', "%{$search}%")
          ->orWhereHas('user', function ($uq) use ($search) {
            $uq->where('name', 'like', "%{$search}%")
               ->orWhere('email', 'like', "%{$search}%");
          });
      });
    }

    $dateRange = $request->input('date_range', 'last_7_days');
    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');
    $now       = Carbon::now();

    if ($dateRange === 'today') {
      $query->whereDate('created_at', Carbon::today());
    } elseif ($dateRange === 'yesterday') {
      $query->whereDate('created_at', Carbon::yesterday());
    } elseif ($dateRange === 'last_7_days' || $dateRange === '7_days') {
      $query->where('created_at', '>=', $now->copy()->subDays(7)->startOfDay());
    } elseif ($dateRange === 'last_30_days' || $dateRange === '30_days') {
      $query->where('created_at', '>=', $now->copy()->subDays(30)->startOfDay());
    } elseif ($dateRange === 'this_month') {
      $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
    } elseif ($dateRange === 'custom') {
      if ($startDate && $endDate) {
        $query->whereBetween('created_at', [
          Carbon::parse($startDate)->startOfDay(),
          Carbon::parse($endDate)->endOfDay()
        ]);
      } elseif ($startDate) {
        $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
      } elseif ($endDate) {
        $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
      }
    }

    $userId = $request->input('user_id');
    if ($userId && $userId !== 'all') {
      $query->where('user_id', $userId);
    }

    $actionType = $request->input('action_type');
    if ($actionType && $actionType !== 'all') {
      if ($actionType === 'create') {
        $query->where(function ($q) {
          $q->where('action', 'like', '%creat%')->orWhere('action', 'like', '%store%');
        });
      } elseif ($actionType === 'update') {
        $query->where(function ($q) {
          $q->where('action', 'like', '%updat%')->orWhere('action', 'like', '%edit%');
        });
      } elseif ($actionType === 'delete') {
        $query->where(function ($q) {
          $q->where('action', 'like', '%delet%')->orWhere('action', 'like', '%destroy%');
        });
      } elseif ($actionType === 'login') {
        $query->where('action', 'like', '%login%');
      } else {
        $query->where('action', $actionType);
      }
    }

    $resource = $request->input('resource');
    if ($resource && $resource !== 'all') {
      $query->where('model_type', 'like', "%{$resource}%");
    }

    $logs = $query->latest()->limit(5000)->get();

    $headers = [
      'Content-Type'        => 'text/csv; charset=UTF-8',
      'Content-Disposition' => 'attachment; filename="audit_logs_' . date('Y_m_d_His') . '.csv"',
      'Pragma'              => 'no-cache',
      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
      'Expires'             => '0',
    ];

    $callback = function () use ($logs) {
      $file = fopen('php://output', 'w');
      fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
      fputcsv($file, ['ID', 'Timestamp', 'User', 'Action', 'Resource', 'Resource ID', 'IP Address', 'Details']);

      foreach ($logs as $log) {
        $detailsText = is_array($log->details) ? json_encode($log->details, JSON_UNESCAPED_UNICODE) : (string)$log->details;
        fputcsv($file, [
          $log->id,
          $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '',
          $log->user ? $log->user->name : 'System',
          ucfirst(str_replace('_', ' ', $log->action)),
          class_basename($log->model_type),
          $log->model_id ?? '',
          $log->ip_address ?? '',
          $detailsText
        ]);
      }
      fclose($file);
    };

    return response()->stream($callback, 200, $headers);
  }
}
