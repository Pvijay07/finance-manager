<?php

// namespace App\Traits;

// use App\Models\ActivityLog;

// trait LogsActivity
// {
//   protected static function bootLogsActivity()
//   {
//     // Log when model is created
//     static::created(function ($model) {
//       ActivityLog::log('created', get_class($model), $model->id, $model->toArray());
//     });

//     // Log when model is updated
//     static::updated(function ($model) {
//       $changes = $model->getChanges();
//       ActivityLog::log('updated', get_class($model), $model->id, [
//         'changes' => $changes,
//         'original' => array_intersect_key($model->getOriginal(), $changes)
//       ]);
//     });

//     // Log when model is deleted
//     static::deleted(function ($model) {
//       ActivityLog::log('deleted', get_class($model), $model->id);
//     });
//   }
// }
