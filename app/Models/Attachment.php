<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
  protected $fillable = [
    'attachable_id',
    'attachable_type',
    'description',
    'file_name',
    'file_path',
    'file_size',
    'uploaded_by'
  ];

  public function invoice()
  {
    return $this->belongsTo(Invoice::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'uploaded_by');
  }
}
