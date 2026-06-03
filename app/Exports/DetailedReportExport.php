<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailedReportExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
  protected $data;

  public function __construct(array $data)
  {
    $this->data = $data;
  }

  public function array(): array
  {
    $rows = [];

    // Header
    $rows[] = ['Detailed Financial Report', '', '', '', '', ''];
    $rows[] = ['Period', $this->data['period_label'], '', '', '', ''];
    $rows[] = ['Date Range', $this->data['start_date'] . ' to ' . $this->data['end_date'], '', '', '', ''];
    $rows[] = []; // Empty row

    // Income Section
    $rows[] = ['INCOME RECORDS', '', '', '', '', ''];
    $rows[] = ['Date', 'Description', 'Company', 'Amount', 'Status', 'Payment Mode'];

    foreach ($this->data['incomes'] as $income) {
      $rows[] = [
        $income->created_at->format('Y-m-d'),
        $income->description ?? 'Income',
        $income->company->name ?? 'N/A',
        $income->amount ?? $income->planned_amount ?? 0,
        $income->status ?? 'N/A',
        $income->payment_mode ?? 'N/A'
      ];
    }

    $rows[] = []; // Empty row

    // Expense Section
    $rows[] = ['EXPENSE RECORDS', '', '', '', '', '', '', ''];
    $rows[] = ['Date', 'Expense Name', 'Company', 'Category', 'Planned Amount', 'Actual Amount', 'Status', 'Payment Mode'];

    foreach ($this->data['expenses'] as $expense) {
      $rows[] = [
        $expense->created_at->format('Y-m-d'),
        $expense->expense_name,
        $expense->company->name ?? 'N/A',
        $expense->categoryRelation->name ?? 'N/A',
        $expense->planned_amount ?? 0,
        $expense->actual_amount ?? 0,
        $expense->status ?? 'N/A',
        $expense->payment_mode ?? 'N/A'
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
      6 => ['font' => ['bold' => true]],
      12 => ['font' => ['bold' => true, 'size' => 14]],
      13 => ['font' => ['bold' => true]],
    ];
  }

  public function title(): string
  {
    return 'Detailed Report';
  }
}
