<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'emp_id',
        'status',
        'full_name',
        'email',
        'department',
        'role',
        'salary_type',
        'monthly_ctc',
        'bank_account',
        'pan',
        'uan',
        'esic',
        'notes'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function salarySheetItems()
    {
        return $this->hasMany(SalarySheetItem::class);
    }
}
