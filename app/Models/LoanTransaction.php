<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'type',
        'transaction_date',
        'total_amount',
        'principal_component',
        'interest_component',
        'penalty_component',
        'gst_component',
        'tds_component',
        'payment_mode',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
