<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Party extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'email',
        'phone',
        'address',
        'pan_number',
        'gstin',
        'company_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function advances()
    {
        return $this->hasMany(Advance::class);
    }

    // Get total outstanding balance for this party
    public function getOutstandingBalanceAttribute()
    {
        return $this->advances()->outstanding()->sum('outstanding_amount');
    }
}