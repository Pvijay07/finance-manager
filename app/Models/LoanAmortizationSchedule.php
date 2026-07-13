<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanAmortizationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'installment_number',
        'due_date',
        'emi_amount',
        'principal_component',
        'interest_component',
        'remaining_balance',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
