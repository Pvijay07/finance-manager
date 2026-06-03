<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && $request->action) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);
        $users = User::whereIn('role', ['admin', 'manager'])->get();

        return view('Admin.activity-logs.index', compact('logs', 'users'));
    }

    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);
        
        return view('Admin.activity-logs.show', compact('log'));
    }

    public function clear(Request $request)
    {
        $days = $request->get('days', 30);
        $cutoffDate = Carbon::now()->subDays($days);
        
        $deleted = ActivityLog::where('created_at', '<', $cutoffDate)->delete();
        
        return redirect()->route('admin.activity-logs.index')
            ->with('success', "Cleared $deleted activity logs older than $days days.");
    }

    public function export(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->whereBetween('created_at', [
                $request->get('date_from', Carbon::now()->subDays(30)),
                $request->get('date_to', Carbon::now())
            ])
            ->get();

        // Generate CSV or Excel file
        $filename = 'activity-logs-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['ID', 'Timestamp', 'User', 'Action', 'Resource', 'Details', 'IP Address']);
            
            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'System',
                    $log->action,
                    $log->model_type . ' #' . $log->model_id,
                    json_encode($log->details),
                    $log->ip_address
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}