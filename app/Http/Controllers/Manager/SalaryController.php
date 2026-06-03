<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\SalaryComponent;
use App\Models\SalaryEmployee;
use App\Models\SalarySetting;
use App\Models\SalarySheet;
use App\Models\SalarySheetItem;
use App\Models\SalaryPayment;
use App\Models\UpcomingPayment;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SalaryController extends Controller
{
    private function getManagerCompanies()
    {
        return Company::where('manager_id', Auth::id())->where('status', 'active')->get();
    }

    public function dashboard(Request $request)
    {
        $companies = $this->getManagerCompanies();
        $companyIds = $companies->pluck('id')->toArray();
        
        $currentMonth = date('Y-m');
        $sheetsCreated = SalarySheet::whereIn('company_id', $companyIds)->count();
        $upcomingPayout = UpcomingPayment::whereIn('company_id', $companyIds)
                                          ->where('type', 'debit')
                                          ->where('category', 'Salaries')
                                          ->where('status', 'pending')
                                          ->sum('amount');
                                          
        $lastUpdated = SalarySheet::whereIn('company_id', $companyIds)->orderBy('updated_at', 'desc')->value('updated_at');

        return view('Manager.salary.dashboard', compact('companies', 'currentMonth', 'sheetsCreated', 'upcomingPayout', 'lastUpdated'));
    }

    public function employees(Request $request)
    {
        $companies = $this->getManagerCompanies();
        $companyId = $request->company_id;
        $status = $request->status ?? 'Active';
        
        $query = SalaryEmployee::whereIn('company_id', $companies->pluck('id'));
        if ($companyId) $query->where('company_id', $companyId);
        if ($status && $status !== 'All') $query->where('status', $status);
        
        $employees = $query->paginate(20);
        return view('Manager.salary.employees', compact('companies', 'employees', 'companyId', 'status'));
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'emp_id' => 'nullable|string',
            'status' => 'required|string',
            'full_name' => 'required|string',
            'email' => 'nullable|email',
            'department' => 'nullable|string',
            'role' => 'nullable|string',
            'salary_type' => 'required|string',
            'monthly_ctc' => 'required|numeric',
            'bank_account' => 'nullable|string',
            'pan' => 'nullable|string',
            'uan' => 'nullable|string',
            'esic' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        SalaryEmployee::create($data);
        return back()->with('success', 'Employee added successfully');
    }

    public function settings(Request $request)
    {
        $companies = $this->getManagerCompanies();
        $companyId = $request->company_id ?? $companies->first()->id ?? null;
        
        $components = collect();
        $settings = null;
        
        if ($companyId) {
            $components = SalaryComponent::where('company_id', $companyId)->get();
            $settings = SalarySetting::firstOrCreate(['company_id' => $companyId]);
        }
        
        return view('Manager.salary.settings', compact('companies', 'components', 'settings', 'companyId'));
    }

    public function storeComponent(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'type' => 'required|string',
            'name' => 'required|string',
            'default_value' => 'nullable|string',
            'is_editable' => 'required|boolean',
        ]);
        
        SalaryComponent::create($data);
        return back()->with('success', 'Component added');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'standard_days' => 'required|numeric',
            'ot_rate' => 'nullable|numeric',
            'lop_rule' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        SalarySetting::updateOrCreate(
            ['company_id' => $data['company_id']],
            $data
        );
        return back()->with('success', 'Settings updated');
    }

    public function sheets(Request $request)
    {
        $companies = $this->getManagerCompanies();
        $companyId = $request->company_id;
        
        $query = SalarySheet::whereIn('company_id', $companies->pluck('id'))->with('company');
        if ($companyId) $query->where('company_id', $companyId);
        
        $sheets = $query->orderBy('month_year', 'desc')->paginate(20);
        return view('Manager.salary.sheets', compact('companies', 'sheets', 'companyId'));
    }

    public function createSheet(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'month_year' => 'required|string',
        ]);
        
        $exists = SalarySheet::where('company_id', $request->company_id)->where('month_year', $request->month_year)->first();
        if ($exists) {
            return redirect()->route('manager.salary.editSheet', $exists->id);
        }
        
        $setting = SalarySetting::where('company_id', $request->company_id)->first();
        
        $sheet = SalarySheet::create([
            'company_id' => $request->company_id,
            'month_year' => $request->month_year,
            'standard_days' => $setting ? $setting->standard_days : 30,
            'status' => 'Draft',
        ]);
        
        $employees = SalaryEmployee::where('company_id', $request->company_id)->where('status', 'Active')->get();
        foreach ($employees as $emp) {
            SalarySheetItem::create([
                'salary_sheet_id' => $sheet->id,
                'salary_employee_id' => $emp->id,
                'present_days' => $sheet->standard_days,
                'basic' => $emp->monthly_ctc * 0.4, // rough estimate just for placeholder
                'hra' => $emp->monthly_ctc * 0.2,
                'allowance' => $emp->monthly_ctc * 0.4,
            ]);
        }
        
        return redirect()->route('manager.salary.editSheet', $sheet->id);
    }

    public function editSheet($id)
    {
        $sheet = SalarySheet::with(['company', 'items.employee'])->findOrFail($id);
        $components = SalaryComponent::where('company_id', $sheet->company_id)->get();
        return view('Manager.salary.sheet_edit', compact('sheet', 'components'));
    }
    
    public function saveSheet(Request $request, $id)
    {
        $sheet = SalarySheet::findOrFail($id);
        $items = $request->items;
        
        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        
        if($items) {
            foreach ($items as $itemId => $data) {
                $item = SalarySheetItem::findOrFail($itemId);
                
                $gross = floatval($data['basic'] ?? 0) + floatval($data['hra'] ?? 0) + floatval($data['allowance'] ?? 0) + floatval($data['incentive'] ?? 0) + floatval($data['bonus'] ?? 0) + floatval($data['ot_amount'] ?? 0);
                $deds = floatval($data['pf'] ?? 0) + floatval($data['esic'] ?? 0) + floatval($data['tds'] ?? 0) + floatval($data['advance_rec'] ?? 0) + floatval($data['other_ded'] ?? 0);
                $net = $gross - $deds;
                
                $data['gross_pay'] = $gross;
                $data['deductions'] = $deds;
                $data['net_pay'] = $net;
                
                $item->update($data);
                
                $totalGross += $gross;
                $totalDeductions += $deds;
                $totalNet += $net;
            }
        }
        
        $sheet->update([
            'total_gross' => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net_pay' => $totalNet,
            'standard_days' => $request->standard_days ?? $sheet->standard_days,
        ]);
        
        return back()->with('success', 'Sheet saved as draft');
    }

    public function lockSheet(Request $request, $id)
    {
        $sheet = SalarySheet::findOrFail($id);
        
        $sheet->update([
            'status' => 'Locked',
            'due_date' => $request->due_date,
            'payment_mode' => $request->payment_mode,
            'notes' => $request->notes,
        ]);
        
        // Create an upcoming payment
        UpcomingPayment::create([
            'company_id' => $sheet->company_id,
            'type' => 'debit',
            'category' => 'Salaries',
            'item_name' => "Salaries for " . $sheet->month_year,
            'due_date' => $request->due_date,
            'amount' => $sheet->total_net_pay,
            'status' => 'pending',
            'source' => 'salary_module',
            'description' => 'Salary locked from Salary Module',
        ]);
        
        return redirect()->route('manager.salary.payments')->with('success', 'Sheet locked and payment debit created');
    }

    public function payments(Request $request)
    {
        $companies = $this->getManagerCompanies();
        
        $query = SalarySheet::whereIn('company_id', $companies->pluck('id'))->whereIn('status', ['Locked', 'Paid']);
        if ($request->company_id) $query->where('company_id', $request->company_id);
        
        $payments = $query->orderBy('month_year', 'desc')->paginate(20);
        return view('Manager.salary.payments', compact('companies', 'payments'));
    }

    public function markPayment(Request $request)
    {
        $request->validate([
            'salary_sheet_id' => 'required|exists:salary_sheets,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);
        
        $sheet = SalarySheet::findOrFail($request->salary_sheet_id);
        
        SalaryPayment::create([
            'salary_sheet_id' => $sheet->id,
            'company_id' => $sheet->company_id,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'reference' => $request->reference,
            'notes' => $request->notes,
        ]);
        
        $totalPaid = $sheet->total_paid + $request->amount;
        $sheet->update([
            'total_paid' => $totalPaid,
            'status' => $totalPaid >= $sheet->total_net_pay ? 'Paid' : 'Locked'
        ]);
        
        return back()->with('success', 'Payment recorded');
    }

    public function reports(Request $request)
    {
        $companies = $this->getManagerCompanies();
        return view('Manager.salary.reports', compact('companies'));
    }
    
    public function generateReport(Request $request)
    {
        // Placeholder for actual report generation
        return back()->with('success', 'Report generated successfully (Placeholder)');
    }

    public function downloadPayslip(Request $request, $itemId)
    {
        $item = SalarySheetItem::with(['employee', 'sheet.company'])->findOrFail($itemId);
        
        // Ensure sheet is not draft
        if ($item->sheet->status === 'Draft') {
            return back()->with('error', 'Cannot generate payslip for a draft sheet.');
        }

        $pdf = Pdf::loadView('Manager.salary.payslip_pdf', compact('item'));
        $fileName = 'Payslip_' . $item->employee->full_name . '_' . $item->sheet->month_year . '.pdf';
        
        return $pdf->download($fileName);
    }

    public function sendPayslipEmail(Request $request, $itemId)
    {
        $item = SalarySheetItem::with(['employee', 'sheet.company'])->findOrFail($itemId);
        
        if ($item->sheet->status === 'Draft') {
            return back()->with('error', 'Cannot send payslip for a draft sheet.');
        }

        if (empty($item->employee->email)) {
            return back()->with('error', 'Employee does not have an email address set.');
        }

        $pdf = Pdf::loadView('Manager.salary.payslip_pdf', compact('item'));
        $pdfContent = $pdf->output();

        $subject = 'Your Payslip for ' . $item->sheet->month_year;
        $employee = $item->employee;

        try {
            Mail::send('emails.payslip_email', ['item' => $item, 'employee' => $employee], function ($message) use ($employee, $subject, $pdfContent) {
                $message->to($employee->email)
                        ->subject($subject)
                        ->attachData($pdfContent, 'Payslip.pdf', [
                            'mime' => 'application/pdf',
                        ]);
            });

            return back()->with('success', 'Payslip sent to ' . $employee->email . ' successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
