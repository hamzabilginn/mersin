<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Models\Notification;
use App\Models\User;

class SendTaskNotification
{
    /**
     * Handle the event.
     */
    public function handle(TaskStatusChanged $event): void
    {
        $task = $event->task;
        $user = $event->user;
        $newStatus = $event->newStatus;

        if ($newStatus === 'pending') {
            // Notify worker about new assignment
            Notification::create([
                'user_id' => $task->worker_id,
                'title' => 'Yeni Görev Atandı',
                'message' => "{$user->name} size \"{$task->title}\" görevini atadı.",
            ]);
        } elseif ($newStatus === 'waiting_approval') {
            // Notify managers
            $managers = User::where('role', 'manager')->get();
            foreach ($managers as $m) {
                Notification::create([
                    'user_id' => $m->id,
                    'title' => 'Onay Bekleyen Görev',
                    'message' => "{$user->name}, \"{$task->title}\" görevini onayınıza sundu.",
                ]);
            }
        } elseif ($newStatus === 'approved' || $newStatus === 'rejected') {
            // Notify worker of manager decision
            $actionWord = $newStatus === 'approved' ? 'onayladı' : 'reddedildi';
            Notification::create([
                'user_id' => $task->worker_id,
                'title' => $newStatus === 'approved' ? 'Görev Onaylandı' : 'Görev Reddedildi',
                'message' => "{$user->name}, \"{$task->title}\" görevinizi {$actionWord}.",
            ]);
        }
    }
}
