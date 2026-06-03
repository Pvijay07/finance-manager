<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseGenerationLog extends Model
{
  use HasFactory;

  protected $fillable = [
    'run_date',
    'status',
    'total_generated',
    'details',
    'triggered_by',
    'trigger_type',
  ];
  protected $casts = [
    'run_date' => 'datetime',
    'details' => 'array',
    'created_at' => 'datetime',
    'updated_at' => 'datetime'
  ];
}
