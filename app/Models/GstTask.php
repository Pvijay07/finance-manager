<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GstTask extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'tax_period',
        'return_type',
        'due_date',
        'reminder_date',
        'assigned_to',
        'notes',
        'status',
        'completed_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_date' => 'date',
        'completed_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope for upcoming tasks
    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>=', now())
                     ->where('status', '!=', 'completed');
    }

    // Scope for overdue tasks
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->where('status', '!=', 'completed');
    }

    // Get status badge class
    public function getStatusBadgeAttribute()
    {
        $classes = [
            'pending' => 'bg-warning text-dark',
            'in_progress' => 'bg-info text-white',
            'completed' => 'bg-success text-white',
            'overdue' => 'bg-danger text-white',
        ];
        
        return $classes[$this->status] ?? 'bg-secondary text-white';
    }
}