<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryAssignment extends Model
{
  protected $table = 'category_assignments';

  protected $fillable = [
    'category_id',
    'main_type',
    'category_type',
    'sub_type'
  ];

  public function category()
  {
    return $this->belongsTo(Category::class);
  }
}
