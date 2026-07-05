<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Models\TaskLog;

class WriteTaskAuditLog
{
    /**
     * Handle the event.
     */
    public function handle(TaskStatusChanged $event): void
    {
        TaskLog::create([
            'task_id' => $event->task->id,
            'user_id' => $event->user->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'comment' => $event->comment,
        ]);
    }
}
