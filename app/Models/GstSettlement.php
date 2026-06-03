<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GstSettlement extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'tax_period',
        'amount',
        'payment_date',
        'payment_mode',
        'challan_number',
        'utr_number',
        'status',
        'purpose_comment',
        'created_by',
    ];

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
        return $this->morphMany(Attachment::class, 'attachable');
    }
}