<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Tax;
use App\Models\Advance;
use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;
use ZipArchive;
use Illuminate\Support\Facades\File;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::all();
        
        $companyId = $request->get('company_id');
        $type = $request->get('type');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $search = $request->get('search');

        $records = collect([]);

        // Fetch Expenses
        if (!$type || $type === 'Expense') {
            $query = Expense::with(['company', 'categoryRelation', 'attachments']);
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('expense_name', 'like', "%{$search}%")
                      ->orWhere('purpose_comment', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            }
            
            foreach ($query->get() as $expense) {
                $records->push((object)[
                    'id' => $expense->id,
                    'model' => 'Expense',
                    'date' => $expense->created_at,
                    'type_label' => 'Expense',
                    'category' => $expense->category_name ?? $expense->expense_name,
                    'description' => $expense->purpose_comment,
                    'amount' => $expense->planned_amount,
                    'reference' => 'EXP-'.str_pad($expense->id, 5, '0', STR_PAD_LEFT),
                    'comments' => $expense->purpose_comment,
                    'attachments' => $expense->attachments,
                    'company_name' => $expense->company->name ?? 'N/A'
                ]);
            }
        }

        // Fetch Incomes
        if (!$type || $type === 'Income') {
            $query = Income::with(['company', 'category']);
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            }
            
            foreach ($query->get() as $income) {
                $records->push((object)[
                    'id' => $income->id,
                    'model' => 'Income',
                    'date' => $income->created_at,
                    'type_label' => 'Income',
                    'category' => $income->category->name ?? 'N/A',
                    'description' => $income->description,
                    'amount' => $income->amount,
                    'reference' => 'INC-'.str_pad($income->id, 5, '0', STR_PAD_LEFT),
                    'comments' => $income->notes,
                    'attachments' => collect([]), // Income has no attachments
                    'company_name' => $income->company->name ?? 'N/A'
                ]);
            }
        }

        // Fetch Taxes
        if (!$type || $type === 'Tax') {
            $query = Tax::with('taxable.company');
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            if ($search) {
                $query->where('payment_notes', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
            }
            
            foreach ($query->get() as $tax) {
                $company_name = 'N/A';
                if ($tax->taxable && isset($tax->taxable->company_id)) {
                    if ($companyId && $tax->taxable->company_id != $companyId) {
                        continue;
                    }
                    if (isset($tax->taxable->company)) {
                        $company_name = $tax->taxable->company->name;
                    }
                } elseif ($companyId) {
                    continue; // Skip if filtered by company and tax has no company
                }

                $records->push((object)[
                    'id' => $tax->id,
                    'model' => 'Tax',
                    'date' => $tax->created_at,
                    'type_label' => 'Tax (' . strtoupper($tax->tax_type) . ')',
                    'category' => 'Tax',
                    'description' => $tax->payment_notes ?? 'Tax Payment',
                    'amount' => $tax->tax_amount,
                    'reference' => 'TAX-'.str_pad($tax->id, 5, '0', STR_PAD_LEFT),
                    'comments' => $tax->payment_notes,
                    'attachments' => collect([]),
                    'company_name' => $company_name
                ]);
            }
        }

        // Fetch Advances (Loans)
        if (!$type || $type === 'Loan') {
            $query = Advance::with(['company', 'attachments']);
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('purpose', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            }
            
            foreach ($query->get() as $advance) {
                $records->push((object)[
                    'id' => $advance->id,
                    'model' => 'Advance',
                    'date' => $advance->created_at,
                    'type_label' => 'Loan (' . ucfirst(str_replace('_', ' ', $advance->transaction_type)) . ')',
                    'category' => 'Advance',
                    'description' => $advance->purpose,
                    'amount' => $advance->amount,
                    'reference' => 'LOAN-'.str_pad($advance->id, 5, '0', STR_PAD_LEFT),
                    'comments' => $advance->comments,
                    'attachments' => $advance->attachments,
                    'company_name' => $advance->company->name ?? 'N/A'
                ]);
            }
        }

        // Sort by date descending
        $sorted = $records->sortByDesc('date')->values();

        // Manual Pagination
        $perPage = 50;
        $page = $request->get('page', 1);
        $paginated = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('CA.records', compact('paginated', 'companies'));
    }
    
    public function downloadAttachments(Request $request)
    {
        $type = $request->get('type');
        $companyId = $request->get('company_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        $filesToZip = [];
        
        if (!$type || $type === 'Expense') {
            $query = Expense::with('attachments');
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            
            foreach ($query->get() as $expense) {
                foreach ($expense->attachments as $attachment) {
                    $filesToZip[] = public_path($attachment->file_path);
                }
            }
        }
        
        if (!$type || $type === 'Loan') {
            $query = Advance::with('attachments');
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            
            foreach ($query->get() as $advance) {
                foreach ($advance->attachments as $attachment) {
                    $filesToZip[] = public_path($attachment->file_path);
                }
            }
        }
        
        $validFiles = array_filter($filesToZip, function($path) {
            return file_exists($path);
        });
        
        if (empty($validFiles)) {
            return back()->with('error', 'No attachments found for the selected filters.');
        }
        
        $zipFileName = 'attachments_' . date('Ymd_His') . '.zip';
        $zipPath = public_path('downloads/' . $zipFileName);
        
        if (!file_exists(public_path('downloads'))) {
            mkdir(public_path('downloads'), 0777, true);
        }
        
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($validFiles as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }
        
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
