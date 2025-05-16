<?php

namespace App\Traits;

use App\OutgoingGoodLog;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;

trait LogsOutgoingGoodActivity
{
    /**
     * Log an activity for the outgoing good
     *
     * @param string $action
     * @param array $changes
     * @param string|null $notes
     * @return void
     */
    public function logActivity($action, $changes = [], $notes = null)
    {
        OutgoingGoodLog::create([
            'outgoing_good_number' => $this->number,
            'action' => $action,
            'changes' => $changes,
            'performed_by' => auth()->user()->_id ?? null,
            'performed_at' => new UTCDateTime(Carbon::now()->getPreciseTimestamp(3)),
            'notes' => $notes,
        ]);
    }

    /**
     * Boot the trait
     */
    protected static function bootLogsOutgoingGoodActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created', $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getDirty();
            if (!empty($changes)) {
                $model->logActivity('updated', $changes);
            }
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }
} 