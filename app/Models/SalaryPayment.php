<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_sheet_id',
        'company_id',
        'payment_date',
        'amount',
        'payment_mode',
        'reference',
        'proof_path',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function sheet()
    {
        return $this->belongsTo(SalarySheet::class, 'salary_sheet_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
