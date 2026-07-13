<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'party_id',
        'loan_number',
        'direction',
        'loan_name',
        'loan_category',
        'principal_amount',
        'loan_date',
        'disbursement_date',
        'due_date',
        'loan_period',
        'currency',
        'interest_applicable',
        'interest_type',
        'interest_rate',
        'interest_frequency',
        'interest_start_date',
        'enable_emi',
        'emi_amount',
        'emi_count',
        'emi_start_date',
        'emi_due_day',
        'processing_fee',
        'documentation_fee',
        'legal_charges',
        'other_charges',
        'gst_applicable',
        'gst_percent',
        'gst_amount',
        'gst_type',
        'tds_applicable',
        'tds_section',
        'tds_percent',
        'tds_amount',
        'status',
        'collateral_applicable',
        'collateral_type',
        'guarantor_name',
        'guarantor_mobile',
        'guarantor_address',
        'guarantor_relation',
        'purpose',
        'comments',
        'created_by',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'disbursement_date' => 'date',
        'due_date' => 'date',
        'interest_start_date' => 'date',
        'emi_start_date' => 'date',
        'interest_applicable' => 'boolean',
        'enable_emi' => 'boolean',
        'gst_applicable' => 'boolean',
        'tds_applicable' => 'boolean',
        'collateral_applicable' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function transactions()
    {
        return $this->hasMany(LoanTransaction::class);
    }

    public function documents()
    {
        return $this->hasMany(LoanDocument::class);
    }

    public function amortizationSchedules()
    {
        return $this->hasMany(LoanAmortizationSchedule::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Dynamic Attributes for outstanding amounts
    public function getOutstandingPrincipalAttribute()
    {
        $totalPrincipalPaid = $this->transactions()
            ->where('type', 'payment')
            ->sum('principal_component');
            
        return max(0, $this->principal_amount - $totalPrincipalPaid);
    }

    public function getInterestDueAttribute()
    {
        $totalInterestAccrued = $this->transactions()
            ->where('type', 'interest_accrual')
            ->sum('total_amount');
            
        $totalInterestPaid = $this->transactions()
            ->where('type', 'payment')
            ->sum('interest_component');
            
        return max(0, $totalInterestAccrued - $totalInterestPaid);
    }

    public function getPenaltyDueAttribute()
    {
        $totalPenaltyAccrued = $this->transactions()
            ->where('type', 'penalty')
            ->sum('total_amount');
            
        $totalPenaltyPaid = $this->transactions()
            ->where('type', 'payment')
            ->sum('penalty_component');
            
        return max(0, $totalPenaltyAccrued - $totalPenaltyPaid);
    }

    public function getBalanceAmountAttribute()
    {
        return $this->outstanding_principal + $this->interest_due + $this->penalty_due;
    }

    public function getNextEmiAttribute()
    {
        if (!$this->enable_emi) return null;
        
        return $this->amortizationSchedules()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first();
    }
}
