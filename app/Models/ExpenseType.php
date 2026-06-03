<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'category',
    'amount_type',
    'default_amount',
    'reminder_days',
    'applicable_companies',
    'status',
    'is_recurring'
  ];
  protected $casts = [
    'applicable_companies' => 'array',
  ];
}
