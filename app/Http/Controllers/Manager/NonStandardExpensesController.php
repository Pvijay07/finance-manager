<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NonStandardExpensesController extends Controller
{
  public function index(Request $request)
  {
    // Get filters
    $companyId = $request->get('company');
    $category = $request->get('category');
    $status = $request->get('status');

    // Query non-standard expenses
    $query = Expense::where('type', 'non_standard')
      ->with('company')
      ->orderBy('due_date', 'desc');

    // Apply filters
    if ($companyId) {
      $query->where('company_id', $companyId);
    }

    if ($category) {
      $query->where('category', $category);
    }

    if ($status) {
      $query->where('status', $status);
    }

    $expenses = $query->get();

    // Get data for filters
    $companies = Company::where('status', 'active')->get();
    $categories = Expense::where('type', 'non_standard')
      ->select('category')
      ->distinct()
      ->pluck('category');

    return view('Manager.expenses.non_standard', compact(
      'expenses',
      'companies',
      'categories'
    ));
  }

  public function store(Request $request)
  {
    $request->validate([
      'company_id' => 'required|exists:companies,id',
      'name' => 'required|string|max:255',
      'category' => 'required|string|max:100',
      'planned_amount' => 'required|numeric|min:0',
      'due_date' => 'nullable|date',
      'purpose_comment' => 'nullable|string',
      'party_name' => 'nullable|string|max:255',
      'status' => 'required|in:upcoming,pending,paid'
    ]);

    DB::beginTransaction();
    try {
      $dueDate = $request->due_date ?: now()->format('Y-m-d');

      $expense = Expense::create([
        'company_id' => $request->company_id,
        'name' => $request->name,
        'type' => 'non_standard',
        'purpose_comment' => $request->purpose_comment,
        'planned_amount' => $request->planned_amount,
        'actual_amount' => $request->status === 'paid' ? $request->planned_amount : null,
        'due_date' => $dueDate,
        'paid_date' => $request->status === 'paid' ? now()->format('Y-m-d') : null,
        'status' => $request->status,
        'party_name' => $request->party_name,
        'category' => $request->category,
        'source' => 'manual',
        'month_year' => Carbon::parse($dueDate)->format('Y-m'),
        'balance_amount' => $request->status === 'paid' ? 0 : $request->planned_amount,
        'created_by' => auth()->id()
      ]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Non-standard expense added successfully!',
        'expense' => $expense
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    $expense = Expense::where('type', 'non_standard')->findOrFail($id);

    $request->validate([
      'company_id' => 'required|exists:companies,id',
      'name' => 'required|string|max:255',
      'category' => 'required|string|max:100',
      'planned_amount' => 'required|numeric|min:0',
      'due_date' => 'nullable|date',
      'purpose_comment' => 'nullable|string',
      'party_name' => 'nullable|string|max:255',
      'status' => 'required|in:upcoming,pending,paid'
    ]);

    DB::beginTransaction();
    try {
      $dueDate = $request->due_date ?: $expense->due_date;

      $updateData = [
        'company_id' => $request->company_id,
        'name' => $request->name,
        'category' => $request->category,
        'planned_amount' => $request->planned_amount,
        'due_date' => $dueDate,
        'purpose_comment' => $request->purpose_comment,
        'party_name' => $request->party_name,
        'status' => $request->status,
        'month_year' => Carbon::parse($dueDate)->format('Y-m'),
        'updated_at' => now()
      ];

      // Handle payment status
      if ($request->status === 'paid') {
        $updateData['actual_amount'] = $request->planned_amount;
        $updateData['balance_amount'] = 0;
        $updateData['paid_date'] = now()->format('Y-m-d');
      } else {
        $updateData['actual_amount'] = null;
        $updateData['balance_amount'] = $request->planned_amount;
        $updateData['paid_date'] = null;
      }

      $expense->update($updateData);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Expense updated successfully!'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ], 500);
    }
  }

  public function markAsPaid($id)
  {
    $expense = Expense::where('type', 'non_standard')->findOrFail($id);

    $expense->update([
      'actual_amount' => $expense->planned_amount,
      'balance_amount' => 0,
      'status' => 'paid',
      'paid_date' => now()->format('Y-m-d')
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Marked as paid successfully!'
    ]);
  }

  public function destroy($id)
  {
    $expense = Expense::where('type', 'non_standard')->findOrFail($id);
    $expense->delete();

    return response()->json([
      'success' => true,
      'message' => 'Expense deleted successfully!'
    ]);
  }
  public function edit($id)
  {
    $expense = Expense::where('type', 'non_standard')->findOrFail($id);

    return response()->json([
      'success' => true,
      'expense' => [
        'id' => $expense->id,
        'company_id' => $expense->company_id,
        'name' => $expense->name,
        'category' => $expense->category,
        'planned_amount' => $expense->planned_amount,
        'due_date' => $expense->due_date,
        'party_name' => $expense->party_name,
        'status' => $expense->status,
        'purpose_comment' => $expense->purpose_comment
      ]
    ]);
  }
}
