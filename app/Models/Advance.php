<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Advance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_type',
        'direction',
        'party_id',
        'party_type',
        'reference_number',
        'amount',
        'recovered_amount',
        'outstanding_amount',
        'transaction_date',
        'expected_recovery_date',
        'status',
        'purpose',
        'comments',
        'created_by',
        'company_id',
        'linked_advance_id'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'expected_recovery_date' => 'date',
        'amount' => 'decimal:2',
        'recovered_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2'
    ];

    // Relationships
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->hasMany(AdvanceAttachment::class);
    }

    public function originalAdvance()
    {
        return $this->belongsTo(Advance::class, 'linked_advance_id');
    }

    public function recoveries()
    {
        return $this->hasMany(Advance::class, 'linked_advance_id');
    }

    // Scopes
    public function scopeIssued($query)
    {
        return $query->where('transaction_type', 'recoverable_advance')
                    ->where('direction', 'OUT');
    }

    public function scopeRecovered($query)
    {
        return $query->where('transaction_type', 'advance_recovery')
                    ->where('direction', 'IN');
    }

    public function scopeOutstanding($query)
    {
        return $query->where('status', 'outstanding')
                    ->orWhere('status', 'partially_recovered');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->where('status', 'outstanding')
                          ->whereDate('expected_recovery_date', '<', now());
                    });
    }
}


