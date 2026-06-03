<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Invoice extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'type',
        'invoice_number',
        'status',
        'client_details',
        'line_items',
        'subtotal',
        'tax_amount',
        'total_amount',
        'is_taxable',
        'issue_date',
        'due_date',
        'paid_date',
        'terms_conditions',
        'created_by',
        'frequency',
        'due_day',
        'reminder_days',
        'is_recurring',
        'is_settled',
        'writeoff_reason',
        'tax_percentage',
        'currency',
        'conversion_rate',
        'converted_amount',
        'received_amount',
        'tax_type',
        'original_currency_amount',
        'conversion_cost',
        'is_active',
        'receivable_amount',
        'balance_amount'
    ];

    protected $casts = [
        'client_details' => 'array',
        'line_items' => 'array',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_taxable' => 'boolean'
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function income()
    {
        return $this->hasOne(Income::class);
    }

    public function upcomingPayment()
    {
        return $this->hasOne(UpcomingPayment::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Scopes
    public function scopeProforma($query)
    {
        return $query->where('type', 'proforma');
    }

    public function scopeInvoice($query)
    {
        return $query->where('type', 'invoice');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'sent')
                    ->where('due_date', '<', now());
            });
    }

    // Methods
    public function generateInvoiceNumber()
    {
        $company = $this->company;
        $financialYear = $this->getFinancialYear();

        if ($this->type === 'proforma') {
            $prefix = "PF-{$financialYear}-PRO-";
            $lastNumber = self::where('invoice_number', 'like', $prefix . '%')
                ->max('invoice_number');
        } else {
            $prefix = "{$financialYear}-INV-";
            $lastNumber = self::where('invoice_number', 'like', $prefix . '%')
                ->max('invoice_number');
        }

        $sequence = $lastNumber ? intval(str_replace($prefix, '', $lastNumber)) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function markAsPaid($paidDate = null)
    {
        if ($this->type === 'proforma') {
            // Convert proforma to final invoice
            $this->update([
                'type' => 'invoice',
                'invoice_number' => $this->generateInvoiceNumber(),
                'is_taxable' => true
            ]);
        }

        $this->update([
            'status' => 'paid',
            'paid_date' => $paidDate ?? now()
        ]);

        // Update upcoming payment
        if ($this->upcomingPayment) {
            $this->upcomingPayment->update(['status' => 'paid']);
        }

        // Create income record
        Income::create([
            'company_id' => $this->company_id,
            'source' => 'invoice_payment',
            'income_type' => 'service',
            'description' => "Payment for invoice {$this->invoice_number}",
            'amount' => $this->total_amount,
            'invoice_id' => $this->id,
            'income_date' => now()
        ]);

        event(new InvoicePaid($this));
    }

    private function getFinancialYear()
    {
        $issueDate = $this->issue_date ?? now();
        $year = $issueDate->year;
        $nextYear = $issueDate->year + 1;

        return substr($year, -2) . '-' . substr($nextYear, -2);
    }
    // Relationship with taxes
    public function taxes()
    {
        return $this->morphMany(Tax::class, 'taxable');
    }

    public function gstTax()
    {
        return $this->morphOne(Tax::class, 'taxable')->where('tax_type', 'gst');
    }

    public function tdsTax()
    {
        return $this->morphOne(Tax::class, 'taxable')->where('tax_type', 'tds');
    }

    // Helper methods
    public function getTotalTaxAttribute()
    {
        return $this->taxes->sum('tax_amount');
    }

    public function getTotalTaxPaidAttribute()
    {
        return $this->taxes->sum('amount_paid');
    }

    public function getTaxTypeAttribute()
    {
        $taxTypes = $this->taxes->pluck('tax_type')->unique()->toArray();

        if (in_array('gst', $taxTypes) && in_array('tds', $taxTypes)) {
            return 'GST+TDS';
        } elseif (in_array('gst', $taxTypes)) {
            return 'GST';
        } elseif (in_array('tds', $taxTypes)) {
            return 'TDS';
        }

        return null;
    }

    // Calculate net amount
    public function getNetAmountAttribute()
    {
        return $this->subtotal + $this->total_tax;
    }
}
