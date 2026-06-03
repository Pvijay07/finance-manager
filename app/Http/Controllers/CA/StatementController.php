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

class StatementController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::all();
        
        $companyId = $request->get('company_id');
        $include = $request->get('include', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $records = collect([]);

        // Determine what to fetch based on "include" filter
        $fetchExpenses = in_array($include, ['all', 'expenses', 'payables']);
        $fetchIncome = in_array($include, ['all', 'income']);
        $fetchLoans = in_array($include, ['all', 'loans']);
        $fetchTaxes = in_array($include, ['all']); // Taxes might only be in 'all'

        // Expenses / Payables
        if ($fetchExpenses) {
            $query = Expense::with(['company', 'categoryRelation', 'attachments']);
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            
            if ($include === 'payables') {
                $query->whereIn('payment_status', ['pending', 'partially_paid']);
            }

            foreach ($query->get() as $expense) {
                $records->push((object)[
                    'id' => $expense->id,
                    'model' => 'Expense',
                    'date' => $expense->created_at,
                    'type_label' => $include === 'payables' ? 'Payable' : 'Expense',
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

        // Income
        if ($fetchIncome) {
            $query = Income::with(['company', 'category']);
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);

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
                    'attachments' => collect([]),
                    'company_name' => $income->company->name ?? 'N/A'
                ]);
            }
        }

        // Taxes
        if ($fetchTaxes) {
            $query = Tax::with('taxable.company');
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            
            foreach ($query->get() as $tax) {
                $company_name = 'N/A';
                if ($tax->taxable && isset($tax->taxable->company_id)) {
                    if ($companyId && $tax->taxable->company_id != $companyId) continue;
                    if (isset($tax->taxable->company)) {
                        $company_name = $tax->taxable->company->name;
                    }
                } elseif ($companyId) {
                    continue;
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

        // Advances (Loans)
        if ($fetchLoans) {
            $query = Advance::with(['company', 'attachments']);
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            
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

        return view('CA.statements', compact('paginated', 'companies'));
    }

    public function downloadAttachments(Request $request)
    {
        $companyId = $request->get('company_id');
        $include = $request->get('include', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $fetchExpenses = in_array($include, ['all', 'expenses', 'payables']);
        $fetchLoans = in_array($include, ['all', 'loans']);

        $filesToZip = [];

        if ($fetchExpenses) {
            $query = Expense::with('attachments');
            if ($companyId) $query->where('company_id', $companyId);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
            
            if ($include === 'payables') {
                $query->whereIn('payment_status', ['pending', 'partially_paid']);
            }

            foreach ($query->get() as $expense) {
                foreach ($expense->attachments as $attachment) {
                    $filesToZip[] = public_path($attachment->file_path ?? $attachment->path);
                }
            }
        }

        if ($fetchLoans) {
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
            return file_exists($path) && is_file($path);
        });

        if (empty($validFiles)) {
            return back()->with('error', 'No attachments found for the selected statement.');
        }

        $zipFileName = 'statement_attachments_' . date('Ymd_His') . '.zip';
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
