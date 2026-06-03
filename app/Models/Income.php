<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Income extends Model
{
  use HasFactory, Auditable;

  protected $fillable = [
    'company_id',
    'source',
    'income_type',
    'description',
    'amount',
    'invoice_id',
    'import_method',
    'income_date',
    'status',
    'tax_type',
    'actual_amount',
    'party_name',
    'frequency',
    'due_day',
    'mail_status',
    'created_by',
    'notes',
    'settle_notes',
    'client_details',
    'line_items',
    'parent_id',
    'invoice_number',
    'planned_amount',
    'conversion_cost',
    'conversion_rate_percentage',
    'receivable_amount',
    'received_amount',
    'balance_amount',
    'schedule_amount',
    'is_split',
    'is_partial',
    'converted_amount',
    'currency',
    'month_year',
    'due_date',
    'original_amount'
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'income_date' => 'date',
    'due_date' => 'date',
    'original_amount' => 'decimal:2',
  ];
  protected $appends = ['client_name', 'display_frequency', 'display_due_day'];
  public function company()
  {
    return $this->belongsTo(Company::class);
  }

  public function invoice()
  {
    return $this->belongsTo(Invoice::class);
  }

  public function scopeThisMonth($query)
  {
    return $query->where('month_year', date('Y-m'));
  }

  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeOverdue($query)
  {
    return $query->where('status', 'overdue');
  }
  public function category()
  {
    return $this->belongsTo(Category::class, 'income_type', 'id');
  }

  public function parent()
  {
    return $this->belongsTo(Income::class, 'parent_id');
  }

  public function children()
  {
    return $this->hasMany(Income::class, 'parent_id');
  }
  // Tax relationship
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

  // Check if all taxes are paid
  public function getAreAllTaxesPaidAttribute()
  {
    return $this->taxes->where('payment_status', '!=', 'paid')->count() === 0;
  }

  // Total payable amount (expense + tax)
  public function getTotalPayableAttribute()
  {
    return $this->actual_amount + $this->total_tax;
  }

  public function getClientNameAttribute()
  {
    if ($this->invoice_id && $this->invoice) {
      $clientDetails = $this->invoice->client_details;

      if (is_string($clientDetails)) {
        $clientDetails = json_decode($clientDetails, true);
      }

      if (is_array($clientDetails)) {
        return $clientDetails['name'] ??
          $clientDetails['client_name'] ??
          $clientDetails['company_name'] ?? $this->party_name;
      }
    }

    return $this->party_name ?? '';
  }

  public function getDisplayFrequencyAttribute()
  {
    if (!empty($this->frequency)) {
      return $this->frequency;
    }

    if ($this->invoice_id && $this->invoice && !empty($this->invoice->frequency)) {
      return $this->invoice->frequency;
    }

    return '';
  }

  public function getDisplayDueDayAttribute()
  {
    if (!empty($this->due_day)) {
      return $this->due_day;
    }

    if ($this->invoice_id && $this->invoice && !empty($this->invoice->due_day)) {
      return $this->invoice->due_day;
    }

    return '';
  }
}
