<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
class SystemSetting extends Model
{
    use SoftDeletes, Auditable;
    //
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];
}
