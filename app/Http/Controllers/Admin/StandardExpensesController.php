<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAssignment;
use App\Models\Expense;
use App\Models\Company;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StandardExpensesController extends Controller
{
  public function index(Request $request)
  {
    $perPage        = $request->input('per_page', 10);
    $search         = $request->input('search');
    $companyFilter  = $request->input('company_id');
    $categoryFilter = $request->input('category_type');

    $query = Expense::where('source', 'standard')
      ->with(['company', 'categoryRelation'])
      ->latest();

    // Apply search filter
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('expense_name', 'like', "%{$search}%");
      });
    }

    // Apply company filter
    if ($companyFilter && $companyFilter !== 'all') {
      $query->where('company_id', $companyFilter);
    }

    // Apply category type filter
    if ($categoryFilter && $categoryFilter !== 'all') {
      $query->whereHas('categoryRelation', function ($q) use ($categoryFilter) {
        $q->where('category_type', $categoryFilter);
      });
    }

    $templates = $query->paginate($perPage);
    
    // Ensure statuses are updated for display (TC001/TC008)
    $templates->getCollection()->transform(function($item) {
        $today = Carbon::today();
        $dueDate = Carbon::parse($item->due_date);
        if ($item->source === 'standard' && $dueDate->isPast()) {
            $item->status = 'upcoming';
        }
        return $item;
    });

    $companies = Company::where('status', 'active')->get();

    return view('Admin.standard_expenses', [
      'expenseTypes'   => $templates,
      'companies'      => $companies,
      'perPage'        => $perPage,
      'search'         => $search,
      'companyFilter'  => $companyFilter,
      'categoryFilter' => $categoryFilter
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'expense_name'          => 'required|string|max:255',
      'company_id'            => 'nullable|exists:companies,id',
      'category_id'           => 'required|exists:categories,id',
      'actual_amount'         => 'required|numeric|min:0',
      'planned_amount'        => 'required|numeric|min:0',
      'frequency'             => 'required|in:monthly,quarterly,yearly',
      'due_day'               => 'required|integer|min:1|max:31',
      'reminder_days'         => 'required|integer|min:0',
      'party_name'            => 'nullable|string|max:255',
      'mobile_number'         => 'nullable|string|max:20',
      'is_active'             => 'in:0,1',

      // GST tax fields
      'apply_gst'             => 'nullable|in:0,1',
      'gst_percentage'        => 'nullable|numeric|min:0|max:100',
      'gst_amount'            => 'nullable|numeric|min:0',
      'gst_amount_paid'       => 'nullable|numeric|min:0',
      'gst_paid_date'         => 'nullable|date',
      'gst_payment_status'    => 'nullable|in:pending,partially_paid,paid',
      'gst_payment_notes'     => 'nullable|string|max:500',
      'gst_payment_reference' => 'nullable|string|max:255',

      // TDS tax fields
      'apply_tds'             => 'nullable|in:0,1',
      'tds_percentage'        => 'nullable|numeric|min:0|max:100',
      'tds_amount'            => 'nullable|numeric|min:0',
      'tds_amount_paid'       => 'nullable|numeric|min:0',
      'tds_paid_date'         => 'nullable|date',
      'tds_payment_status'    => 'nullable|in:pending,partially_paid,paid',
      'tds_payment_notes'     => 'nullable|string|max:500',
      'tds_payment_reference' => 'nullable|string|max:255'
    ]);

    try {
      DB::beginTransaction();
      $templateId = $request->input('template_id');

      // Calculate tax type based on what's applied
      $taxType = '';
      if ($request->apply_gst && $request->apply_tds) {
        $taxType = 'GST+TDS';
      } elseif ($request->apply_gst) {
        $taxType = 'GST';
      } elseif ($request->apply_tds) {
        $taxType = 'TDS';
      }
      $today = Carbon::now();

      $baseMonth = ((int)$request->due_day < $today->day)
        ? $today->copy()->addMonth()
        : $today->copy();
      // Prepare expense data
      $expenseData = [
        'expense_name'   => $request->expense_name,
        'company_id'     => $request->company_id,
        'category_id'    => $request->category_id,
        'actual_amount'  => $request->actual_amount,
        'planned_amount' => $request->planned_amount,
        'status'         => 'upcoming',
        'source'         => 'standard',
        'frequency'      => $request->frequency,
        'due_day'        => $request->due_day,
        'reminder_days'  => $request->reminder_days,
        'is_recurring'   => true,
        'party_name'     => $request->party_name,
        'mobile_number'  => $request->mobile_number,
        'amount_mode'    => 'fixed',
        'is_active'      => $request->is_active ?: 1,
        'tax_type'       => $taxType ?: null,
        'original_amount'=> $request->actual_amount,

        'due_date' => $baseMonth
          ->day(min((int)$request->due_day, $baseMonth->daysInMonth))
          ->format('Y-m-d'),

      ];

      // Create or update expense
      if ($templateId) {
        $expense = Expense::where('id', $templateId)
          ->where('source', 'standard')
          ->firstOrFail();
        $expense->update($expenseData);
        $message = 'Template updated successfully';
      } else {
        $expenseData['created_by'] = auth()->id();
        $expense                   = Expense::create($expenseData);
        $message                   = 'Template created successfully';
      }

      // Handle GST Tax
      if ($request->apply_gst) {
        $this->saveTax($expense, 'gst', [
          'tax_percentage'    => $request->gst_percentage ?? 0,
          'tax_amount'        => $request->gst_amount ?? 0,
          'amount_paid'       => $request->gst_amount_paid ?? 0,
          'paid_date'         => $request->gst_paid_date ?: null,
          'payment_status'    => $request->gst_payment_status ?? 'not_received',
          'payment_notes'     => $request->gst_payment_notes ?: null,
          'payment_reference' => $request->gst_payment_reference ?: null,
          'due_date'          => $this->calculateTaxDueDate($expense)
        ]);
      } else {
        // Remove GST tax if it exists
        $expense->taxes()->where('tax_type', 'gst')->delete();
      }

      // Handle TDS Tax
      if ($request->apply_tds) {
        $this->saveTax($expense, 'tds', [
          'tax_percentage'    => $request->tds_percentage ?? 0,
          'tax_amount'        => $request->tds_amount ?? 0,
          'amount_paid'       => $request->tds_amount_paid ?? 0,
          'paid_date'         => $request->tds_paid_date ?: null,
          'payment_status'    => $request->tds_payment_status ?? 'not_received',
          'payment_notes'     => $request->tds_payment_notes ?: null,
          'payment_reference' => $request->tds_payment_reference ?: null,
          'due_date'          => $this->calculateTaxDueDate($expense)
        ]);
      } else {
        // Remove TDS tax if it exists
        $expense->taxes()->where('tax_type', 'tds')->delete();
      }

      // Update expense payment status based on tax payments
      $this->updateExpensePaymentStatus($expense);

      DB::commit();

      return redirect()->route('admin.standard-expenses')
        ->with('success', $message);
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Failed to save: ' . $e->getMessage())
        ->withInput();
    }
  }

  /**
   * Save or update tax record
   */
  /**
   * Save or update tax record
   */
  private function saveTax($expense, $taxType, $taxData)
  {
    // Make sure tax_amount is not null
    if (!isset($taxData['tax_amount']) || is_null($taxData['tax_amount'])) {
      // Calculate tax amount if not provided
      $taxAmount = 0;

      if ($taxType === 'gst' && isset($taxData['tax_percentage'])) {
        $taxAmount = ($expense->actual_amount * $taxData['tax_percentage']) / 100;
      } elseif ($taxType === 'tds' && isset($taxData['tax_percentage'])) {
        $baseAmount = $expense->actual_amount;
        $taxAmount = ($baseAmount * $taxData['tax_percentage']) / 100;
      }

      $taxData['tax_amount'] = $taxAmount;
    }
    $taxData['tax_type'] = $taxType;

    // Ensure tax_amount is not null
    $taxData['tax_amount'] = $taxData['tax_amount'] ?? 0;

    // Add direction and other required fields
    $taxData = array_merge($taxData, [
      'direction'    => 'expense',
      'taxable_type' => get_class($expense)
    ]);

    // Find existing tax or create new
    $tax = $expense->taxes()->where('tax_type', $taxType)->first();

    if ($tax) {
      $tax->update($taxData);
    } else {
      $expense->taxes()->create($taxData);
    }
  }
  /**
   * Calculate tax due date based on expense frequency
   */
  /**
   * Calculate tax due date based on expense frequency
   */
  private function calculateTaxDueDate($expense)
  {
    try {
      $dueDate = now();

      switch ($expense->frequency) {
        case 'monthly':
          $dueDate = $dueDate->addMonth();
          break;
        case 'quarterly':
          $dueDate = $dueDate->addMonths(3);
          break;
        case 'yearly':
          $dueDate = $dueDate->addYear();
          break;
      }

      // Ensure due_day is an integer
      $dueDay = (int) $expense->due_day;

      // Set due day - handle cases where day might exceed days in month
      $daysInMonth  = $dueDate->daysInMonth;
      $dueDate->day = min($dueDay, $daysInMonth);

      return $dueDate->toDateString();
    } catch (\Exception $e) {
      // Log error and return a default date (1 month from now)
      \Log::error('Error calculating tax due date: ' . $e->getMessage());
      return now()->addMonth()->toDateString();
    }
  }
  /**
   * Update expense payment status based on tax payments
   */
  private function updateExpensePaymentStatus($expense)
  {
    $totalTaxAmount = $expense->taxes->sum('tax_amount');
    $totalTaxPaid   = $expense->taxes->sum('amount_paid');

    if ($totalTaxAmount == 0) {
      // No taxes, check main payment
      $mainAmount = $expense->actual_amount;
      $mainPaid   = $expense->amount_paid ?? 0;

      if ($mainPaid >= $mainAmount) {
        $expense->update(['payment_status' => 'paid', 'status' => 'paid']);
      } elseif ($mainPaid > 0) {
        $expense->update(['payment_status' => 'partially_paid', 'status' => 'pending']);
      } else {
        $expense->update(['payment_status' => 'pending', 'status' => 'upcoming']);
      }
    } else {
      // Check tax payments
      $allTaxesPaid = $expense->taxes->every(function ($tax) {
        return $tax->payment_status == 'paid';
      });

      if ($allTaxesPaid) {
        $expense->update(['payment_status' => 'paid', 'status' => 'paid']);
      } else {
        $someTaxesPaid = $expense->taxes->contains(function ($tax) {
          return in_array($tax->payment_status, ['paid', 'partially_paid']);
        });

        if ($someTaxesPaid) {
          $expense->update(['payment_status' => 'partially_paid', 'status' => 'pending']);
        } else {
          $expense->update(['payment_status' => 'pending', 'status' => 'upcoming']);
        }
      }
    }
  }

  public function show($id)
  {
    $template = Expense::with('categoryRelation')
      ->findOrFail($id);
    $category = $template->categoryRelation;

    $category_type = $category->category_type ?? null;
    $sub_type      = null;

    if ($category_type === 'standard_fixed') {
      $category_type = 'standard';
      $sub_type      = 'fixed';
    } elseif ($category_type === 'standard_editable') {
      $category_type = 'standard';
      $sub_type      = 'editable';
    }

    return response()->json([
      'id'             => $template->id,
      'expense_name'   => $template->expense_name,
      'company_id'     => $template->company_id,
      'category_id'    => $template->category_id,
      'amount_mode'    => $template->amount_mode,
      'actual_amount'  => $template->actual_amount,
      'planned_amount' => $template->planned_amount,
      'tax_percentage' => $template->tax_percentage,
      'tax_amount'     => $template->tax_amount,
      'apply_tax'      => $template->apply_tax,
      'party_name'     => $template->party_name,
      'mobile_number'  => $template->mobile_number,
      'category_type'  => $category_type,
      'sub_type'       => $sub_type,
      'frequency'      => $template->frequency,
      'due_day'        => $template->due_day,
      'reminder_days'  => $template->reminder_days,
      'is_recurring'   => $template->is_recurring,
      'is_active'      => $template->is_active,
      'tax_type'       => $template->tax_type

    ]);
  }
  public function edit($id)
  {
    try {
      $expense = Expense::with(['company', 'category'])
        ->where('source', 'standard')
        ->findOrFail($id);

      return response()->json([
        'id'             => $expense->id,
        'expense_name'   => $expense->expense_name,
        'company_id'     => $expense->company_id,
        'category_id'    => $expense->category_id,
        'planned_amount' => $expense->planned_amount,
        'frequency'      => $expense->frequency,
        'due_day'        => $expense->due_day,
        'reminder_days'  => $expense->reminder_days,
        'is_recurring'   => $expense->is_recurring,
        'amount_mode'    => $expense->amount_mode
      ]);
    } catch (\Exception $e) {
      return response()->json(['error' => 'Expense not found'], 404);
    }
  }

  public function getCategories(Request $request)
  {
    $request->validate([
      'direction'     => 'required|in:expense,income',
      'category_type' => 'required|string',
      // 'sub_type'      => 'required|string'
    ]);
    $request->direction == 'income' ? $category_type = $request->direction : $category_type = $request->category_type;
    $categories = Category::where('category_type', $category_type)
      ->where('main_type', $request->direction)
      ->where('is_active', true)
      ->orderBy('name')
      ->get(['id', 'name']);

    return response()->json([
      'success'    => true,
      'categories' => $categories
    ]);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'expense_name'   => 'required|string|max:255',
      'company_id'     => 'nullable|exists:companies,id',
      'category_id'    => 'required|exists:categories,id',
      'actual_amount'  => 'required|numeric|min:0',
      'planned_amount' => 'required|numeric|min:0',
      'frequency'      => 'required|in:monthly,quarterly,yearly',
      'due_day'        => 'required|integer|min:1|max:31',
      'reminder_days'  => 'required|integer|min:0',
      'party_name'     => 'nullable|string|max:255',
      'mobile_number'  => 'nullable|string|max:20',
      'is_active'      => 'nullable|in:0,1',

      // GST fields
      'apply_gst'      => 'nullable|in:0,1',
      'gst_percentage' => 'nullable|numeric|min:0|max:100',
      'gst_amount'     => 'nullable|numeric|min:0',
      'tds_amount'     => 'nullable|numeric|min:0',
    ]);
    // echo $request->is_active;
    // die;
    try {
      DB::beginTransaction();

      // Calculate tax type
      $taxType = '';
      if ($request->apply_gst && $request->apply_tds) {
        $taxType = 'GST+TDS';
      } elseif ($request->apply_gst) {
        $taxType = 'GST';
      } elseif ($request->apply_tds) {
        $taxType = 'TDS';
      }

      // Update expense
      $expense = Expense::where('id', $id)
        ->where('source', 'standard')
        ->firstOrFail();

      $expense->update([
        'expense_name'   => $request->expense_name,
        'company_id'     => $request->company_id,
        'category_id'    => $request->category_id,
        'actual_amount'  => $request->actual_amount,
        'planned_amount' => $request->planned_amount ?: $request->actual_amount,
        'frequency'      => $request->frequency,
        'due_day'        => $request->due_day,
        'reminder_days'  => $request->reminder_days,
        'party_name'     => $request->party_name,
        'mobile_number'  => $request->mobile_number,
        'is_active'      => $request->is_active,
        'tax_type'       => $taxType ?: null,
        'status'         => 'upcoming',
        'due_date'       => Carbon::now()->day((int)$request->due_day < Carbon::now()->day ? Carbon::now()->addMonth()->day : Carbon::now()->day)
          ->day(min((int)$request->due_day, Carbon::now()->daysInMonth))
          ->format('Y-m-d')
      ]);

      // Handle GST Tax
      if ($request->apply_gst) {
        $expense->taxes()->updateOrCreate(
          ['tax_type' => 'gst'],
          [
            'tax_percentage' => $request->gst_percentage ?? 18,
            'tax_amount'     => $request->gst_amount ?? 0,
            'amount_paid'    => 0,
            'payment_status' => 'not_received',
            'due_date'       => now()->addDays(30)
          ]
        );
      } else {
        $expense->taxes()->where('tax_type', 'gst')->delete();
      }

      // Handle TDS Tax
      if ($request->apply_tds) {
        $expense->taxes()->updateOrCreate(
          ['tax_type' => 'tds'],
          [
            'tax_percentage' => $request->tds_percentage ?? 10,
            'tax_amount'     => $request->tds_amount ?? 0,
            'amount_paid'    => 0,
            'payment_status' => 'not_received',
            'due_date'       => now()->addDays(30)
          ]
        );
      } else {
        $expense->taxes()->where('tax_type', 'tds')->delete();
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Template updated successfully'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to update: ' . $e->getMessage()
      ], 422);
    }
  }

  public function destroy($id)
  {
    try {
      $template = Expense::where('is_template', true)->findOrFail($id);

      // Check if template is used in any actual expenses
      $usedInExpenses = Expense::where('expense_type_id', $id)
        ->orWhere(function ($query) use ($template) {
          $query->where('template_name', $template->template_name)
            ->where('company_id', $template->company_id);
        })
        ->where('is_template', false)
        ->exists();

      if ($usedInExpenses) {
        return redirect()->back()
          ->with('error', 'Cannot delete template. It is used in existing expenses.');
      }

      $template->delete();

      return redirect()->route('admin.standard-expenses.index')
        ->with('success', 'Template deleted successfully');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to delete template: ' . $e->getMessage());
    }
  }


  public function generateExpenses(Request $request)
  {
    try {
      DB::beginTransaction();

      $userId         = auth()->id();
      $month          = date('Y-m');
      $generatedCount = 0;

      // Get all active recurring templates
      $templates = Expense::where('is_template', true)
        ->where('template_status', 'active')
        ->where('is_recurring', true)
        ->get();

      foreach ($templates as $template) {
        // Check if expense already exists for this month
        $exists = Expense::where('is_template', false)
          ->where('template_name', $template->template_name)
          ->where('company_id', $template->company_id)
          ->where('month_year', $month)
          ->exists();

        if (!$exists) {
          // Calculate due date
          $dueDate = $this->calculateDueDate($month, $template->due_day ?? 1);

          // Determine status based on due date
          $status = $this->determineExpenseStatus($dueDate);

          // Create actual expense from template
          Expense::create([
            'company_id'         => $template->company_id,
            'expense_name'       => $template->template_name,
            'type'               => 'standard',
            'purpose_comment'    => 'Auto-generated from template',
            'planned_amount'     => $template->amount_mode === 'standard'
              ? $template->default_amount
              : null,
            'default_amount'     => $template->default_amount,
            'actual_amount'      => null,
            'due_date'           => $dueDate,
            'due_day'            => $template->due_day,
            'paid_date'          => null,
            'status'             => $status,
            'template_status'    => null,
            'party_name'         => null,
            'category'           => $template->category,
            'source'             => 'standard',
            'month_year'         => $month,
            'balance_amount'     => $template->amount_mode === 'standard'
              ? $template->default_amount
              : null,
            'created_by'         => $userId,
            'entry_direction'    => $template->entry_direction,
            'amount_mode'        => $template->amount_mode,
            'frequency'          => $template->frequency,
            'reminder_days'      => $template->reminder_days,
            'is_recurring'       => false, // Actual expenses are not recurring
            'adjustable_balance' => $template->adjustable_balance,
            'is_template'        => false,
            'template_id'        => $template->id // Reference to template
          ]);

          $generatedCount++;
        }
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Successfully generated ' . $generatedCount . ' expenses.',
        'count'   => $generatedCount
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Generation failed: ' . $e->getMessage()
      ], 500);
    }
  }

  private function calculateDueDate($monthYear, $day)
  {
    try {
      $date      = \Carbon\Carbon::createFromFormat('Y-m-d', $monthYear . '-01');
      $date->day = $day;

      if (!$date->isValid()) {
        $date->endOfMonth();
      }

      return $date->format('Y-m-d');
    } catch (\Exception $e) {
      return \Carbon\Carbon::createFromFormat('Y-m', $monthYear)
        ->addMonth()
        ->firstOfMonth()
        ->format('Y-m-d');
    }
  }

  private function determineExpenseStatus($dueDate)
  {
    $dueDateCarbon = \Carbon\Carbon::parse($dueDate);
    $today         = \Carbon\Carbon::today();

    if ($dueDateCarbon->isPast()) {
      return 'pending';
    } elseif ($dueDateCarbon->isToday()) {
      return 'upcoming';
    } else {
      return 'upcoming';
    }
  }
  public function getReceipts($id)
  {
    try {
      $expense  = Expense::findOrFail($id);
      $receipts = $expense->receipts ?? [];

      return response()->json([
        'success'  => true,
        'receipts' => $receipts
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error loading receipts'
      ]);
    }
  }

  /**
   * Get tax details for editing
   */
  public function getTaxDetails($expenseId)
  {
    $expense = Expense::with([
      'taxes' => function ($query) {
        $query->orderBy('tax_type');
      }
    ])->findOrFail($expenseId);

    $taxes = [
      'gst' => null,
      'tds' => null
    ];

    foreach ($expense->taxes as $tax) {
      if ($tax->tax_type == 'gst') {
        $taxes['gst'] = [
          'percentage'        => $tax->tax_percentage,
          'amount'            => $tax->tax_amount,
          'amount_paid'       => $tax->amount_paid,
          'paid_date'         => $tax->paid_date,
          'payment_status'    => $tax->payment_status,
          'payment_notes'     => $tax->payment_notes,
          'payment_reference' => $tax->payment_reference
        ];
      } elseif ($tax->tax_type == 'tds') {
        $taxes['tds'] = [
          'percentage'        => $tax->tax_percentage,
          'amount'            => $tax->tax_amount,
          'amount_paid'       => $tax->amount_paid,
          'paid_date'         => $tax->paid_date,
          'payment_status'    => $tax->payment_status,
          'payment_notes'     => $tax->payment_notes,
          'payment_reference' => $tax->payment_reference
        ];
      }
    }

    return response()->json([
      'success'   => true,
      'taxes'     => $taxes,
      'apply_gst' => !is_null($taxes['gst']),
      'apply_tds' => !is_null($taxes['tds'])
    ]);
  }

  /**
   * Mark tax as paid
   */
  public function markTaxAsPaid(Request $request, $taxId)
  {
    $request->validate([
      'amount_paid'       => 'required|numeric|min:0',
      'paid_date'         => 'required|date',
      'payment_reference' => 'nullable|string|max:255',
      'payment_notes'     => 'nullable|string|max:500'
    ]);

    try {
      DB::beginTransaction();

      $tax = Tax::findOrFail($taxId);

      $tax->update([
        'amount_paid'       => $request->amount_paid,
        'paid_date'         => $request->paid_date,
        'payment_reference' => $request->payment_reference,
        'payment_notes'     => $request->payment_notes
      ]);

      // Update payment status
      if ($request->amount_paid >= $tax->tax_amount) {
        $tax->update(['payment_status' => 'paid']);
      } elseif ($request->amount_paid > 0) {
        $tax->update(['payment_status' => 'partially_paid']);
      } else {
        $tax->update(['payment_status' => 'pending']);
      }

      // Update expense status
      $this->updateExpensePaymentStatus($tax->taxable);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Tax payment recorded successfully'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to record payment: ' . $e->getMessage()
      ], 500);
    }
  }
}
