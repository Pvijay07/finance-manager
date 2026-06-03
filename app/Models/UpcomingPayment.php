<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class UpcomingPayment extends Model
{
  use HasFactory, Auditable;

  protected $fillable = [
    'company_id',
    'type',
    'payment_number',
    'item_name',
    'party_name',
    'due_date',
    'category',
    'status',
    'amount',
    'category',
    'source',
    'description',
    'client_details',
    'original_upcoming_payment_id',
    'reminder_sent',
    'expense_id',
    'invoice_id',
    ''
  ];

  protected $casts = [
    'due_date'       => 'date',
    'amount'         => 'decimal:2',
  ];
  
  public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

}