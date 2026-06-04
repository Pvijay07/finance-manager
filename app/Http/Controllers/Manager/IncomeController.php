<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tax;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;


class IncomeController extends Controller
{
  
  public function index(Request $request)
  {
    $user = auth()->user();

    $companyId = $request->get('company');
    $dateRange = $request->get('date_range', 'month');
    $type = $request->get('type');
    $status = $request->get('status', 'all');
    $category = $request->get('category', 'all');
    $currency = $request->get('currency', 'all');

    $query = Income::with(['company', 'parent', 'children', 'invoice'])
      ->orderBy('created_at', 'desc');

    // Filter by user's managed companies
    $query->whereHas('company', function ($q) use ($user) {
      $q->where('manager_id', $user->id);
    });

    // Apply company filter
    if ($companyId) {
      $query->where('company_id', $companyId);
    }

    // Apply currency filter
    if ($currency && $currency !== 'all') {
      $query->whereHas('invoice', function ($q) use ($currency) {
        $q->where('currency', $currency);
      });
    }

    $now = now();
    $currentMonth = $now->format('M');
    $nextMonth = $now->copy()->addMonth()->format('M');
    $currentYear = $now->format('Y');

    // Apply date range filter
    switch ($dateRange) {
      case 'today':
        $query->whereDate('created_at', $now->toDateString());
        $statsStartDate = $now->copy()->startOfDay();
        $statsEndDate = $now->copy()->endOfDay();
        $dateRangeTitle = 'Today (' . $now->format('d M Y') . ')';
        break;
      case 'week':
        $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        $statsStartDate = $now->copy()->startOfWeek();
        $statsEndDate = $now->copy()->endOfWeek();
        $dateRangeTitle = 'This Week (' . $statsStartDate->format('d M') . ' - ' . $statsEndDate->format('d M Y') . ')';
        break;
      case 'month':
        $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        $statsStartDate = $now->copy()->startOfMonth();
        $statsEndDate = $now->copy()->endOfMonth();
        $dateRangeTitle = $now->format('F Y');
        break;
      case 'quarter':
        $query->whereBetween('created_at', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
        $statsStartDate = $now->copy()->startOfQuarter();
        $statsEndDate = $now->copy()->endOfQuarter();
        $quarter = ceil($now->month / 3);
        $dateRangeTitle = 'Q' . $quarter . ' ' . $now->format('Y');
        break;
      case 'year':
        $query->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
        $statsStartDate = $now->copy()->startOfYear();
        $statsEndDate = $now->copy()->endOfYear();
        $dateRangeTitle = $now->format('Y');
        break;
      case 'next7days':
        $query->whereBetween('created_at', [$now->copy(), $now->copy()->addDays(7)]);
        $statsStartDate = $now->copy();
        $statsEndDate = $now->copy()->addDays(7);
        $dateRangeTitle = 'Next 7 Days';
        break;
      case 'custom':
        // Handle custom date range if needed
        $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        $statsStartDate = $now->copy()->startOfMonth();
        $statsEndDate = $now->copy()->endOfMonth();
        $dateRangeTitle = $now->format('F Y');
        break;
      default:
        $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        $statsStartDate = $now->copy()->startOfMonth();
        $statsEndDate = $now->copy()->endOfMonth();
        $dateRangeTitle = $now->format('F Y');
        break;
    }

    // Apply type filter (category in your case)
    if ($category && $category !== 'all') {
      if ($category === 'standard') {
        $query->where('income_type', 'standard');
      } elseif ($category === 'non-standard') {
        $query->where('income_type', 'non-standard');
      }
    }

    // Apply status filter
    if ($status && $status !== 'all') {
      $query->where('status', $status);
    }

    $incomes = $query->paginate(20);
    $companies = Company::where('manager_id', $user->id)
      ->where('status', 'active')
      ->get();
    $statuses = ['pending', 'received', 'overdue', 'upcoming'];

    // Create a helper function for statistics queries
    $statsQuery = function ($conditions = []) use ($user, $statsStartDate, $statsEndDate, $companyId, $currency) {
      $query = Income::whereHas('company', function ($q) use ($user) {
        $q->where('manager_id', $user->id);
      });

      // Apply date range
      $query->whereBetween('created_at', [$statsStartDate, $statsEndDate]);

      // Apply company filter if selected
      if ($companyId) {
        $query->where('company_id', $companyId);
      }

      // Apply currency filter
      if ($currency && $currency !== 'all') {
        $query->whereHas('invoice', function ($q) use ($currency) {
          $q->where('currency', $currency);
        });
      }

      // Apply additional conditions
      foreach ($conditions as $condition) {
        $query->where($condition[0], $condition[1], $condition[2] ?? null);
      }

      return $query;
    };

    // Calculate statistics based on selected date range - FIXED
    $stats = [
      'totalPayments' => $statsQuery()->sum('amount') ?? 0,
      'paymentItems' => $statsQuery()->count(),

      'totalReceived' => $statsQuery([['status', 'received']])->sum('amount') ?? 0,
      'receivedItems' => $statsQuery([['status', 'received']])->count(),

      'totalPending' => $statsQuery([['status', 'pending']])->sum('amount') ?? 0,
      'pendingItems' => $statsQuery([['status', 'pending']])->count(),

      'totalOverdue' => $statsQuery([['status', 'overdue']])->sum('amount') ?? 0,
      'overdueItems' => $statsQuery([['status', 'overdue']])->count(),

      // All-time overdue (not filtered by date range) - FIXED
      'allTimeOverdue' => Income::whereHas('company', function ($q) use ($user, $companyId) {
        $q->where('manager_id', $user->id);
        if ($companyId) {
          $q->where('id', $companyId);
        }
      })
        ->where('status', 'overdue')
        ->when($currency && $currency !== 'all', function ($q) use ($currency) {
          return $q->whereHas('invoice', function ($sq) use ($currency) {
            $sq->where('currency', $currency);
          });
        })
        ->sum('amount') ?? 0,
      'allTimeOverdueItems' => Income::whereHas('company', function ($q) use ($user, $companyId) {
        $q->where('manager_id', $user->id);
        if ($companyId) {
          $q->where('id', $companyId);
        }
      })
        ->where('status', 'overdue')
        ->when($currency && $currency !== 'all', function ($q) use ($currency) {
          return $q->whereHas('invoice', function ($sq) use ($currency) {
            $sq->where('currency', $currency);
          });
        })
        ->count(),
    ];

    return view('Manager.cash-flow.income', compact(
      'incomes',
      'companies',
      'statuses',
      'stats',
      'dateRangeTitle',
      'dateRange',
      'companyId',
      'type',
      'category',
      'status',
      'currency',
      'currentMonth',
      'nextMonth',
      'currentYear'
    ));
  }

  public function showIncome($id)
  {
      try {
          $income = Income::with(['company', 'invoice', 'parent.children', 'children', 'taxes'])->find($id);
          
          if (!$income) {
              return redirect()->back()->with('error', 'Income not found');
          }

          // Traverse up to find the ultimate root income
          $rootIncome = $income;
          while ($rootIncome->parent_id) {
              $rootIncome = Income::with(['parent', 'children', 'taxes'])->find($rootIncome->parent_id);
              if (!$rootIncome) break;
          }
          
          if ($rootIncome) {
              $rootId = $rootIncome->id;
              $allSplits = collect([$rootIncome]);
              $toProcess = collect([$rootId]);

              while ($toProcess->isNotEmpty()) {
                  $nextLevel = Income::with(['company', 'invoice', 'parent', 'children', 'taxes'])->whereIn('parent_id', $toProcess)->get();
                  if ($nextLevel->isEmpty()) break;
                  
                  $allSplits = $allSplits->concat($nextLevel);
                  $toProcess = $nextLevel->pluck('id');
              }
              $familyIncomes = $allSplits;
          } else {
              $familyIncomes = collect([$income]);
          }
          
          $uniqueFamily = $familyIncomes->unique('id');
          $original_conversion_cost = $uniqueFamily->sum('conversion_cost');
          $original_total_amount = $uniqueFamily->sum('amount');
          
          return view('Manager.cash-flow.income_view', compact('income', 'uniqueFamily', 'original_conversion_cost', 'original_total_amount'));
      } catch (\Exception $e) {
          return redirect()->back()->with('error', 'Failed to load income details: ' . $e->getMessage());
      }
  }

  public function getIncomeDetails($id)
  {
    try {
      $income = Income::with(['company', 'invoice', 'parent.children', 'children'])->find($id);
      
      if ($income) {
          // Traverse up to find the ultimate root income
          $rootIncome = $income;
          while ($rootIncome->parent_id) {
              $rootIncome = Income::with(['parent', 'children'])->find($rootIncome->parent_id);
              if (!$rootIncome) break;
          }
          
          if ($rootIncome) {
              $rootId = $rootIncome->id;
              $allSplits = collect([$rootIncome]);
              $toProcess = collect([$rootId]);

              while ($toProcess->isNotEmpty()) {
                  $nextLevel = Income::with(['company', 'invoice', 'parent', 'children'])->whereIn('parent_id', $toProcess)->get();
                  if ($nextLevel->isEmpty()) break;
                  
                  $allSplits = $allSplits->concat($nextLevel);
                  $toProcess = $nextLevel->pluck('id');
              }
              $familyIncomes = $allSplits;
          } else {
              $familyIncomes = collect([$income]);
          }
          
          $uniqueFamily = $familyIncomes->unique('id');
          $income->original_conversion_cost = $uniqueFamily->sum('conversion_cost');
          $income->original_total_amount = $uniqueFamily->sum('amount');
      }

      return response()->json([
        'success' => true,
        'income' => $income,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Income not found',
      ]);
    }
  }

  // Add new method to receive partial payment
  public function receivePayment(Request $request, $id)
  {
    $request->validate([
      'received_amount' => 'required|numeric|min:0.01',
      'payment_date' => 'required|date',
      'new_due_date' => 'nullable|date|required_if:create_new_proforma,1',
      'internal_note' => 'nullable|string',
      'create_new_proforma' => 'required|boolean',
    ]);

    try {
      DB::beginTransaction();

      $income = Income::with('taxes')->findOrFail($id);
      
      $totalPayable = $income->amount; // Total Payable Amount
      $receivedAmount = $request->received_amount;

      if ($receivedAmount > $totalPayable) {
        return response()->json([
          'success' => false,
          'message' => 'Received amount must be less than payable amount for partial payment',
        ]);
      }

      $paymentPercentage = $receivedAmount / $totalPayable;
      
      // Calculate current portion
      $currentBaseAmount = $income->actual_amount * $paymentPercentage;
      $currentPlannedAmount = $income->planned_amount * $paymentPercentage;
      $currentConversionCost = ($income->conversion_cost ?? 0) * $paymentPercentage;
      
      // Calculate pending portion
      $pendingBaseAmount = $income->actual_amount - $currentBaseAmount;
      $pendingPlannedAmount = $income->planned_amount - $currentPlannedAmount;
      $pendingConversionCost = ($income->conversion_cost ?? 0) - $currentConversionCost;
      $pendingAmount = $totalPayable - $receivedAmount;

      // Create new income record for balance if requested
      if (($request->create_new_proforma == 1 || $request->create_new_proforma === true) && $receivedAmount < $totalPayable) {
          $newIncome = $income->replicate();
          unset($newIncome->paid_date);
          $newIncome->actual_amount = $pendingBaseAmount;
          $newIncome->planned_amount = $pendingPlannedAmount;
          $newIncome->amount = $pendingAmount;
          $newIncome->balance_amount = $pendingAmount;
          $newIncome->status = 'pending';
          $newIncome->income_date = $request->new_due_date ?? now()->addDays(30)->format('Y-m-d');
          $newIncome->is_partial = true;
          $newIncome->parent_id = $income->parent_id ?? $income->id;
          $newIncome->conversion_cost = $pendingConversionCost;
          $newIncome->save();

          // Handle taxes replication
          foreach ($income->taxes as $tax) {
              $newTaxAmount = $tax->tax_amount - ($tax->tax_amount * $paymentPercentage);
              if ($newTaxAmount > 0) {
                  $newIncome->taxes()->create([
                      'taxable_type' => Income::class,
                      'taxable_id' => $newIncome->id,
                      'tax_type' => $tax->tax_type,
                      'tax_percentage' => $tax->tax_percentage,
                      'tax_amount' => $newTaxAmount,
                      'payment_status' => 'not_received',
                      'direction' => 'income',
                      'company_id' => $tax->company_id,
                      'taxable_amount' => $pendingBaseAmount
                  ]);
              }
              // Update original tax amount for current income
              $tax->tax_amount = $tax->tax_amount * $paymentPercentage;
              $tax->taxable_amount = $currentBaseAmount;
              $tax->save();
          }
      }

      // Update original income record
      $income->amount = $receivedAmount;
      $income->actual_amount = $currentBaseAmount;
      $income->planned_amount = $currentPlannedAmount;
      $income->status = 'received';
      $income->paid_date = $request->payment_date ?? now()->format('Y-m-d');
      $income->is_partial = true;
      $income->balance_amount = 0; // Balance moved to new record
      $income->conversion_cost = $currentConversionCost;
      $income->save();

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Partial payment recorded successfully',
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Receive payment error: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error recording payment: ' . $e->getMessage(),
      ]);
    }
  }

  public function store(Request $request)
  {
    // Handle "Convert to TDS" special status
    if ($request->status === 'convert_to_tds') {
      $pAmount = $request->grand_total;
      $rAmount = $request->received_amount ?? 0;
      $targetTds = $pAmount - $rAmount;

      if ($targetTds > 0) {
        $request->merge([
          'apply_tds' => '1',
          'tds_amount' => $targetTds,
          'tds_percentage' => ($pAmount > 0) ? ($targetTds / $pAmount) * 100 : 0,
          'status' => 'settle'
        ]);
      } else {
        $request->merge(['status' => 'settle']);
      }
    }

    $data = $request->validate([
      'company_id' => 'required|exists:companies,id',
      'client_name' => 'required|string|max:255',
      'amount' => 'required|numeric|min:0',
      'frequency' => 'nullable|string|in:Monthly,Weekly,Quarterly,Yearly,One-time',
      'gst_percentage' => 'nullable|numeric|min:0|max:100',
      'gst_amount' => 'nullable|numeric|min:0',
      'tds_percentage' => 'nullable|numeric|min:0|max:100',
      'tds_amount' => 'nullable|numeric|min:0',
      'amount_after_tds' => 'nullable|numeric|min:0',
      'grand_total' => 'required|numeric|min:0',
      'received_amount' => 'nullable|numeric|min:0',
      'balance_amount' => 'nullable|numeric|min:0',
      'tds_status' => 'nullable|string|in:received,not_received',
      'due_date' => 'required_if:status,due|nullable|date',
      'status' => 'required|in:settle,due,convert_to_tds',
      'income_date' => 'nullable|date',
      'received_date' => 'nullable|date',
      'mail_status' => 'nullable|in:1,0',
      'notes' => 'nullable|string',
      'settle_notes' => 'required_if:status,settle,paid|nullable|string',
      'receipts.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
      'tds_receipt' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
    ]);

    DB::beginTransaction();
    try {
      // Calculate base amounts and taxes
      $actualTotalBase = $data['amount'] ?? 0;
      $gstAmountTotal = $data['gst_amount'] ?? 0;
      $tdsAmountTotal = $data['tds_amount'] ?? 0;
      
      $plannedAmountTotal = $actualTotalBase + $gstAmountTotal;
      $payableAmountTotal = $plannedAmountTotal - $tdsAmountTotal;
      
      $receivedAmount = $request->received_amount ?? 0;

      // Check if this should be a split payment
      $isSplitPayment = $request->status === 'due' &&
        $receivedAmount > 0 &&
        $receivedAmount < $payableAmountTotal;

      if (($isSplitPayment || $request->status === 'settle') && $payableAmountTotal > 0) {
        $proportion = $receivedAmount / $payableAmountTotal;
        $gstAmountForCurrent = $gstAmountTotal * $proportion;
        $tdsAmountForCurrent = $tdsAmountTotal * $proportion;
        $paidBaseAmount = $actualTotalBase * $proportion;
        $paidPlannedAmount = $plannedAmountTotal * $proportion;
        
        $balanceBaseAmount = $actualTotalBase - $paidBaseAmount;
        $balancePlannedAmount = $plannedAmountTotal - $paidPlannedAmount;
        $balanceAmount = $payableAmountTotal - $receivedAmount;
      } else {
        $gstAmountForCurrent = $gstAmountTotal;
        $tdsAmountForCurrent = $tdsAmountTotal;
        $paidBaseAmount = $actualTotalBase;
        $paidPlannedAmount = $plannedAmountTotal;
        $balanceBaseAmount = 0;
        $balancePlannedAmount = 0;
        $balanceAmount = $payableAmountTotal - $receivedAmount;
      }

      $income = Income::create([
        'company_id' => $data['company_id'],
        'party_name' => $data['client_name'],
        'amount' => ($isSplitPayment || $request->status === 'settle') ? $receivedAmount : $payableAmountTotal,
        'received_amount' => $receivedAmount,
        'planned_amount' => $paidPlannedAmount,
        'actual_amount' => $paidBaseAmount,
        'original_amount' => $actualTotalBase,
        'schedule_amount' => $plannedAmountTotal,
        'balance_amount' => $isSplitPayment ? 0 : max(0, $balanceAmount),
        'due_date' => $data['due_date'] ?? null,
        'status' => ($isSplitPayment || $data['status'] === 'settle') ? 'received' : 'pending',
        'income_date' => $data['received_date'] ?? now()->format('Y-m-d'),
        'paid_date' => ($isSplitPayment || $data['status'] === 'settle') ? ($request->received_date ?? now()->format('Y-m-d')) : null,
        'mail_status' => $data['mail_status'] ?? 0,
        'notes' => $data['notes'] ?? null,
        'settle_notes' => $data['settle_notes'] ?? null,
        'client_details' => json_encode(['mobile_number' => $request->mobile_number ?? null]),
        'is_partial' => $isSplitPayment,
        'income_type' => 'non-standard',
        'source' => 'manual',
        'created_by' => auth()->id()
      ]);

      // Handle taxes (always for the first shard in non-standard income)
      if (true) {
        // Handle GST tax
        if ($request->boolean('apply_gst')) {
          $income->taxes()->create([
            'taxable_type' => Income::class,
            'taxable_id' => $income->id,
            'tax_type' => 'gst',
            'tax_percentage' => $data['gst_percentage'] ?? 0,
            'tax_amount' => $gstAmountForCurrent,
            'payment_status' => 'received',
            'direction' => 'income',
            'taxable_amount' => $paidBaseAmount
          ]);
        }

        // Handle TDS tax
        if ($request->boolean('apply_tds')) {
          $path = '';
          if ($request->hasFile('tds_receipt')) {
            $tdsReceipt = $request->file('tds_receipt');
            $filename = 'tds_receipt_' . time() . '_' . uniqid() . '.' . $tdsReceipt->getClientOriginalExtension();

            $destinationPath = public_path('uploads/receipts');
            if (!file_exists($destinationPath)) {
              mkdir($destinationPath, 0755, true);
            }

            $tdsReceipt->move($destinationPath, $filename);
            $path = 'uploads/receipts/' . $filename;
          }

          $income->taxes()->create([
            'taxable_type' => Income::class,
            'taxable_id' => $income->id,
            'tax_type' => 'tds',
            'tax_percentage' => $data['tds_percentage'] ?? 0,
            'tax_amount' => $tdsAmountForCurrent,
            'payment_status' => $data['tds_status'] ?? 'not_received',
            'amount_after_tds' => $data['amount_after_tds'] ?? 0,
            'tds_proof_path' => $path,
            'direction' => 'income',
            'taxable_amount' => $paidBaseAmount
          ]);
        }
      }

      // Create new income for balance if this is a split payment
      $newIncomeId = null;
      if ($isSplitPayment && $balanceAmount > 0) {
        $newIncome = $income->replicate();
        unset($newIncome->paid_date);
        $newIncome->party_name = $income->party_name;
        $newIncome->amount = $balanceAmount;
        $newIncome->planned_amount = $balancePlannedAmount;
        $newIncome->actual_amount = $balanceBaseAmount;
        $newIncome->balance_amount = $balanceAmount;
        $newIncome->status = 'pending';
        $newIncome->income_date = $request->new_due_date ?? now()->addDays(30)->format('Y-m-d');
        $newIncome->is_partial = true;
        $newIncome->parent_id = $income->id;
        $newIncome->notes = $request->balance_notes ?? 'Balance from partial payment of income #' . $income->id;
        $newIncome->created_at = now();
        $newIncome->updated_at = now();
        $newIncome->save();

        $newIncomeId = $newIncome->id;

        // Create taxes for the balance income
        if ($request->boolean('apply_gst')) {
          $newGstAmount = $gstAmountTotal - $gstAmountForCurrent;
          if ($newGstAmount > 0) {
            $newIncome->taxes()->create([
              'taxable_type' => Income::class,
              'taxable_id' => $newIncomeId,
              'tax_type' => 'gst',
              'tax_percentage' => $data['gst_percentage'] ?? 0,
              'tax_amount' => $newGstAmount,
              'payment_status' => 'not_received',
              'direction' => 'income',
              'taxable_amount' => $balanceBaseAmount
            ]);
          }
        }

        if ($request->boolean('apply_tds')) {
          $newTdsAmount = $tdsAmountTotal - $tdsAmountForCurrent;
          if ($newTdsAmount > 0) {
            $newIncome->taxes()->create([
              'taxable_type' => Income::class,
              'taxable_id' => $newIncomeId,
              'tax_type' => 'tds',
              'tax_percentage' => $data['tds_percentage'] ?? 0,
              'tax_amount' => $newTdsAmount,
              'payment_status' => 'not_received',
              'direction' => 'income',
              'taxable_amount' => $balanceBaseAmount
            ]);
          }
        }
      }


      // Handle receipt uploads
      if ($request->hasFile('receipts')) {
        foreach ($request->file('receipts') as $file) {
          if ($file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;
            $fileSize = $this->formatBytes($file->getSize());

            $destinationPath = public_path('uploads/receipts');
            if (!file_exists($destinationPath)) {
              mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $filePath = 'uploads/receipts/' . $filename;

            // Create receipt record
            $income->receipts()->create([
              'file_name' => $originalName,
              'file_path' => $filePath,
              'file_type' => $extension,
              'file_size' => $fileSize
            ]);
          }
        }
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => $isSplitPayment ?
          'Income created with partial payment. New income created for balance.' :
          'Income saved successfully',
        'new_income_id' => $newIncomeId,
        'is_split_payment' => $isSplitPayment,
        'data' => [
          'original_income' => [
            'id' => $income->id,
            'client_name' => $income->party_name,
            'amount' => $income->amount,
            'actual_amount' => $income->actual_amount,
            'balance' => $income->balance,
            'status' => $income->status
          ]
        ]
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ], 500);
    }
  }


  /**
   * Create tax record
   */
  private function createTaxRecord($expense, $type, $data)
  {
    // Check if you have a Tax model, otherwise adjust accordingly
    if (class_exists('App\Models\Tax')) {
      Tax::create([
        'expense_id' => $expense->id,
        'tax_type' => $type,
        'percentage' => $data['percentage'],
        'amount' => $data['amount'],
        'status' => $data['status'],
      ]);
    } else {
      // If you don't have a separate Tax model, store in expense_taxes table
      DB::table('expense_taxes')->insert([
        'expense_id' => $expense->id,
        'tax_type' => $type,
        'percentage' => $data['percentage'],
        'amount' => $data['amount'],
        'status' => $data['status'],
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }

  /**
   * Upload receipt file
   */
  private function uploadReceipt($file, $expenseId, $type = 'general')
  {
    if ($file->isValid()) {
      $originalName = $file->getClientOriginalName();
      $extension = $file->getClientOriginalExtension();
      $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;

      $destinationPath = public_path('uploads/receipts');
      $fileSize = $file->getSize();

      if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0755, true);
      }

      $file->move($destinationPath, $filename);
      $filePath = 'uploads/receipts/' . $filename;

      Receipt::create([
        'expense_id' => $expenseId,
        'file_name' => $originalName,
        'file_path' => $filePath,
        'file_type' => $extension,
        'file_size' => $fileSize,
        'type' => $type,
      ]);
    }
  }

  public function edit($id)
  {
    try {
      $income = Income::with([
        'company',
        'category',
        'invoice', // Added this
        'taxes' => function ($query) {
          $query->where('tax_type', 'gst')->orWhere('tax_type', 'tds');
        }
      ])->findOrFail($id);

      // Get GST and TDS tax records
      $gstTax = $income->taxes->where('tax_type', 'gst')->where('direction', 'income')->where('taxable_type', 'App\Models\Income')->where('taxable_id', $id)->first();
      $tdsTax = $income->taxes->where('tax_type', 'tds')->where('direction', 'income')->where('taxable_type', 'App\Models\Income')->where('taxable_id', $id)->first();

      // Calculate amounts and determine if actual_amount needs INR conversion for the UI
      $currency = $income->invoice ? $income->invoice->currency : ($income->currency ?? 'INR');
      $baseAmount = floatval($income->actual_amount);

      // $baseAmount is already in INR in the database, no conversion needed.
      
      $gstAmount = $gstTax ? $gstTax->tax_amount : 0;
      $tdsAmount = $tdsTax ? $tdsTax->tax_amount : 0;
      $plannedAmount = $income->planned_amount;
      $payableAmount = $income->amount;
      $balance = $income->balance_amount;

      $clientDetailsParsed = is_string($income->client_details) ? json_decode($income->client_details, true) : $income->client_details;
      if (empty($clientDetailsParsed) && $income->invoice) {
          $clientDetailsParsed = is_string($income->invoice->client_details) ? json_decode($income->invoice->client_details, true) : $income->invoice->client_details;
      }
      $clientDetailsParsed = $clientDetailsParsed ?: [];
      $mobileNumber = $clientDetailsParsed['mobile_number'] ?? ($clientDetailsParsed['phone'] ?? null);

      return response()->json([
        'success' => true,
        'income' => [
          'id' => $income->id,
          'company_id' => $income->company_id,
          'client_name' => $income->party_name ?? $income->description,
          'actual_amount' => $baseAmount,
          'planned_amount' => $plannedAmount,
          'invoice_id' => $income->invoice_id,
          'currency' => $income->invoice ? $income->invoice->currency : ($income->currency ?? 'INR'),
          'amount' => $payableAmount,
          'frequency' => $income->frequency,
          'due_day' => $income->due_day,
          'status' => $income->status,
          'income_date' => $income->income_date,
          'mail_status' => $income->mail_status ? 1 : 0,
          'notes' => $income->notes,
          'party_name' => $income->party_name,
          'mobile_number' => $mobileNumber,
          'payment_mode' => $income->payment_mode,
          'paid_date' => $income->paid_date,
          'due_date' => $income->due_date,

          'gst_percentage' => $gstTax ? $gstTax->tax_percentage : 0,
          'gst_amount' => $gstAmount,
          'tds_percentage' => $tdsTax ? $tdsTax->tax_percentage : 0,
          'tds_amount' => $tdsAmount,
          'amount_after_tds' => $plannedAmount - $tdsAmount,
          'grand_total' => $payableAmount, // Payable
          'tds_status' => $tdsTax ? $tdsTax->payment_status : 'not_received',
          'received_amount' => $income->received_amount ?? 0,
          'balance_amount' => $balance,
          'original_total_base' => $income->schedule_amount ?? 0,
          'source' => $income->source,
          'category_id' => $income->category_id,
          'parent_id' => $income->parent_id,
          'is_partial' => $income->is_partial,
          'tdsTax' => $tdsTax,
          'gstTax' => $gstTax,
          'conversion_cost' => $income->conversion_cost ?? 0,
        ],
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Income not found: ' . $e->getMessage()
      ], 404);
    }
  }

  public function splitHistory($id)
  {
    try {
      $income = Income::with(['parent', 'children', 'taxes'])->findOrFail($id);

      // Traverse up to find the ultimate root income
      $rootIncome = $income;
      while ($rootIncome->parent_id) {
        $rootIncome = Income::findOrFail($rootIncome->parent_id);
      }
      $rootId = $rootIncome->id;

      // Recursively get all parts of the split (descendants)
      $allSplits = collect([$rootIncome]);
      $toProcess = collect([$rootId]);

      while ($toProcess->isNotEmpty()) {
        $nextLevel = Income::with('taxes')->whereIn('parent_id', $toProcess)->get();
        if ($nextLevel->isEmpty())
          break;
        $allSplits = $allSplits->concat($nextLevel);
        $toProcess = $nextLevel->pluck('id');
      }

      $allSplits = $allSplits->unique('id')->sortBy('created_at')->values();

      $paidStatuses = ['received', 'settle', 'paid'];
      $totalPaid = $allSplits->whereIn('status', $paidStatuses)->sum('amount');
      $totalBalance = $allSplits->whereNotIn('status', $paidStatuses)->sum('amount');
      $originalSum = $allSplits->sum('amount');

      $rate = 1;
      if ($rootIncome->invoice && $rootIncome->invoice->currency != 'INR' && $rootIncome->invoice->original_currency_amount > 0) {
          $rate = $rootIncome->invoice->converted_amount / $rootIncome->invoice->original_currency_amount;
      }

            $tdsBalance = $allSplits->sum(fn($split) => $split->taxes->where('tax_type', 'tds')->whereNotIn('payment_status', ['paid', 'received'])->sum('tax_amount'));

      $rootGst = $rootIncome->taxes->where('tax_type', 'gst')->first();
      $rootTds = $rootIncome->taxes->where('tax_type', 'tds')->first();
      $gstPercentage = $rootGst ? $rootGst->tax_percentage : 0;
      $tdsPercentage = $rootTds ? $rootTds->tax_percentage : 0;

      $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹'];
      $currencyCode = $rootIncome->invoice ? $rootIncome->invoice->currency : ($rootIncome->currency ?? 'INR');
      $currencySymbol = $currencySymbols[strtoupper($currencyCode)] ?? '₹';

      return response()->json([
        'success' => true,
        'currency_symbol' => $currencySymbol,
        'currency' => strtoupper($currencyCode),
        'current_expense' => [
          'id' => $income->id,
          'planned_amount' => $income->amount,
          'status' => $income->status
        ],
        'parent_expense' => $income->parent_id ? [
          'id' => $rootIncome->id,
          'planned_amount' => $originalSum,
          'original_total' => $rootIncome->original_amount ?? $rootIncome->actual_amount ?? $originalSum,
          'status' => $rootIncome->status,
          'created_at' => $rootIncome->created_at->toIso8601String(),
          'gst_amount' => $allSplits->sum(function ($split) { return $split->taxes->where('tax_type', 'gst')->sum('tax_amount'); }) / $rate,
          'tds_amount' => $allSplits->sum(function ($split) { return $split->taxes->where('tax_type', 'tds')->sum('tax_amount'); }) / $rate,
          'gst_percentage' => $gstPercentage,
          'tds_percentage' => $tdsPercentage
        ] : null,
        'children' => $allSplits->map(function ($split) use ($rate) {
          return [
            'id' => $split->id,
            'planned_amount' => $split->amount,
            'actual_amount' => $split->actual_amount ?? 0,
            'status' => $split->status,
            'created_at' => $split->created_at->toIso8601String(),
            'paid_date' => $split->status =='received' ? $split->income_date : 'N/A',
            'due_date' => $split->due_date,
            'gst_amount' => $split->taxes->where('tax_type', 'gst')->sum('tax_amount') / $rate,
            'tds_amount' => $split->taxes->where('tax_type', 'tds')->sum('tax_amount') / $rate
          ];
        })->values(),
        'summary' => [
          'original_amount' => $originalSum,
          'total_paid' => $totalPaid,
          'total_balance' => $totalBalance,
          'tds_balance' => $tdsBalance,
          'split_count' => $allSplits->count()
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error loading split history: ' . $e->getMessage()
      ]);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      DB::beginTransaction();

      $income = Income::findOrFail($id);

      // Validate request
      $validated = $request->validate([
        'company_id' => 'required|exists:companies,id',
        'client_name' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'status' => 'required|in:settle,due,convert_to_tds',
        'notes' => 'nullable|string',
        'settle_notes' => 'required_if:status,settle,paid|nullable|string',
        // Tax fields
        'apply_gst' => 'nullable|boolean',
        'gst_percentage' => 'nullable|numeric|min:0|max:100',
        'gst_amount' => 'nullable|numeric|min:0',
        'apply_tds' => 'nullable|boolean',
        'tds_percentage' => 'nullable|numeric|min:0|max:100',
        'tds_amount' => 'nullable|numeric|min:0',
        'amount_after_tds' => 'nullable|numeric|min:0',
        'grand_total' => 'nullable|numeric|min:0',
        'tds_status' => 'nullable|in:received,not_received',

        // Received amounts
        'received_amount' => 'nullable|numeric|min:0',
        'received_date' => 'nullable|date',
        'balance_amount' => 'nullable|numeric',

        // Payment Mode Validation
        'payment_mode' => 'nullable|string',
        'bank_name' => 'nullable|required_if:payment_mode,bank_transfer,cheque|string',
        'upi_type' => 'nullable|required_if:payment_mode,upi|string',
        'upi_number' => 'nullable|required_if:payment_mode,upi|string',

        // File uploads
        'tds_receipt' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        'receipts.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
      ]);

      $isConvertToTds = false;
      if ($validated['status'] === 'convert_to_tds') {
        $isConvertToTds = true;
        $actualTotalBase = $validated['amount'] ?? 0;
        $gAmount = $request->boolean('apply_gst') ? ($validated['gst_amount'] ?? 0) : 0;
        $pAmount = $actualTotalBase + $gAmount;
        $rAmount = $validated['received_amount'] ?? $income->received_amount ?? 0;
        $targetTds = $pAmount - $rAmount;

        if ($targetTds > 0) {
          $validated['apply_tds'] = 1;
          $validated['tds_amount'] = $targetTds;
          $validated['tds_percentage'] = ($pAmount > 0) ? ($targetTds / $pAmount) * 100 : 0;

          $request->merge([
            'apply_tds' => 1,
            'tds_amount' => $targetTds,
            'tds_percentage' => $validated['tds_percentage']
          ]);
        }
        $validated['status'] = 'settle';
      }

      // Calculate base amounts and taxes
      $actualTotalBase = $validated['amount'] ?? 0;
      
      // Determine exchange rate if foreign currency
      $exchangeRate = 1;
      if ($income->invoice && $income->invoice->currency != 'INR' && $income->invoice->original_currency_amount > 0) {
          $exchangeRate = $income->invoice->converted_amount / $income->invoice->original_currency_amount;
      } elseif ($income->actual_amount > 0 && $income->amount > 0 && ($income->actual_amount < ($income->amount / 2))) {
          // Fallback if no invoice relation but it seems converted
          $exchangeRate = $income->planned_amount > 0 ? ($income->amount / $income->planned_amount) : ($income->amount / $income->actual_amount);
      }
      $gstAmountTotal = $request->boolean('apply_gst') ? ($validated['gst_amount'] ?? 0) : 0;
      $tdsAmountTotal = $request->boolean('apply_tds') ? ($validated['tds_amount'] ?? 0) : 0;
      $conversionCost = $income->conversion_cost ?? 0;
      
      $plannedAmountTotal = $actualTotalBase + $gstAmountTotal;
      $payableAmountTotal = $plannedAmountTotal - $tdsAmountTotal - $conversionCost;
      
      // Fix rounding if needed
      // Note: $actualTotalBase from frontend might be INR instead of actual_amount for USD invoices
      if (abs($payableAmountTotal - floatval($income->amount)) <= 1.00) {
          $payableAmountTotal = floatval($income->amount);
      }
      
      $receivedAmount = $validated['received_amount'] ?? $income->received_amount ?? 0;

      $isSplitPayment = $validated['status'] === 'due' &&
        $receivedAmount > 0 &&
        $receivedAmount < $payableAmountTotal;

      $originalActualAmount = floatval($income->actual_amount);
      $isForeignCurrency = ($income->invoice && $income->invoice->currency != 'INR') || ($income->currency && $income->currency != 'INR');

      if ($isSplitPayment && $payableAmountTotal > 0) {
        $proportion = $receivedAmount / $payableAmountTotal;
        $gstAmountForCurrent = $gstAmountTotal * $proportion;
        $tdsAmountForCurrent = $tdsAmountTotal * $proportion;
        
        // actual_amount in DB stores the foreign currency value if foreign, else INR
        $paidBaseAmount = $isForeignCurrency ? ($originalActualAmount * $proportion) : ($actualTotalBase * $proportion);
        $balanceBaseAmount = $isForeignCurrency ? ($originalActualAmount - $paidBaseAmount) : ($actualTotalBase - $paidBaseAmount);
        
        $paidPlannedAmount = $plannedAmountTotal * $proportion;
        $conversionCostForCurrent = $conversionCost * $proportion;
        
        $balancePlannedAmount = $plannedAmountTotal - $paidPlannedAmount;
        $balanceAmount = $payableAmountTotal - $receivedAmount;
        $balanceConversionCost = $conversionCost - $conversionCostForCurrent;
      } else {
        $gstAmountForCurrent = $gstAmountTotal;
        $tdsAmountForCurrent = $tdsAmountTotal;
        
        $paidBaseAmount = $isForeignCurrency ? $originalActualAmount : $actualTotalBase;
        $balanceBaseAmount = 0;
        
        $paidPlannedAmount = $plannedAmountTotal;
        $balancePlannedAmount = 0;
        $balanceAmount = $payableAmountTotal - $receivedAmount;
      }

      // Update client_details with the new mobile_number if provided
      $existingClientDetails = is_string($income->client_details) ? json_decode($income->client_details, true) : (is_array($income->client_details) ? $income->client_details : []);
      if (isset($validated['mobile_number'])) {
          $existingClientDetails['mobile_number'] = $validated['mobile_number'];
      }

      $paidBaseAmountToSave = $paidBaseAmount;
      $actualTotalBaseToSave = $actualTotalBase;
      $balanceBaseAmountToSave = $balanceBaseAmount;

      $incomeData = [
        'party_name' => $validated['client_name'] ?? $income->party_name,
        'amount' => $isSplitPayment ? $receivedAmount : $payableAmountTotal,
        'received_amount' => $receivedAmount,
        'planned_amount' => $paidPlannedAmount,
        'actual_amount' => $paidBaseAmountToSave,
        'original_amount' => $actualTotalBase,
        'schedule_amount' => $plannedAmountTotal,
        'balance_amount' => $isSplitPayment ? 0 : max(0, $balanceAmount),
        'status' => $isSplitPayment ? 'received' : ($balanceAmount <= 0 ? 'received' : ($receivedAmount > 0 ? 'received' : 'pending')),
        'income_date' => $isSplitPayment ? $income->income_date : ($validated['received_date'] ?? $income->income_date),
        'paid_date' => ($isSplitPayment || $receivedAmount > 0) ? ($validated['received_date'] ?? now()->format('Y-m-d')) : $income->paid_date,
        'payment_mode' => $validated['payment_mode'] ?? $income->payment_mode,
        'client_details' => json_encode($existingClientDetails),
        'notes' => $validated['notes'] ?? $income->notes,
        'settle_notes' => $validated['settle_notes'] ?? $income->settle_notes,
        'company_id' => $income->company_id,
        'is_partial' => $isSplitPayment,
        'conversion_cost' => $isSplitPayment ? $conversionCostForCurrent : $conversionCost,
        'due_date' => $isSplitPayment ? $income->due_date : ($request->due_date ?? $income->due_date),
      ];

      if ($receivedAmount > 0) {
        $incomeData['income_date'] = $validated['received_date'] ?? now()->format('Y-m-d');
      }

      // Update income
      $income->update($incomeData);

      // Handle GST tax
      if ($request->boolean('apply_gst')) {
        $gstPercentage = $validated['gst_percentage'] ?? 0;
        Tax::updateOrCreate(
          [
            'taxable_type' => Income::class,
            'taxable_id' => $income->id,
            'tax_type' => 'gst',
            'direction' => 'income'
          ],
          [
            'tax_amount' => $gstAmountForCurrent,
            'tax_percentage' => $gstPercentage,
            'payment_status' => 'received',
            'company_id' => $income->company_id,
            'taxable_amount' => $paidBaseAmount
          ]
        );
      } else {
        Tax::where('taxable_type', Income::class)
          ->where('taxable_id', $income->id)
          ->where('tax_type', 'gst')
          ->delete();
      }

      // Handle TDS tax
      if ($request->boolean('apply_tds')) {
        $tdsPercentage = $validated['tds_percentage'] ?? 0;
        $filePath = null;
        if ($request->hasFile('tds_receipt')) {
          $file = $request->file('tds_receipt');
          if ($file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = 'tds_receipt_' . time() . '_' . uniqid() . '.' . $extension;
            $destinationPath = public_path('uploads/receipts');
            if (!file_exists($destinationPath)) {
              mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $filePath = 'uploads/receipts/' . $filename;
          }
        }
        Tax::updateOrCreate(
          [
            'taxable_type' => Income::class,
            'taxable_id' => $income->id,
            'tax_type' => 'tds',
            'direction' => 'income'
          ],
          [
            'tax_amount' => $tdsAmountForCurrent,
            'tax_percentage' => $tdsPercentage,
            'payment_status' => $validated['tds_status'] ?? 'not_received',
            'company_id' => $income->company_id,
            'tds_proof_path' => $filePath,
            'taxable_amount' => $paidBaseAmount

          ]
        );
      } else {
        Tax::where('taxable_type', Income::class)
          ->where('taxable_id', $income->id)
          ->where('tax_type', 'tds')
          ->delete();
      }

      // Create new income for balance if this is a split payment
      $newIncomeId = null;
      if ($isSplitPayment && $balanceBaseAmount > 0) {

        $newIncome = $income->replicate();
        unset($newIncome->paid_date);
        $newIncome->party_name = $income->party_name;
        $newIncome->amount = $balanceAmount;
        $newIncome->actual_amount = $balanceBaseAmountToSave;
        $newIncome->planned_amount = $balancePlannedAmount;
        $newIncome->balance_amount = $balanceAmount;
        $newIncome->status = 'pending';
        $newIncome->due_date = $request->new_due_date ?? $request->due_date ?? now()->addDays(30)->format('Y-m-d');
        $newIncome->income_date = $income->income_date;
        $newIncome->is_partial = true;
        $newIncome->parent_id = $income->id;
        $newIncome->conversion_cost = $balanceConversionCost;
        $newIncome->notes = $request->balance_notes ?? 'Balance from partial payment of income #' . $income->id;
        $newIncome->created_at = now();
        $newIncome->updated_at = now();
        $newIncome->save();

        $newIncomeId = $newIncome->id;

        // Create GST tax for new income if applicable
        if ($request->boolean('apply_gst') && $gstAmountTotal > 0) {
          $newGstAmount = $gstAmountTotal - $gstAmountForCurrent;
          if ($newGstAmount > 0) {
            Tax::create([
              'taxable_type' => Income::class,
              'taxable_id' => $newIncomeId,
              'tax_type' => 'gst',
              'tax_amount' => $newGstAmount,
              'tax_percentage' => $validated['gst_percentage'] ?? 0,
              'payment_status' => 'not_received',
              'direction' => 'income',
              'company_id' => $income->company_id,
              'taxable_amount' => $balanceBaseAmount,
            ]);
          }
        }

        // Create TDS tax for new income if applicable
        if ($request->boolean('apply_tds') && $tdsAmountTotal > 0) {
          $newTdsAmount = $tdsAmountTotal - $tdsAmountForCurrent;
          if ($newTdsAmount > 0) {
            Tax::create([
              'taxable_type' => Income::class,
              'taxable_id' => $newIncomeId,
              'tax_type' => 'tds',
              'tax_amount' => $newTdsAmount,
              'tax_percentage' => $validated['tds_percentage'] ?? 0,
              'payment_status' => 'not_received',
              'direction' => 'income',
              'company_id' => $income->company_id,
              'taxable_amount' => $balanceBaseAmount,

            ]);
          }
        }
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => $isSplitPayment ?
          'Partial payment recorded. Original income updated and new income created for balance.' :
          'Income updated successfully!',
        'new_income_id' => $newIncomeId,
        'is_split_payment' => $isSplitPayment,
        'data' => [
          'original_income' => [
            'id' => $income->id,
            'amount' => $income->amount,
            'actual_amount' => $income->actual_amount,
            'received_amount' => $income->received_amount,
            'balance_amount' => $income->balance_amount,
            'status' => $income->status,
            'net_payable' => $payableAmountTotal
          ],
          'taxes' => [
            'gst_amount' => $gstAmountForCurrent,
            'tds_amount' => $tdsAmountForCurrent
          ],
          'split_info' => [
            'is_split' => $isSplitPayment,
            'balance' => $balanceAmount,
            'new_income_created' => !is_null($newIncomeId)
          ]
        ]
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Income Update Error: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error updating income: ' . $e->getMessage()
      ], 500);
    }
  }

  public function show($id)
  {
    try {
      $income = Income::with([
        'company',
        'taxes',
        'invoice' // Load invoice
      ])->findOrFail($id);
      $clientName = $income->party_name;
      $client = null;
      if ($income->party_name) {
      }

      $gstItems = [];
      $tdsItems = [];
      $gstTotal = 0;
      $tdsTotal = 0;

      if ($income->taxes && count($income->taxes) > 0) {
        $gstItems = $income->taxes->filter(function ($tax) {
          return $tax->tax_type === 'gst';
        })->values()->toArray();

        $tdsItems = $income->taxes->filter(function ($tax) {
          return $tax->tax_type === 'tds';
        })->values()->toArray();

        foreach ($gstItems as $gst) {
          $gstTotal += floatval($gst['tax_amount']);
        }

        foreach ($tdsItems as $tds) {
          $tdsTotal += floatval($tds['tax_amount']);
        }
      }

      // Calculate total without TDS
      $totalWithoutTds = floatval($income->amount) + $gstTotal;

      // Calculate original totals for split payments
      $originalGstTotal = $gstTotal;
      $originalTdsTotal = $tdsTotal;
      $totalPaidAmount = 0;

      if ($income->is_partial || $income->parent_id) {
        $rootId = $income->id;
        $current = clone $income;
        while ($current->parent_id) {
          $rootId = $current->parent_id;
          $current = \App\Models\Income::find($rootId);
        }

        $allIds = [$rootId];
        $children = \App\Models\Income::where('parent_id', $rootId)->pluck('id')->toArray();
        while (!empty($children)) {
          $allIds = array_merge($allIds, $children);
          $children = \App\Models\Income::whereIn('parent_id', $children)->pluck('id')->toArray();
        }

        $allSplits = \App\Models\Income::with('taxes')->whereIn('id', $allIds)->get();

        $originalGstTotal = 0;
        $originalTdsTotal = 0;
        $originalBaseTotal = 0;
        $totalPaidAmount = 0;

        // Calculate paid amount from all splits
        foreach ($allSplits as $split) {
          if ($split->status === 'received' || $split->status === 'paid') {
            $totalPaidAmount += floatval($split->amount);
          }
        }

        // Calculate original taxes from root income to ensure accuracy
        $rootIncome = $allSplits->where('id', $rootId)->first();
        if ($rootIncome) {
          // original_amount is the pure base amount (before GST/TDS)
          // schedule_amount is the planned amount (base + GST) 
          $pureBaseAmount = floatval($rootIncome->original_amount ?: $rootIncome->actual_amount ?: $rootIncome->amount);
          $originalBaseTotal = $pureBaseAmount;
          if ($rootIncome->taxes) {
            $gstTax = $rootIncome->taxes->where('tax_type', 'gst')->first();
            if ($gstTax && $gstTax->tax_percentage > 0) {
              $originalGstTotal = ($pureBaseAmount * floatval($gstTax->tax_percentage)) / 100;
            } else {
              $originalGstTotal = $allSplits->pluck('taxes')->flatten()->where('tax_type', 'gst')->sum('tax_amount');
            }

            $tdsTax = $rootIncome->taxes->where('tax_type', 'tds')->first();
            if ($tdsTax && $tdsTax->tax_percentage > 0) {
              $originalTdsTotal = ($pureBaseAmount * floatval($tdsTax->tax_percentage)) / 100;
            } else {
              $originalTdsTotal = $allSplits->pluck('taxes')->flatten()->where('tax_type', 'tds')->sum('tax_amount');
            }
          }
        }
      } else {
        $originalBaseTotal = floatval($income->original_amount ?: $income->actual_amount ?: $income->amount);
      }

      // Create invoice data structure
      $lineItemsData = $income->line_items;

      if (is_string($lineItemsData)) {
        $lineItemsData = json_decode($lineItemsData);
      }
      $lineItems = [];
      $currencyCode = $income->invoice ? $income->invoice->currency : ($income->currency ?? 'INR');
      $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹'];
      $currencySymbol = $currencySymbols[strtoupper($currencyCode)] ?? '₹';

      $invoiceLineItems = $income->invoice && !empty($income->invoice->line_items)
        ? (is_string($income->invoice->line_items) ? json_decode($income->invoice->line_items, true) : $income->invoice->line_items)
        : (!empty($income->line_items) ? (is_string($income->line_items) ? json_decode($income->line_items, true) : $income->line_items) : []);

      if (!empty($invoiceLineItems)) {
        foreach ($invoiceLineItems as $item) {
          $lineItems[] = [
            'description' => $item['description'] ?? $item->description ?? '',
            'quantity' => (int) ($item['quantity'] ?? $item->quantity ?? 1),
            'rate' => (float) ($item['rate'] ?? $item->rate ?? 0),
            'amount' => (float) ($item['amount'] ?? $item->amount ?? 0),
            'tax_type' => $gstItems ? 'gst' : null,
            'tax_percentage' => $gstItems[0]['tax_percentage'] ?? 0,
            'currency' => $currencySymbol,
          ];
        }
      }

      $clientDetailsParsed = is_string($income->client_details) ? json_decode($income->client_details, true) : $income->client_details;
      if (empty($clientDetailsParsed) && $income->invoice) {
          $clientDetailsParsed = is_string($income->invoice->client_details) ? json_decode($income->invoice->client_details, true) : $income->invoice->client_details;
      }
      $clientDetailsParsed = $clientDetailsParsed ?: [];

      $invoice = [
        'id' => $income->id,
        'invoice_number' => $income->invoice
          ? $income->invoice->invoice_number
          : ($income->invoice_id ?? 'INV-' . str_pad($income->id, 6, '0', STR_PAD_LEFT)),

        'status' => $income->status,
        'type' => $income->income_type ?? 'invoice',
        'issue_date' => $income->income_date,
        'due_date' => $income->due_date ?? $income->income_date,

        'is_split' => $income->is_partial,
        'parent_id' => $income->parent_id,
        'schedule_amount' => $income->schedule_amount,
        'balance_amount' => $income->balance_amount,
        'receivable_amount' => $income->receivable_amount,

        'original_gst_total' => $originalGstTotal,
        'original_tds_total' => $originalTdsTotal,
        'original_base_amount' => $originalBaseTotal,
        'total_paid_amount' => $totalPaidAmount,

        'subtotal' => floatval($income->original_amount ?? $income->actual_amount ?? $income->amount),
        'actual_amount' => floatval($income->actual_amount ?? $income->amount),

        'total_amount' => floatval($income->schedule_amount ?? $income->planned_amount ?? ($income->amount + $gstTotal - $tdsTotal)),

        'purpose_comment' => $income->notes,
        'terms_conditions' => $income->invoice->terms_conditions ?? null, 

        'tax_type' => $income->tax_type ?? 'GST',
        'currency' => $income->invoice ? $income->invoice->currency : ($income->currency ?? 'INR'),

        'original_currency_amount' => $income->invoice ? $income->invoice->original_currency_amount : ($income->original_amount ?? $income->planned_amount),
        'invoice_converted_amount' => $income->invoice ? $income->invoice->converted_amount : ($income->schedule_amount ?? $income->planned_amount ?? $income->amount),
        'converted_amount' => $income->schedule_amount ?? $income->planned_amount ?? ($income->invoice->converted_amount ?? $income->amount),
        'conversion_rate' => $income->conversion_rate_percentage ?? ($income->invoice->conversion_rate ?? 0),
        'original_conversion_cost' => $income->invoice ? $income->invoice->conversion_cost : ($income->parent_id ? \App\Models\Income::find($income->parent_id)?->conversion_cost : $income->conversion_cost),
        'conversion_cost' => $income->conversion_cost ?? ($income->invoice->conversion_cost ?? 0),
        'amount' => $income->amount,

        'received_amount' => floatval($income->received_amount ?? $income->amount),

        'company' => $income->company ? [
          'id' => $income->company->id,
          'name' => $income->company->name,
          'gstin' => $income->company->gstin ?? null,
          'address' => $income->company->address ?? null,
          'state' => $income->company->state ?? null,
          'email' => $income->company->email ?? null,
        ] : [
          'name' => 'Unknown Company',
          'gstin' => null,
          'address' => null,
          'state' => null,
        ],

        'client_details' => [
          'name' => $clientDetailsParsed['name'] ?? $clientName,
          'email' => $clientDetailsParsed['email'] ?? null,
          'phone' => $clientDetailsParsed['mobile_number'] ?? ($clientDetailsParsed['phone'] ?? null),
          'gstin' => $clientDetailsParsed['gstin'] ?? null,
          'address' => $clientDetailsParsed['billing_address'] ?? ($clientDetailsParsed['address'] ?? null),
        ],

        'taxes' => array_merge($gstItems, $tdsItems),
        'line_items' => $lineItems,
      ];

      return response()->json([
        'success' => true,
        'invoice' => $invoice
      ]);
    } catch (\Exception $e) {
      \Log::error('Error fetching income invoice data: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error fetching invoice details: ' . $e->getMessage()
      ], 500);
    }
  }
  
  public function destroy($id)
  {
    $income = Income::findOrFail($id);
    $income->delete();

    return response()->json([
      'success' => true,
      'message' => 'Income deleted successfully!',
    ]);
  }

  private function formatBytes($bytes, $precision = 2)
  {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }

  public function markAsReceived($id)
  {
    $income = Income::findOrFail($id);
    $income->update(['status' => 'received']);

    return response()->json([
      'success' => true,
      'message' => 'Income marked as received!',
    ]);
  }

  public function export(Request $request)
  {
    // Export logic here (use Maatwebsite/Excel package)
    // Return Excel file download
  }

  // ========== UPCOMING PAYMENTS PAGE ==========
  public function upcoming(Request $request)
  {
    // Get filters from request
    $dateRange = $request->get('range', '7days');
    $companyId = $request->get('company');
    $type = $request->get('type');
    $status = $request->get('status');
    $tab = $request->get('tab', 'all');
    $fromDate = $request->get('from_date', date('Y-m-d'));
    $toDate = $request->get('to_date', date('Y-m-d', strtotime('+30 days')));

    // Calculate date range based on filter
    switch ($dateRange) {
      case 'today':
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        break;
      case '7days':
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+7 days'));
        break;
      case '30days':
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+30 days'));
        break;
      case 'month':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        break;
      default:
        $startDate = $fromDate;
        $endDate = $toDate;
    }

    // Get upcoming incomes
    $incomeQuery = Income::with('category')->whereBetween('income_date', [$startDate, $endDate])
      ->with('company')
      ->orderBy('income_date', 'asc');

    // Get upcoming expenses
    $expenseQuery = Expense::with('categoryRelation')
      // ->whereBetween('due_date', [$startDate, $endDate])
      ->where('status', 'upcoming')
      ->with(['company', 'expenseType'])
      ->orderBy('due_date', 'asc');

    // Apply company filter
    if ($companyId) {
      $incomeQuery->where('company_id', $companyId);
      $expenseQuery->where('company_id', $companyId);
    }

    // Apply status filter
    if ($status) {
      if ($status === 'paid') {
        $incomeQuery->where('status', 'received');
        $expenseQuery->where('status', 'paid');
      } else {
        $incomeQuery->where('status', $status);
        $expenseQuery->where('status', $status);
      }
    }

    // Get data
    $upcomingIncomes = $incomeQuery->get();
    $upcomingExpenses = $expenseQuery->get();

    // Combine and process payments
    $allPayments = collect();
    $today = Carbon::today();

    foreach ($upcomingIncomes as $income) {
      // echo $income->category->name;
      $daysLeft = $today->diffInDays(Carbon::parse($income->income_date), false);
      $isOverdue = $daysLeft < 0;

      $allPayments->push([
        'type' => 'income',
        'id' => $income->id,
        'date' => $income->income_date,
        'description' => $income->source,
        'amount' => $income->amount,
        'status' => $income->status,
        'company' => $income->company->name ?? 'N/A',
        'category' => ucfirst($income->category->name ?? ''),
        'party_name' => '-',
        'source' => 'income',
        'days_left' => $daysLeft,
        'is_overdue' => $isOverdue,
        'is_today' => $daysLeft === 0,
        'is_tomorrow' => $daysLeft === 1,
        'payment_date' => $income->income_date,
        'payment_type' => 'Credit',
      ]);
    }
    foreach ($upcomingExpenses as $expense) {
      $daysLeft = $today->diffInDays(Carbon::parse($expense->due_date), false);
      $isOverdue = $daysLeft < 0 || $expense->status === 'overdue';

      $allPayments->push([
        'type' => 'expense',
        'id' => $expense->id,
        'date' => $expense->due_date,
        'description' => $expense->name,
        'amount' => -$expense->planned_amount,
        'status' => $expense->status,
        'company' => $expense->company->name ?? 'N/A',
        'category' => $expense->categoryRelation->name ?? 'Other',
        'party_name' => $expense->party_name ?? '-',
        'source' => $expense->categoryRelation->category_type === 'standard_fixed' || 'standard_editable' ? 'Standard' : 'Non-Standard',
        'days_left' => $daysLeft,
        'is_overdue' => $isOverdue,
        'is_today' => $daysLeft === 0,
        'is_tomorrow' => $daysLeft === 1,
        'payment_date' => $expense->due_date,
        'payment_type' => 'Debit',
      ]);
    }

    // Apply type filter
    if ($type === 'credit') {
      $allPayments = $allPayments->where('type', 'income');
    } elseif ($type === 'debit') {
      $allPayments = $allPayments->where('type', 'expense');
    }

    // Apply tab filter
    if ($tab === 'debits') {
      $allPayments = $allPayments->where('type', 'expense');
    } elseif ($tab === 'credits') {
      $allPayments = $allPayments->where('type', 'income');
    } elseif ($tab === 'overdue') {
      $allPayments = $allPayments->where('is_overdue', true);
    }

    // Apply amount filters
    $minAmount = $request->get('min_amount');
    $maxAmount = $request->get('max_amount');

    if ($minAmount) {
      $allPayments = $allPayments->filter(function ($payment) use ($minAmount) {
        return abs($payment['amount']) >= $minAmount;
      });
    }

    if ($maxAmount) {
      $allPayments = $allPayments->filter(function ($payment) use ($maxAmount) {
        return abs($payment['amount']) <= $maxAmount;
      });
    }

    // Sort by date
    $payments = $allPayments->sortBy('date')->values();

    // Group by date for timeline view
    $groupedPayments = $payments->groupBy(function ($item) {
      return Carbon::parse($item['date'])->format('Y-m-d');
    });

    // Calculate statistics
    $stats = [
      'total_upcoming' => $payments->sum(function ($item) {
        return abs($item['amount']);
      }),
      'upcoming_count' => $payments->count(),
      'total_debits' => $payments->where('type', 'expense')->sum(function ($item) {
        return abs($item['amount']);
      }),
      'debits_count' => $payments->where('type', 'expense')->count(),
      'total_credits' => $payments->where('type', 'income')->sum(function ($item) {
        return abs($item['amount']);
      }),
      'credits_count' => $payments->where('type', 'income')->count(),
      'total_overdue' => $payments->where('is_overdue', true)->sum(function ($item) {
        return abs($item['amount']);
      }),
      'overdue_count' => $payments->where('is_overdue', true)->count(),
    ];

    // Get companies for filter
    $companies = Company::where('status', 'active')->get();

    return view('Manager.cash-flow.upcoming_payments', compact(
      'payments',
      'groupedPayments',
      'stats',
      'companies',
      'dateRange',
      'companyId',
      'type',
      'status',
      'tab',
      'fromDate',
      'toDate'
    ));
  }
  // ========== BALANCES & DUES PAGE ==========
  public function balance(Request $request)
  {
    $user = auth()->user();

    $companyId = $request->get('company');
    $sort = $request->get('sort', 'name');

    $query = Company::where('manager_id', $user->id)
      ->where('status', 'active')
      ->with(['incomes', 'expenses']);

    if ($companyId) {
      $query->where('id', $companyId);
    }

    $companies = $query->get()->map(function ($company) {
      $totalIncome = $company->incomes->where('status', 'received')->sum('amount');
      $totalExpenses = $company->expenses->where('status', 'paid')->sum('actual_amount');
      $pendingIncome = $company->incomes->where('status', 'pending')->sum('amount');
      $pendingExpenses = $company->expenses->whereIn('status', ['pending', 'upcoming'])->sum('planned_amount');

      $netBalance = $totalIncome - $totalExpenses;
      $netDues = $pendingIncome - $pendingExpenses;

      return [
        'id' => $company->id,
        'name' => $company->name,
        'code' => $company->code,
        'total_income' => $totalIncome,
        'total_expenses' => $totalExpenses,
        'net_balance' => $netBalance,
        'dues' => [
          'income' => $pendingIncome,
          'expenses' => $pendingExpenses,
          'net' => $netDues,
        ],
        'pending_income_count' => $company->incomes->where('status', 'pending')->count(),
        'pending_expense_count' => $company->expenses->whereIn('status', ['pending', 'upcoming'])->count(),
      ];
    });

    // Apply sorting
    if ($sort === 'balance_desc') {
      $companies = $companies->sortByDesc('net_balance');
    } elseif ($sort === 'balance_asc') {
      $companies = $companies->sortBy('net_balance');
    } elseif ($sort === 'dues_desc') {
      $companies = $companies->sortByDesc('dues.net');
    } else {
      $companies = $companies->sortBy('name');
    }

    $overallStats = [
      'total_income' => $companies->sum('total_income'),
      'total_expenses' => $companies->sum('total_expenses'),
      'net_balance' => $companies->sum('net_balance'),
      'total_dues_income' => $companies->sum('dues.income'),
      'total_dues_expenses' => $companies->sum('dues.expenses'),
      'total_net_dues' => $companies->sum('dues.net'),
    ];

    return view('Manager.cash-flow.balances', compact('companies', 'overallStats'));
  }

  // Add these methods to your controller
  public function companyDuesDetails($id)
  {
    $company = Company::with([
      'incomes' => function ($q) {
        $q->where('status', 'pending');
      },
      'expenses' => function ($q) {
        $q->whereIn('status', ['pending', 'upcoming']);
      },
    ])->findOrFail($id);

    $pendingIncome = $company->incomes->sum('amount');
    $pendingExpenses = $company->expenses->sum('planned_amount');
    $netDues = $pendingIncome - $pendingExpenses;

    return response()->json([
      'success' => true,
      'company' => $company->only(['id', 'name', 'code']),
      'pendingIncomes' => $company->incomes,
      'pendingExpenses' => $company->expenses,
      'netDues' => $netDues,
    ]);
  }

  public function balanceSummary($id)
  {
    $company = Company::with(['incomes', 'expenses'])->findOrFail($id);

    $pendingIncome = $company->incomes->where('status', 'pending')->sum('amount');
    $pendingExpenses = $company->expenses->whereIn('status', ['pending', 'upcoming'])->sum('planned_amount');
    $netDues = $pendingIncome - $pendingExpenses;

    return response()->json([
      'success' => true,
      'pendingIncome' => $pendingIncome,
      'pendingExpenses' => $pendingExpenses,
      'netDues' => $netDues,
    ]);
  }

  // ========== IMPORT INCOME FROM EXCEL ==========
  public function import(Request $request)
  {
    $request->validate([
      'file' => 'required|mimes:xlsx,xls,csv',
      'company_id' => 'required|exists:companies,id',
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Import functionality will be implemented with Laravel Excel package',
    ]);
  }

  public function sendEmail(Request $request)
  {
    try {
      $request->validate([
        'to_email' => 'required|email',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
      ]);

      $invoice = Income::with(['company', 'invoice'])->find($request->income_id);

      // Properly decode client_details and ensure it's an array
      $clientDetails = $invoice->client_details;

      if (is_string($clientDetails)) {
        $clientDetails = json_decode($clientDetails, true);
      }

      // Ensure clientDetails is always an array
      if (!is_array($clientDetails)) {
        $clientDetails = [];
      }

      // Process variables in message
      $currencyCode = ($invoice->invoice && $invoice->invoice->currency) ? $invoice->invoice->currency : ($invoice->currency ?? 'INR');
      $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹'];
      $currencySymbol = $currencySymbols[strtoupper($currencyCode)] ?? '₹';

      $message = $request->message;
      $message = str_replace('{client_name}', $clientDetails['name'] ?? 'Customer', $message);
      $message = str_replace('{invoice_no}', $invoice->invoice_number, $message);
      $message = str_replace('{due_date}', $invoice->due_date ? date('d M, Y', strtotime($invoice->due_date)) : 'N/A', $message);
      $message = str_replace('{amount}', $currencySymbol . ' ' . number_format($invoice->total_amount, 2), $message);
      $message = str_replace('{company_name}', $invoice->company->name ?? '', $message);

      // Process subject variables
      $subject = $request->subject;
      $subject = str_replace('{client_name}', $clientDetails['name'] ?? 'Customer', $subject);
      $subject = str_replace('{invoice_no}', $invoice->invoice_number, $subject);

      // CC emails
      $ccEmails = [];
      if ($request->cc_email) {
        $ccEmails = array_map('trim', explode(',', $request->cc_email));
        $ccEmails = array_filter($ccEmails, function ($email) {
          return filter_var($email, FILTER_VALIDATE_EMAIL);
        });
      }

      // Convert client_details back to string for the view if it was an array
      if (is_array($clientDetails)) {
        $invoice->client_details = $clientDetails;
      }

      // Send email
      Mail::send('emails.invoice', [
        'invoice' => $invoice,
        'client_details' => $clientDetails, // Pass decoded client details separately
        'custom_message' => $message,
        'subject' => $subject,
      ], function ($mail) use ($invoice, $request, $subject, $ccEmails) {
        $mail->to($request->to_email)
          ->subject($subject)
          ->from('support@petsfolio.in', 'Finance Manager');

        if (!empty($ccEmails)) {
          $mail->cc($ccEmails);
        }

        // Attach PDF if requested
        if ($request->boolean('attach_pdf')) {
          $pdf = $this->generateInvoicePdf($invoice);
          $mail->attachData($pdf->output(), "{$invoice->invoice_number}.pdf", [
            'mime' => 'application/pdf',
          ]);
        }
      });

      // Update the income record's mail_status to 1 (yes)
      Income::where('id', $invoice->id)->update(['mail_status' => 1]);

      return response()->json([
        'success' => true,
        'message' => 'Invoice sent successfully!'
      ]);
    } catch (\Exception $e) {
      \Log::error('Error sending invoice email: ' . $e->getMessage());
      \Log::error('Stack trace: ' . $e->getTraceAsString());

      return response()->json([
        'success' => false,
        'message' => 'Failed to send invoice: ' . $e->getMessage()
      ], 500);
    }
  
  }

  public function downloadFromIncome($incomeId)
  {
    $income = Income::findOrFail($incomeId);

    if (!$income->invoice_id) {
      abort(404, 'No invoice associated with this income');
    }

    return $this->download($income->invoice_id);
  }

  public function download($id)
  {
    $invoice = Invoice::with(['company', 'creator'])->findOrFail($id);
    $company = $invoice->company;

    $clientDetails = $invoice->client_details;
    if (is_string($clientDetails)) {
      $clientDetails = json_decode($clientDetails, true);
    }

    // Get line items (decoded from JSON)
    $lineItems = $invoice->line_items;
    if (is_string($lineItems)) {
      $lineItems = json_decode($lineItems, true);
    }

    // Ensure currency symbol and currency are set
    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹'];
    $invoice->currency = $invoice->currency ?? 'INR';
    $invoice->currency_symbol = $currencySymbols[strtoupper($invoice->currency)] ?? '₹';

    // If total_amount is zero but we have line items, calculate it from items
    if ((!$invoice->total_amount || $invoice->total_amount == 0) && is_array($lineItems)) {
      $calculatedTotal = 0;
      foreach ($lineItems as $item) {
        $calculatedTotal += ($item['amount'] ?? 0);
      }
      if ($calculatedTotal > 0) {
        $invoice->total_amount = $calculatedTotal;
      }
    }

    // Ensure subtotal is present
    if (!$invoice->subtotal || $invoice->subtotal == 0) {
      $gstPercentage = 0;
      if (isset($invoice->taxes) && $invoice->taxes->count() > 0) {
        $gstTax = $invoice->taxes->where('tax_type', 'gst')->first();
        if ($gstTax)
          $gstPercentage = $gstTax->tax_percentage;
      }

      if ($gstPercentage > 0) {
        $invoice->subtotal = $invoice->total_amount / (1 + ($gstPercentage / 100));
      } else {
        $invoice->subtotal = $invoice->total_amount;
      }
    }

    // Generate amount in words (after ensuring total_amount is set)
    $amountInWords = $this->numberToWords($invoice->total_amount, $invoice->currency);
    $invoice->project_note = $invoice->project_note ?? 'Digital Display Videos for Restaurant';
    $invoice->delivery_terms = $invoice->delivery_terms ?? 'Online Delivery';
    $logoBase64 = null;
    $logoPath = public_path('uploads/logo.png');

    if (file_exists($logoPath)) {
      $logoData = file_get_contents($logoPath);
      $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
    $pdf = PDF::loadView('Admin.pdf.invoice-template', compact(
      'invoice',
      'clientDetails',
      'company',
      'lineItems',
      'amountInWords',
      'logoBase64'
    ));

    $pdf->setOptions([
      'defaultFont' => 'DejaVu Sans',
      'isHtml5ParserEnabled' => true,
      'isRemoteEnabled' => false,
      'isPhpEnabled' => false,
      'dpi' => 150,
      'margin_top' => 10,
      'margin_right' => 10,
      'margin_bottom' => 10,
      'margin_left' => 10
    ]);

    $pdf->setPaper('A4', 'portrait');
    $filename = strtolower(str_replace(' ', '_', $invoice->invoice_number)) . '.pdf';

    return $pdf->download($filename);
  }

  public function getInvoiceDetails($id)
  {
    $invoice = Invoice::with(['company'])->findOrFail($id);
    if ($invoice->line_items) {
      $invoice->line_items = json_decode($invoice->line_items, true);
    }
    if ($invoice->client_details) {
      $invoice->client_details = json_decode($invoice->client_details, true);
    }

    return response()->json([
      'success' => true,
      'invoice' => $invoice
    ]);
  }

  private function generateInvoicePdf($invoice)
  {
    $logoBase64 = null;
    $logoPath = public_path('uploads/logo.png');

    if (file_exists($logoPath)) {
      $logoData = file_get_contents($logoPath);
      $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
    $clientDetails = $invoice->client_details;
    if (is_string($clientDetails)) {
      $clientDetails = json_decode($clientDetails, true);
    }
    // Get line items (decoded from JSON)
    $lineItems = $invoice->line_items;
    if (is_string($lineItems)) {
      $lineItems = json_decode($lineItems, true);
    }
    // Ensure currency symbol (check related invoice if this is an Income model)
    $currencyCode = ($invoice->invoice && $invoice->invoice->currency) ? $invoice->invoice->currency : ($invoice->currency ?? 'INR');

    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹'];
    $invoice->currency = $currencyCode;
    $invoice->currency_symbol = $currencySymbols[strtoupper($currencyCode)] ?? '₹';

    // If total_amount is zero but we have line items, calculate it from items
    if ((!$invoice->total_amount || $invoice->total_amount == 0) && is_array($lineItems)) {
      $calculatedTotal = 0;
      foreach ($lineItems as $item) {
        $calculatedTotal += ($item['amount'] ?? 0);
      }
      if ($calculatedTotal > 0) {
        $invoice->total_amount = $calculatedTotal;
      }
    }

    // Ensure subtotal is present
    if (!$invoice->subtotal || $invoice->subtotal == 0) {
      $gstPercentage = 0;
      if (isset($invoice->taxes) && $invoice->taxes->count() > 0) {
        $gstTax = $invoice->taxes->where('tax_type', 'gst')->first();
        if ($gstTax)
          $gstPercentage = $gstTax->tax_percentage;
      }

      if ($gstPercentage > 0) {
        $invoice->subtotal = $invoice->total_amount / (1 + ($gstPercentage / 100));
      } else {
        $invoice->subtotal = $invoice->total_amount;
      }
    }

    $company = $invoice->company;

    // Generate amount in words
    $amountInWords = $this->numberToWords($invoice->total_amount, $invoice->currency);

    $pdf = PDF::loadView('Admin.pdf.invoice-template', [
      'invoice' => $invoice,
      'logoBase64' => $logoBase64,
      'clientDetails' => $clientDetails,
      'company' => $company,
      'lineItems' => $lineItems,
      'amountInWords' => $amountInWords
    ]);
    return $pdf;
  }

  private function numberToWords($number, $currency = 'INR')
  {
    $currency = strtoupper($currency);
    $currencyText = 'Rupees';
    $centsText = 'Paise';

    if ($currency === 'USD') {
      $currencyText = 'Dollars';
      $centsText = 'Cents';
    } elseif ($currency === 'EUR') {
      $currencyText = 'Euros';
      $centsText = 'Cents';
    } elseif ($currency === 'GBP') {
      $currencyText = 'Pounds';
      $centsText = 'Pence';
    }

    $words = [
      0 => 'Zero',
      1 => 'One',
      2 => 'Two',
      3 => 'Three',
      4 => 'Four',
      5 => 'Five',
      6 => 'Six',
      7 => 'Seven',
      8 => 'Eight',
      9 => 'Nine',
      10 => 'Ten',
      11 => 'Eleven',
      12 => 'Twelve',
      13 => 'Thirteen',
      14 => 'Fourteen',
      15 => 'Fifteen',
      16 => 'Sixteen',
      17 => 'Seventeen',
      18 => 'Eighteen',
      19 => 'Nineteen',
      20 => 'Twenty',
      30 => 'Thirty',
      40 => 'Forty',
      50 => 'Fifty',
      60 => 'Sixty',
      70 => 'Seventy',
      80 => 'Eighty',
      90 => 'Ninety',
      100 => 'Hundred',
      1000 => 'Thousand',
      100000 => 'Lakh',
      10000000 => 'Crore'
    ];

    // Convert to integer part only
    $integerPart = floor($number);
    $formatted = number_format($number, 2);
    $withoutCommas = str_replace(',', '', $formatted);
    $dollars = floor($number);
    $cents = round(($number - $dollars) * 100);

    return ucwords($this->convertNumberToWords($dollars)) . ' ' . $currencyText . ($cents > 0 ? ' and ' . $cents . ' ' . $centsText : '');
  }

  private function convertNumberToWords($number)
  {
    if ($number < 21) {
      $words = [
        'Zero',
        'One',
        'Two',
        'Three',
        'Four',
        'Five',
        'Six',
        'Seven',
        'Eight',
        'Nine',
        'Ten',
        'Eleven',
        'Twelve',
        'Thirteen',
        'Fourteen',
        'Fifteen',
        'Sixteen',
        'Seventeen',
        'Eighteen',
        'Nineteen',
        'Twenty'
      ];
      return $words[$number] ?? '';
    }

    // For numbers above 20, return simple representation
    return "{$number}";
  }

  public function viewCompanyDetails($id)
  {
    $company = Company::findOrFail($id);
    if (view()->exists('Manager.companies.show')) {
      return view('Manager.companies.show', compact('company'));
    }
    return redirect()->route('income.index', ['company' => $id]);
  }

  public function viewTransactions(Request $request)
  {
    $companyId = $request->get('company');
    if (view()->exists('Manager.transactions.index')) {
      return view('Manager.transactions.index', compact('companyId'));
    }
    return redirect()->route('manager.reports', ['company' => $companyId]);
  }
}
