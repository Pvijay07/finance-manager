<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Expense extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'purpose_comment',
        'planned_amount',
        'actual_amount',
        'due_date',
        'paid_date',
        'status',
        'party_name',
        'category_id',
        'source',
        'created_by',
        'expense_name',
        'notes',
        'settle_notes',
        'frequency',
        'purpose_comment',
        'default_amount',
        'due_day',
        'reminder_days',
        'amount_mode',
        'tax_percentage',
        'tax_amount',
        'apply_tax',
        'mobile_number',
        'is_recurring',
        'is_split',
        'parent_id',
        'partial_paid',
        'tax_type',
        'is_active',
        'payment_mode',
        'bank_name',
        'upi_type',
        'upi_number',
        'original_amount'
    ];

    protected $casts = [
        'due_date'       => 'date',
        'paid_date'      => 'date',
        'planned_amount' => 'decimal:2',
        'actual_amount'  => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'original_amount' => 'decimal:2'
    ];

    // Relationships

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function upcomingPayment()
    {
        return $this->hasOne(UpcomingPayment::class);
    }

    // Scopes
    public function scopeStandard($query)
    {
        return $query->where('type', 'standard');
    }

    public function scopeNonStandard($query)
    {
        return $query->where('type', 'non_standard');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    // Methods
    public function markAsPaid($actualAmount = null, $paidDate = null)
    {
        $this->update([
            'status'         => 'paid',
            'actual_amount'  => $actualAmount ?? $this->planned_amount,
            'paid_date'      => $paidDate ?? now(),
            'balance_amount' => 0
        ]);

        // Update related upcoming payment
        if ($this->upcomingPayment) {
            $this->upcomingPayment->update(['status' => 'paid']);
        }

        event(new PaymentMarkedPaid($this));
    }

    public function isOverdue()
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }


    public function categoryRelation()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    // Also add this accessor for category_name
    public function getCategoryNameAttribute()
    {
        return $this->categoryRelation ? $this->categoryRelation->name : 'N/A';
    }
    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    // Tax relationship
    public function taxes()
    {
        return $this->morphMany(Tax::class, 'taxable');
    }

    public function tdsTax()
    {
        return $this->morphOne(Tax::class, 'taxable')->where('tax_type', 'tds');
    }

    // Helper method to get total tax
    public function getTotalTaxAttribute()
    {
        return $this->taxes->sum('tax_amount');
    }

    // Helper method to get total tax paid
    public function getTotalTaxPaidAttribute()
    {
        return $this->taxes->sum('amount_paid');
    }

    // Net income after tax
    public function getNetIncomeAttribute()
    {
        return $this->actual_amount - $this->total_tax;
    }

    public function parent()
    {
        return $this->belongsTo(Expense::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Expense::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->hasMany(Expense::class, 'parent_id')->with('allChildren');
    }
}
