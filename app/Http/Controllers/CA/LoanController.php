<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Advance;
use Illuminate\Support\Facades\File;
use ZipArchive;

class LoanController extends Controller
{
    public function issued(Request $request)
    {
        $companies = Company::all();

        $companyId = $request->get('company_id');
        $status = $request->get('status', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $search = $request->get('search');

        $query = Advance::issued()->with(['company', 'party', 'attachments'])->orderBy('transaction_date', 'desc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($status !== 'all') {
            if ($status === 'open') {
                $query->whereIn('status', ['outstanding', 'partially_recovered']);
            } elseif ($status === 'partially_recovered') {
                $query->where('status', 'partially_recovered');
            } elseif ($status === 'closed') {
                $query->where('status', 'recovered');
            }
        }

        if ($fromDate) {
            $query->whereDate('transaction_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('transaction_date', '<=', $toDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('party', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = 20;
        $paginated = $query->paginate($perPage)->appends($request->query());

        return view('CA.loans_issued', compact('companies', 'paginated'));
    }

    public function downloadIssuedAttachments(Request $request)
    {
        $companyId = $request->get('company_id');
        $status = $request->get('status', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = Advance::issued()->with(['attachments']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($status !== 'all') {
            if ($status === 'open') {
                $query->whereIn('status', ['outstanding', 'partially_recovered']);
            } elseif ($status === 'partially_recovered') {
                $query->where('status', 'partially_recovered');
            } elseif ($status === 'closed') {
                $query->where('status', 'recovered');
            }
        }

        if ($fromDate) {
            $query->whereDate('transaction_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('transaction_date', '<=', $toDate);
        }

        $loans = $query->get();
        $filesToZip = [];

        foreach ($loans as $loan) {
            foreach ($loan->attachments as $attachment) {
                $filesToZip[] = public_path($attachment->file_path);
            }
        }

        return $this->createZip($filesToZip, 'issued_loans_agreements');
    }

    public function recovery(Request $request)
    {
        $companies = Company::all();

        $companyId = $request->get('company_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $search = $request->get('search'); // loan reference
        $mode = $request->get('mode', 'all');

        $query = Advance::recovered()->with(['company', 'party', 'originalAdvance', 'attachments'])->orderBy('transaction_date', 'desc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($fromDate) {
            $query->whereDate('transaction_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('transaction_date', '<=', $toDate);
        }

        if ($search) {
            $query->whereHas('originalAdvance', function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%");
            });
        }

        // Mode is stored in comments or purpose currently in DB. Let's just do a basic like on comments if mode is specified, or omit if not standard
        if ($mode !== 'all') {
            $query->where('comments', 'like', "%{$mode}%");
        }

        $perPage = 20;
        $paginated = $query->paginate($perPage)->appends($request->query());

        return view('CA.loan_recovery', compact('companies', 'paginated'));
    }

    public function downloadRecoveryAttachments(Request $request)
    {
        $companyId = $request->get('company_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $search = $request->get('search'); // loan reference
        $mode = $request->get('mode', 'all');

        $query = Advance::recovered()->with(['attachments']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($fromDate) {
            $query->whereDate('transaction_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('transaction_date', '<=', $toDate);
        }

        if ($search) {
            $query->whereHas('originalAdvance', function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%");
            });
        }

        if ($mode !== 'all') {
            $query->where('comments', 'like', "%{$mode}%");
        }

        $recoveries = $query->get();
        $filesToZip = [];

        foreach ($recoveries as $recovery) {
            foreach ($recovery->attachments as $attachment) {
                $filesToZip[] = public_path($attachment->file_path);
            }
        }

        return $this->createZip($filesToZip, 'loan_recovery_proofs');
    }

    private function createZip($filesToZip, $prefix)
    {
        $validFiles = array_filter($filesToZip, function($path) {
            return file_exists($path) && is_file($path);
        });
        
        if (empty($validFiles)) {
            return back()->with('error', 'No attachments found for the selected records.');
        }
        
        $zipFileName = $prefix . '_' . date('Ymd_His') . '.zip';
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
