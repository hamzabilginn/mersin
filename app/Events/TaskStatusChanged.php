<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskStatusChanged
{
    use Dispatchable, SerializesModels;

    public Task $task;
    public User $user;
    public ?string $oldStatus;
    public string $newStatus;
    public ?string $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, User $user, ?string $oldStatus, string $newStatus, ?string $comment = null)
    {
        $this->task = $task;
        $this->user = $user;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->comment = $comment;
    }
}
