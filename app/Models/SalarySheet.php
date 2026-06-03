<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'month_year',
        'standard_days',
        'status',
        'total_gross',
        'total_deductions',
        'total_net_pay',
        'total_paid',
        'due_date',
        'payment_mode',
        'notes'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(SalarySheetItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
