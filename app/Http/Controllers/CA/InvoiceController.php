<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Company;
use ZipArchive;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::all();
        
        $companyId = $request->get('company_id');
        $type = $request->get('type');
        $status = $request->get('status');
        $search = $request->get('search');

        $query = Invoice::with(['company', 'attachments'])->orderBy('created_at', 'desc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($type) {
            // 'Proforma' -> 'proforma', 'Tax Invoice' -> 'invoice'
            if ($type === 'Proforma') {
                $query->where('type', 'proforma');
            } elseif ($type === 'Tax Invoice') {
                $query->where('type', 'invoice');
            }
        }

        if ($status) {
            $query->where('status', strtolower($status));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_details', 'like', "%{$search}%")
                  ->orWhere('total_amount', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('CA.invoices', compact('invoices', 'companies'));
    }
    
    public function downloadAttachments(Request $request)
    {
        $companyId = $request->get('company_id');
        $type = $request->get('type');
        $status = $request->get('status');

        $query = Invoice::with('attachments');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($type) {
            if ($type === 'Proforma') {
                $query->where('type', 'proforma');
            } elseif ($type === 'Tax Invoice') {
                $query->where('type', 'invoice');
            }
        }

        if ($status) {
            $query->where('status', strtolower($status));
        }

        $filesToZip = [];
        
        foreach ($query->get() as $invoice) {
            foreach ($invoice->attachments as $attachment) {
                $filesToZip[] = public_path($attachment->file_path ?? $attachment->path);
            }
        }

        $validFiles = array_filter($filesToZip, function($path) {
            return file_exists($path);
        });
        
        if (empty($validFiles)) {
            return back()->with('error', 'No attachments found for the selected invoices.');
        }
        
        $zipFileName = 'invoices_' . date('Ymd_His') . '.zip';
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
