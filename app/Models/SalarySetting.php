<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'standard_days',
        'ot_rate',
        'lop_rule',
        'notes'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
