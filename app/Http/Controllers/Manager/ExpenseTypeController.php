<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\{Company, ExpenseType, StandardExpense, NonStandardExpense, Income, UpcomingPayment};
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseTypeController extends Controller
{

  public function index(Request $request)
  {
    $expenseTypes = ExpenseType::all()->map(function ($type) {
      $companyIds          = $type->applicable_companies ?? [];
      $type->company_names = Company::whereIn('id', $companyIds)
        ->pluck('name')
        ->implode(', ');
      return $type;
    });

    $companies = Company::all();
    return view('Manager.expense_types', compact('expenseTypes', 'companies'));
  }
  public function store(Request $request)
  {
    try {
      // Validation
      $validated = $request->validate([
        'name'           => 'required|string|max:255',
        'category'       => 'required|string|max:255',
        'amount_type'    => 'required|string|max:50',
        'default_amount' => 'required|numeric|min:0',
        'reminder_days'  => 'required|integer|min:1|max:30',
        'company_ids'    => 'array',
        'company_ids.*'  => 'integer|exists:companies,id',
        'status'         => 'required|string|in:Active,Inactive',
      ]);

      // Save Expense Type
      $expense = ExpenseType::create([
        'name'                 => $validated['name'],
        'category'             => $validated['category'],
        'amount_type'          => $validated['amount_type'],
        'default_amount'       => $validated['default_amount'],
        'reminder_days'        => $validated['reminder_days'],
        'status'               => $validated['status'],
        'applicable_companies' => $validated['company_ids'] ?? [],
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Expense Type saved successfully!',
        'data'    => $expense
      ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors'  => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to save Expense Type',
        'error'   => $e->getMessage()
      ], 500);
    }
  }
}
