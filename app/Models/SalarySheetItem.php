<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySheetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_sheet_id',
        'salary_employee_id',
        'present_days',
        'lop_days',
        'ot_amount',
        'basic',
        'hra',
        'allowance',
        'incentive',
        'bonus',
        'pf',
        'esic',
        'tds',
        'advance_rec',
        'other_ded',
        'custom_earnings',
        'custom_deductions',
        'gross_pay',
        'deductions',
        'net_pay',
        'notes'
    ];

    protected $casts = [
        'custom_earnings' => 'array',
        'custom_deductions' => 'array',
    ];

    public function sheet()
    {
        return $this->belongsTo(SalarySheet::class, 'salary_sheet_id');
    }

    public function employee()
    {
        return $this->belongsTo(SalaryEmployee::class, 'salary_employee_id');
    }
}
