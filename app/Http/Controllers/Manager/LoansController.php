<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advance;
use App\Models\Party;
use App\Models\Company;
use App\Models\AdvanceAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LoansController extends Controller
{
  public function index(Request $request)
  {
    // Get active tab
    $activeTab = $request->get('tab', 'payable'); // payable or receivable

    // Get dashboard statistics
    $stats = $this->getAdvanceStats();

    // Get filters
    $filters = $request->only(['status', 'party_id', 'company_id', 'party_type', 'date_from', 'date_to']);

    // Query advances based on tab
    if ($activeTab === 'receivable') {
      // Loans to be recovered (money coming IN to company)
      $advances = Advance::with(['party', 'company', 'recoveries'])
        ->where('transaction_type', 'receivable_advance')
        ->where('direction', 'IN');
    } else {
      // Loans to be paid (money going OUT from company) - default
      $advances = Advance::with(['party', 'company', 'recoveries'])
        ->where('transaction_type', 'recoverable_advance')
        ->where('direction', 'OUT');
    }

    // Apply filters
    $advances = $advances->when(isset($filters['status']) && $filters['status'] !== 'all', function ($q) use ($filters) {
      $q->where('status', $filters['status']);
    })
      ->when(isset($filters['party_id']), function ($q) use ($filters) {
        $q->where('party_id', $filters['party_id']);
      })
      ->when(isset($filters['company_id']) && $filters['company_id'] !== 'all', function ($q) use ($filters) {
        $q->where('company_id', $filters['company_id']);
      })
      ->when(isset($filters['party_type']) && $filters['party_type'] !== 'all', function ($q) use ($filters) {
        $q->where('party_type', $filters['party_type']);
      })
      ->when(isset($filters['date_from']) && isset($filters['date_to']), function ($q) use ($filters) {
        $q->whereBetween('transaction_date', [$filters['date_from'], $filters['date_to']]);
      })
      ->orderBy('transaction_date', 'desc')
      ->paginate(20)
      ->appends(array_merge($filters, ['tab' => $activeTab]));

    // Get parties for filter dropdown
    $parties = Party::orderBy('name')->get();
    $companies = Company::orderBy('name')->get();

    return view('Manager.loans.index', compact('advances', 'stats', 'parties', 'companies', 'activeTab'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'advance_type' => 'required|in:payable,receivable',
      'party_id' => 'required|exists:parties,id',
      'party_type' => 'required|in:employee,vendor,partner,other',
      'reference_number' => 'nullable|string|max:100|unique:advances,reference_number',
      'amount' => 'required|numeric|min:0',
      'transaction_date' => 'required|date',
      'expected_date' => 'nullable|date|after_or_equal:transaction_date',
      'purpose' => 'required|string|max:500',
      'comments' => 'nullable|string|max:1000',
      'company_id' => 'required|exists:companies,id',
      'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
    ]);

    try {
      DB::beginTransaction();

      // Determine transaction type and direction based on advance_type
      if ($request->advance_type === 'receivable') {
        $transactionType = 'receivable_advance';
        $direction = 'IN'; // Money coming IN to company
      } else {
        $transactionType = 'recoverable_advance';
        $direction = 'OUT'; // Money going OUT from company
      }

      // Create advance record
      $advance = Advance::create([
        'transaction_type' => $transactionType,
        'direction' => $direction,
        'party_id' => $request->party_id,
        'party_type' => $request->party_type,
        'reference_number' => $request->reference_number,
        'amount' => $request->amount,
        'recovered_amount' => 0,
        'outstanding_amount' => $request->amount,
        'transaction_date' => $request->transaction_date,
        'expected_recovery_date' => $request->expected_date,
        'status' => 'outstanding',
        'purpose' => $request->purpose,
        'comments' => $request->comments,
        'created_by' => auth()->id(),
        'company_id' => $request->company_id,
      ]);

      // Handle attachments
      if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
          if ($file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = 'advance_' . time() . '_' . uniqid() . '.' . $extension;

            // Store in storage/app/public/advances
            $path = $file->storeAs('advances', $filename, 'public');

            AdvanceAttachment::create([
              'advance_id' => $advance->id,
              'file_name' => $originalName,
              'file_path' => $path,
              'file_type' => $extension,
              'file_size' => $this->formatBytes($file->getSize()),
              'attachment_type' => 'loan_agreement'
            ]);
          }
        }
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => $request->advance_type === 'receivable'
          ? 'Receivable advance recorded successfully.'
          : 'Advance issued successfully.',
        'data' => $advance->load('party', 'company')
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Advance creation error: ' . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error creating advance: ' . $e->getMessage()
      ], 500);
    }
  }

  public function show($id)
  {
    $advance = Advance::with(['party', 'company', 'attachments', 'recoveries'])->findOrFail($id);

    return response()->json([
      'success' => true,
      'data' => $advance
    ]);
  }

  public function update(Request $request, $id)
  {
    $advance = Advance::findOrFail($id);

    $request->validate([
      'reference_number' => 'nullable|string|max:100|unique:advances,reference_number,' . $id,
      'expected_recovery_date' => 'nullable|date|after_or_equal:transaction_date',
      'purpose' => 'required|string|max:500',
      'comments' => 'nullable|string|max:1000',
      'status' => 'required|in:outstanding,partially_recovered,recovered,overdue',
      'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
    ]);

    try {
      DB::beginTransaction();

      // Update advance
      $advance->update([
        'reference_number' => $request->reference_number,
        'expected_recovery_date' => $request->expected_recovery_date,
        'purpose' => $request->purpose,
        'comments' => $request->comments,
        'status' => $request->status,
      ]);

      // Handle new attachments
      if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
          if ($file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = 'advance_' . time() . '_' . uniqid() . '.' . $extension;

            $path = $file->storeAs('advances', $filename, 'public');

            AdvanceAttachment::create([
              'advance_id' => $advance->id,
              'file_name' => $originalName,
              'file_path' => $path,
              'file_type' => $extension,
              'file_size' => $this->formatBytes($file->getSize()),
              'attachment_type' => 'loan_agreement'
            ]);
          }
        }
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Advance updated successfully.',
        'data' => $advance->load('party', 'company')
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Advance update error: ' . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error updating advance: ' . $e->getMessage()
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $advance = Advance::findOrFail($id);

      // Check if there are recoveries/repayments
      if ($advance->recoveries()->count() > 0) {
        return response()->json([
          'success' => false,
          'message' => 'Cannot delete advance with recovery/repayment entries.'
        ], 400);
      }

      $advance->delete();

      return response()->json([
        'success' => true,
        'message' => 'Advance deleted successfully.'
      ]);
    } catch (\Exception $e) {
      \Log::error('Advance deletion error: ' . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error deleting advance: ' . $e->getMessage()
      ], 500);
    }
  }

  public function storeRecovery(Request $request, $id)
  {
    $advance = Advance::findOrFail($id);

    $request->validate([
      'recovery_amount' => 'required|numeric|min:0.01|max:' . $advance->outstanding_amount,
      'recovery_date' => 'required|date',
      'comments' => 'nullable|string|max:1000',
      'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ]);

    try {
      DB::beginTransaction();

      // Calculate new amounts
      $newRecoveredAmount = $advance->recovered_amount + $request->recovery_amount;
      $newOutstandingAmount = $advance->outstanding_amount - $request->recovery_amount;

      // Determine new status
      if ($newOutstandingAmount <= 0) {
        $newStatus = 'recovered';
      } elseif ($newRecoveredAmount > 0) {
        $newStatus = 'partially_recovered';
      } else {
        $newStatus = 'outstanding';
      }

      // Check if overdue
      if (
        $advance->expected_recovery_date &&
        $request->recovery_date > $advance->expected_recovery_date
      ) {
        $newStatus = 'overdue';
      }

      // Update original advance
      $advance->update([
        'recovered_amount' => $newRecoveredAmount,
        'outstanding_amount' => $newOutstandingAmount,
        'status' => $newStatus,
      ]);

      // Determine transaction type based on original advance
      $recoveryTransactionType = $advance->transaction_type === 'receivable_advance'
        ? 'receivable_recovery'
        : 'advance_recovery';

      $recoveryDirection = $advance->transaction_type === 'receivable_advance'
        ? 'OUT' // When we receive money, recovery entry is OUT (money coming in)
        : 'IN'; // When we pay money, recovery entry is IN (money coming back)

      // Create recovery entry
      $recovery = Advance::create([
        'transaction_type' => $recoveryTransactionType,
        'direction' => $recoveryDirection,
        'party_id' => $advance->party_id,
        'party_type' => $advance->party_type,
        'reference_number' => 'REC-' . ($advance->reference_number ?? $advance->id),
        'amount' => $request->recovery_amount,
        'recovered_amount' => $request->recovery_amount,
        'outstanding_amount' => 0,
        'transaction_date' => $request->recovery_date,
        'expected_recovery_date' => null,
        'status' => 'recovered',
        'purpose' => 'Recovery of advance #' . $advance->id,
        'comments' => $request->comments,
        'created_by' => auth()->id(),
        'company_id' => $advance->company_id,
        'linked_advance_id' => $advance->id,
      ]);

      // Handle recovery attachment
      if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
        $file = $request->file('attachment');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = 'recovery_' . time() . '_' . uniqid() . '.' . $extension;

        $path = $file->storeAs('advances/recovery', $filename, 'public');

        AdvanceAttachment::create([
          'advance_id' => $recovery->id,
          'file_name' => $originalName,
          'file_path' => $path,
          'file_type' => $extension,
          'file_size' => $this->formatBytes($file->getSize()),
          'attachment_type' => 'recovery_receipt'
        ]);
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Recovery recorded successfully.',
        'data' => $advance->load('party', 'company', 'recoveries')
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Recovery creation error: ' . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error recording recovery: ' . $e->getMessage()
      ], 500);
    }
  }

  public function getStats()
  {
    $stats = $this->getAdvanceStats();

    return response()->json([
      'success' => true,
      'data' => $stats
    ]);
  }

  private function getAdvanceStats()
  {
    // Loans to be Paid (Advances given by company)
    $totalIssued = Advance::where('transaction_type', 'recoverable_advance')
      ->where('direction', 'OUT')
      ->sum('amount');

    $totalRecoveredPaid = Advance::where('transaction_type', 'advance_recovery')
      ->where('direction', 'IN')
      ->sum('amount');

    $totalOutstandingPaid = Advance::where('transaction_type', 'recoverable_advance')
      ->where('direction', 'OUT')
      ->sum('outstanding_amount');

    // Loans to be Recovered (Advances received by company)
    $totalReceivable = Advance::where('transaction_type', 'receivable_advance')
      ->where('direction', 'IN')
      ->sum('amount');

    $totalRecoveredReceivable = Advance::where('transaction_type', 'receivable_recovery')
      ->where('direction', 'OUT')
      ->sum('amount');

    $totalOutstandingReceivable = Advance::where('transaction_type', 'receivable_advance')
      ->where('direction', 'IN')
      ->sum('outstanding_amount');

    // Combined overdue advances
    $overdueCount = Advance::whereIn('transaction_type', ['recoverable_advance', 'receivable_advance'])
      ->where(function ($query) {
        $query->where('status', 'overdue')
          ->orWhere(function ($q) {
            $q->whereIn('status', ['outstanding', 'partially_recovered'])
              ->whereDate('expected_recovery_date', '<', now());
          });
      })
      ->count();

    $overdueAmount = Advance::whereIn('transaction_type', ['recoverable_advance', 'receivable_advance'])
      ->where(function ($query) {
        $query->where('status', 'overdue')
          ->orWhere(function ($q) {
            $q->whereIn('status', ['outstanding', 'partially_recovered'])
              ->whereDate('expected_recovery_date', '<', now());
          });
      })
      ->sum('outstanding_amount');

    return [
      // Payable advances (money given by company)
      'total_payable_issued' => $totalIssued ?? 0,
      'total_payable_recovered' => $totalRecoveredPaid ?? 0,
      'total_payable_outstanding' => $totalOutstandingPaid ?? 0,

      // Receivable advances (money to be received by company)
      'total_receivable_issued' => $totalReceivable ?? 0,
      'total_receivable_recovered' => $totalRecoveredReceivable ?? 0,
      'total_receivable_outstanding' => $totalOutstandingReceivable ?? 0,

      // Combined overdue
      'overdue_count' => $overdueCount ?? 0,
      'overdue_amount' => $overdueAmount ?? 0,
    ];
  }

  public function getParties(Request $request)
  {
    $parties = Party::orderBy('name')->get();

    return response()->json([
      'success' => true,
      'data' => $parties
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
}
