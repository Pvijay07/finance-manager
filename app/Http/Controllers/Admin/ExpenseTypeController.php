<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Company, ExpenseType, StandardExpense, NonStandardExpense, Income, UpcomingPayment};
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseTypeController extends Controller
{
  public function index( Request $request )
  {
    $expenseTypes = ExpenseType::all ()->map ( function ( $type )
    {
      $companyIds          = $type->applicable_companies ?? [];
      $type->company_names = Company::whereIn ( 'id', $companyIds )
        ->pluck ( 'name' )
        ->implode ( ', ' );
      return $type;
    } );

    $companies = Company::all ();
    return view ( 'Admin.expense_types', compact ( 'expenseTypes', 'companies' ) );
  }

  public function store( Request $request )
  {
    try {
      $validated = $request->validate ( [
        'name'           => 'required|string|max:255',
        'category'       => 'required|string|max:255',
        'amount_type'    => 'required|string|max:50',
        'default_amount' => 'required|numeric|min:0',
        'reminder_days'  => 'required|integer|min:1|max:30',
        'company_ids'    => 'array',
        'company_ids.*'  => 'integer|exists:companies,id',
        'status'         => 'required|string|in:active,inactive',
        'is_recurring'   => 'required|in:1,0',
      ] );

      $expense = ExpenseType::create ( [
        'name'                 => $validated['name'],
        'category'             => $validated['category'],
        'amount_type'          => $validated['amount_type'],
        'default_amount'       => $validated['default_amount'],
        'reminder_days'        => $validated['reminder_days'],
        'status'               => $validated['status'],
        'applicable_companies' => $validated['company_ids'] ?? [],
        'is_recurring'         => $validated['is_recurring']
      ] );

      return response ()->json ( [
        'success' => true,
        'message' => 'Expense Type saved successfully!',
        'data'    => $expense
      ], 201 );
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response ()->json ( [
        'success' => false,
        'message' => 'Validation failed',
        'errors'  => $e->errors ()
      ], 422 );
    } catch (\Exception $e) {
      return response ()->json ( [
        'success' => false,
        'message' => 'Failed to save Expense Type',
        'error'   => $e->getMessage ()
      ], 500 );
    }
  }

  public function edit( $id )
  {
    $expenseType = ExpenseType::findOrFail ( $id );
    return response ()->json ( [
      'success' => true,
      'data'    => $expenseType
    ] );
  }

  public function update( Request $request, $id )
  {
    try {
      $validated = $request->validate ( [
        'name'           => 'required|string|max:255',
        'category'       => 'required|string|max:255',
        'amount_type'    => 'required|string|max:50',
        'default_amount' => 'required|numeric|min:0',
        'reminder_days'  => 'required|integer|min:1|max:30',
        'company_ids'    => 'array',
        'company_ids.*'  => 'integer|exists:companies,id',
        'status'         => 'required|string|in:active,inactive',
        'is_recurring'   => 'required|in:1,0',

      ] );

      $expenseType = ExpenseType::findOrFail ( $id );
      $expenseType->update ( [
        'name'                 => $validated['name'],
        'category'             => $validated['category'],
        'amount_type'          => $validated['amount_type'],
        'default_amount'       => $validated['default_amount'],
        'reminder_days'        => $validated['reminder_days'],
        'status'               => $validated['status'],
        'applicable_companies' => $validated['company_ids'] ?? [],
        'is_recurring'         => $validated['is_recurring']

      ] );

      return response ()->json ( [
        'success' => true,
        'message' => 'Expense Type updated successfully!',
        'data'    => $expenseType
      ] );
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response ()->json ( [
        'success' => false,
        'message' => 'Validation failed',
        'errors'  => $e->errors ()
      ], 422 );
    } catch (\Exception $e) {
      return response ()->json ( [
        'success' => false,
        'message' => 'Failed to update Expense Type',
        'error'   => $e->getMessage ()
      ], 500 );
    }
  }
}
