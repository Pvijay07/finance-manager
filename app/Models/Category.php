<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'name',
    // 'description',
    'main_type',
    'category_type',
    'is_active',
    'is_default'
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'is_default' => 'boolean',
  ];

  // Scopes for different types
  public function scopeExpense($query)
  {
    return $query->where('main_type', 'expense');
  }

  public function scopeIncome($query)
  {
    return $query->where('main_type', 'income');
  }

  public function scopeStandardFixed($query)
  {
    return $query->where('category_type', 'standard_fixed');
  }

  public function scopeStandardEditable($query)
  {
    return $query->where('category_type', 'standard_editable');
  }

  public function scopeNotStandard($query)
  {
    return $query->where('category_type', 'not_standard');
  }

  public function scopeIncomeType($query)
  {
    return $query->where('category_type', 'income');
  }

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  // Helper methods
  public function getFullTypeAttribute()
  {
    $types = [
      'standard_fixed' => 'Standard Fixed Expense',
      'standard_editable' => 'Standard Editable Expense',
      'not_standard' => 'Not Standard Expense',
      'income' => 'Income',
    ];

    return $types[$this->category_type] ?? ucfirst(str_replace('_', ' ', $this->category_type));
  }

  public function getBadgeClassAttribute()
  {
    $classes = [
      'standard_fixed' => 'bg-primary',
      'standard_editable' => 'bg-warning text-dark',
      'not_standard' => 'bg-success',
      'income' => 'bg-info',
    ];

    return $classes[$this->category_type] ?? 'bg-secondary';
  }

  public static function getValidCategoryTypes($mainType)
  {
    $validTypes = [
      'expense' => ['standard_fixed', 'standard_editable', 'not_standard'],
      'income' => ['income'],
    ];

    return $validTypes[$mainType] ?? [];
  }
  public function expenses()
  {
    return $this->hasMany(Expense::class);
  }
}
