<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvanceAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'advance_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'attachment_type'
    ];

    public function advance()
    {
        return $this->belongsTo(Advance::class);
    }
}