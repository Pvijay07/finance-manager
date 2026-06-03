<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait Auditable
{
    /**
     * Boot the Auditable trait to hook into model events.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logActivity('Created', $model);
        });

        static::updated(function ($model) {
            self::logActivity('Updated', $model);
        });

        static::deleted(function ($model) {
            self::logActivity('Deleted', $model);
        });
    }

    /**
     * Log the activity to the activity_logs table.
     */
    protected static function logActivity($action, $model)
    {
        $userId = auth()->id();
        
        // Use a default system ID or avoid logging entirely if triggered from the console
        if (!$userId && !app()->runningInConsole()) {
             $userId = 1; 
        }

        $details = [];
        if ($action === 'Updated') {
            // Track previous vs new attributes upon edits
            $details = [
                'old' => $model->getOriginal(),
                'new' => $model->getAttributes()
            ];
        } else {
            $details = $model->getAttributes();
        }

        ActivityLog::create([
            'user_id'    => $userId,
            'action'     => $action . ' ' . class_basename($model),
            'model_type' => get_class($model),
            'model_id'   => $model->id,
            'details'    => $details,
            'ip_address' => request() ? request()->ip() : null,
            'user_agent' => request() ? request()->userAgent() : null
        ]);
    }
}
