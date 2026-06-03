<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'details',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedDetailsAttribute()
    {
        if (empty($this->details)) return 'N/A';

        $details = $this->details;

        if (str_contains(strtolower($this->action), 'updated')) {
            $old = $details['old'] ?? [];
            $new = $details['new'] ?? [];
            $changes = [];

            foreach ($new as $key => $value) {
                if (in_array($key, ['updated_at', 'created_at', 'password'])) continue;

                $oldValue = $old[$key] ?? null;
                if ($oldValue != $value) {
                    $changes[] = "<strong>" . ucwords(str_replace('_', ' ', $key)) . "</strong>: " . 
                                  ($oldValue ?: '<i>empty</i>') . " &rarr; " . ($value ?: '<i>empty</i>');
                }
            }
            return !empty($changes) ? implode('<br>', $changes) : 'No major fields changed';
        }

        // For Created/Deleted or other actions, show key-value pairs
        $output = [];
        foreach ($details as $key => $value) {
            if (in_array($key, ['updated_at', 'created_at', 'id', 'password'])) continue;
            if (is_array($value)) continue; // Skip nested arrays for simple view
            
            $output[] = "<strong>" . ucwords(str_replace('_', ' ', $key)) . "</strong>: " . ($value ?: '<i>empty</i>');
        }

        return implode(', ', array_slice($output, 0, 5)) . (count($output) > 5 ? '...' : '');
    }
}