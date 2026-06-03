<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\GstTask;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::all();

        $companyId = $request->get('company_id');
        $taskType = $request->get('task_type', 'all');
        $status = $request->get('status', 'all');

        $query = GstTask::with(['company', 'creator'])->orderBy('due_date', 'asc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($taskType !== 'all') {
            $query->where('return_type', $taskType);
        }

        if ($status !== 'all') {
            $query->where('status', strtolower($status));
        }

        $perPage = 20;
        $paginated = $query->paginate($perPage)->appends($request->query());

        // We can dynamically extract available return types
        $availableTaskTypes = GstTask::select('return_type')->distinct()->pluck('return_type');

        return view('CA.tasks', compact('companies', 'paginated', 'availableTaskTypes'));
    }

    public function updateStatus(Request $request, GstTask $task)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task->status = $request->status;
        
        if ($task->status === 'completed' && !$task->completed_date) {
            $task->completed_date = now();
        } elseif ($task->status !== 'completed') {
            $task->completed_date = null;
        }

        $task->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }

        return back()->with('success', 'Task status updated.');
    }
}
