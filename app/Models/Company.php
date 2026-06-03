<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Company extends Model
{
  use HasFactory, Auditable;

  protected $fillable = [
    'code',
    'name',
    'manager_id',
    'financial_year_start',
    'default_currency',
    'invoice_prefix',
    'tax_percentage',
    'logo_path',
    'address',
    'contact_details',
    'status',
    'email',
    'website',
    'currency'
  ];
  protected $casts = [
    'financial_year_start' => 'date',
  ];

  public function manager()
  {
    return $this->belongsTo(User::class, 'manager_id');
  }

  public function expenseTypes()
  {
    return $this->hasMany(ExpenseType::class);
  }
  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }
  public function incomes()
  {
    return $this->hasMany(Income::class);
  }
  public function expenses()
  {
    return $this->hasMany(Expense::class);
  }
  public function expenseTypesCount()
  {
    return $this->expenses()
      ->selectRaw('expense_type_id, count(*) as count')
      ->groupBy('expense_type_id')
      ->get()
      ->count();
  }
}
