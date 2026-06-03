<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\SalarySheet;
use Illuminate\Support\Facades\File;
use ZipArchive;

class SalaryPackController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::all();

        $companyId = $request->get('company_id');
        $monthYear = $request->get('month_year'); // e.g. "2025-12"
        $status = $request->get('status', 'all');

        $query = SalarySheet::with(['company', 'items', 'payments'])->orderBy('month_year', 'desc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($monthYear) {
            $query->where('month_year', $monthYear);
        }

        if ($status !== 'all') {
            $query->where('status', strtolower($status));
        }

        $perPage = 20;
        $paginated = $query->paginate($perPage)->appends($request->query());

        // Extract unique months for the dropdown
        $availableMonths = SalarySheet::select('month_year')->distinct()->orderBy('month_year', 'desc')->pluck('month_year');

        return view('CA.salary_packs', compact('companies', 'paginated', 'availableMonths'));
    }

    public function downloadPack(Request $request, $id)
    {
        $sheet = SalarySheet::findOrFail($id);
        
        $filesToZip = [];
        // We assume attendance and proof files might be attached or stored in certain locations
        // Since I don't see a direct 'attachments' relation on SalarySheet, I'll assume they might be in public/salary_proofs or similar,
        // or there's a file path column on the sheet or payments.
        
        // Actually, let's just collect any proof from SalaryPayment
        foreach ($sheet->payments as $payment) {
            if (!empty($payment->proof_path) && file_exists(public_path($payment->proof_path))) {
                $filesToZip[] = public_path($payment->proof_path);
            }
        }

        // If there are generated PDF sheets, we could include them too
        $pdfPath = public_path("downloads/salary_sheet_{$sheet->id}.pdf");
        if (file_exists($pdfPath)) {
            $filesToZip[] = $pdfPath;
        }

        return $this->createZip($filesToZip, "salary_pack_{$sheet->month_year}");
    }

    public function downloadAllPacks(Request $request)
    {
        // Bulk download based on filters
        $companyId = $request->get('company_id');
        $monthYear = $request->get('month_year');
        $status = $request->get('status', 'all');

        $query = SalarySheet::with(['payments']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($monthYear) {
            $query->where('month_year', $monthYear);
        }

        if ($status !== 'all') {
            $query->where('status', strtolower($status));
        }

        $sheets = $query->get();
        $filesToZip = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet->payments as $payment) {
                if (!empty($payment->proof_path) && file_exists(public_path($payment->proof_path))) {
                    $filesToZip[] = public_path($payment->proof_path);
                }
            }
            $pdfPath = public_path("downloads/salary_sheet_{$sheet->id}.pdf");
            if (file_exists($pdfPath)) {
                $filesToZip[] = $pdfPath;
            }
        }

        return $this->createZip($filesToZip, "bulk_salary_packs");
    }

    private function createZip($filesToZip, $prefix)
    {
        $validFiles = array_filter($filesToZip, function($path) {
            return file_exists($path) && is_file($path);
        });
        
        if (empty($validFiles)) {
            return back()->with('error', 'No attachments found for the selected salary packs.');
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
