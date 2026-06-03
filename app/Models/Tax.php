<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
class Tax extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'taxable_id',
        'taxable_type',
        'tax_type',
        'tax_percentage',
        'tax_amount',
        'amount_paid',
        'paid_date',
        'payment_status',
        'payment_notes',
        'payment_reference',
        'due_date',
        'direction',
        'tds_proof_path',
        'taxable_amount'
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_date' => 'date',
        'due_date' => 'date'
    ];

    // Polymorphic relationship
    public function taxable()
    {
        return $this->morphTo();
    }

    // Scope for different tax types
    public function scopeGst($query)
    {
        return $query->where('tax_type', 'gst');
    }

    public function scopeTds($query)
    {
        return $query->where('tax_type', 'tds');
    }

    public function scopeIncome($query)
    {
        return $query->where('direction', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('direction', 'expense');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function getAmountAttribute()
    {
        return $this->tax_amount;
    }
    
    public function getPercentageAttribute()
    {
        return $this->tax_percentage;
    }
}