<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'document_type',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
