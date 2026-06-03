<?php

namespace App\Models;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Receipt extends Model
{
  use HasFactory, Auditable;
  protected $fillable = [
    'expense_id',
    'file_name',
    'file_path',
    'file_type',
    'file_size'
  ];
  public function getFileUrlAttribute()
  {
    return asset($this->file_path);
  }
  public function expense()
  {
    return $this->belongsTo(Expense::class);
  }
}
