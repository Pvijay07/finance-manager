<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;
    protected $type;

    public function __construct(array $data, $type = 'summary')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function array(): array
    {
        if ($this->type === 'company_wise') {
            return $this->getCompanyWiseData();
        }
        
        return $this->getSummaryData();
    }

    private function getSummaryData()
    {
        $rows = [];
        
        // Header
        $rows[] = ['Financial Report', '', '', ''];
        $rows[] = ['Period', $this->data['period_label'], '', ''];
        $rows[] = ['Date Range', $this->data['start_date'] . ' to ' . $this->data['end_date'], '', ''];
        $rows[] = []; // Empty row
        
        // Summary
        $rows[] = ['SUMMARY', '', '', ''];
        $rows[] = ['Total Income', $this->data['total_income'], '', ''];
        $rows[] = ['Total Expense', $this->data['total_expense'], '', ''];
        $rows[] = ['Net Profit', $this->data['net_profit'], '', ''];
        $rows[] = ['Total Income Records', $this->data['income_count'], '', ''];
        $rows[] = ['Total Expense Records', $this->data['expense_count'], '', ''];
        $rows[] = []; // Empty row
        
        return $rows;
    }

    private function getCompanyWiseData()
    {
        $rows = [];
        
        // Header
        $rows[] = ['Company-wise Financial Report', '', '', '', ''];
        $rows[] = ['Period', $this->data['period_label'], '', '', ''];
        $rows[] = ['Date Range', $this->data['start_date'] . ' to ' . $this->data['end_date'], '', '', ''];
        $rows[] = []; // Empty row
        
        // Headers
        $rows[] = ['Company Name', 'Income', 'Expenses', 'Profit/Loss', 'Margin %'];
        
        // Data rows
        foreach ($this->data['company_data'] as $company) {
            $rows[] = [
                $company['company_name'],
                $company['income'],
                $company['expense'],
                $company['profit'],
                number_format($company['margin'], 2) . '%'
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }

    public function title(): string
    {
        return 'Financial Report';
    }
}