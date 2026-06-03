<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tax;
use App\Models\Company;
use App\Models\Expense;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ExpenseTaxController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::all();

        $companyId = $request->get('company_id');
        $taxType = $request->get('tax_type', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $search = $request->get('search');

        // Fetch taxes attached to Expenses
        $query = Tax::with(['taxable.company', 'taxable.categoryRelation', 'taxable.attachments'])
            ->where('taxable_type', Expense::class)
            ->whereIn('tax_type', ['gst', 'tds'])
            ->orderBy('created_at', 'desc');

        if ($taxType !== 'all') {
            $query->where('tax_type', strtolower($taxType));
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                // We can search payment notes or related expense fields
                $q->where('payment_notes', 'like', "%{$search}%")
                  ->orWhereHasMorph('taxable', [Expense::class], function ($q) use ($search) {
                      $q->where('expense_name', 'like', "%{$search}%")
                        ->orWhere('purpose_comment', 'like', "%{$search}%");
                  });
            });
        }

        $taxes = $query->get();

        // Manual filtering by company ID because it's a polymorphic relation
        if ($companyId) {
            $taxes = $taxes->filter(function ($tax) use ($companyId) {
                return $tax->taxable && $tax->taxable->company_id == $companyId;
            });
        }

        // Calculate KPIs
        $gstInputTotal = $taxes->where('tax_type', 'gst')->sum('tax_amount');
        $tdsTotal = $taxes->where('tax_type', 'tds')->sum('tax_amount');

        // Count missing invoices (taxes where taxable expense has 0 attachments)
        $missingInvoices = $taxes->filter(function ($tax) {
            return $tax->taxable && $tax->taxable->attachments->count() === 0;
        })->count();

        // Paginate the collection manually
        $perPage = 20;
        $page = $request->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $taxes->forPage($page, $perPage),
            $taxes->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('CA.expense_taxes', compact(
            'companies',
            'paginated',
            'gstInputTotal',
            'tdsTotal',
            'missingInvoices'
        ));
    }

    public function downloadAttachments(Request $request)
    {
        $companyId = $request->get('company_id');
        $taxType = $request->get('tax_type', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = Tax::with(['taxable.attachments'])
            ->where('taxable_type', Expense::class)
            ->whereIn('tax_type', ['gst', 'tds']);

        if ($taxType !== 'all') {
            $query->where('tax_type', strtolower($taxType));
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $taxes = $query->get();

        if ($companyId) {
            $taxes = $taxes->filter(function ($tax) use ($companyId) {
                return $tax->taxable && $tax->taxable->company_id == $companyId;
            });
        }

        $filesToZip = [];
        
        foreach ($taxes as $tax) {
            if ($tax->taxable) {
                foreach ($tax->taxable->attachments as $attachment) {
                    $filesToZip[] = public_path($attachment->file_path ?? $attachment->path);
                }
            }
        }

        $validFiles = array_filter($filesToZip, function($path) {
            return file_exists($path);
        });
        
        if (empty($validFiles)) {
            return back()->with('error', 'No attachments found for the selected taxes.');
        }
        
        $zipFileName = 'expense_tax_attachments_' . date('Ymd_His') . '.zip';
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
