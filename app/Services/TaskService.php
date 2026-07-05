<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Events\TaskStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function createTask(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            // SC and PM will be determined based on the crew region (simplified here)
            $sc = User::where('role', 'sc')->first();
            $pm = User::where('role', 'pm')->first();
            
            $task = Task::create([
                'zzz_code' => $data['zzz_code'],
                'tow' => $data['tow'],
                'stow' => $data['stow'],
                'sstow' => $data['sstow'],
                'planned_qty' => $data['planned_qty'],
                'planned_man_day' => $data['planned_man_day'],
                'status' => 'assigned',
                'tech_office_id' => $data['tech_office_id'],
                'hom_id' => $data['hom_id'],
                'sc_id' => $sc ? $sc->id : null,
                'pm_id' => $pm ? $pm->id : null,
                'due_date' => $data['due_date'],
            ]);

            $techOffice = User::findOrFail($data['tech_office_id']);
            event(new TaskStatusChanged($task, $techOffice, null, 'assigned', 'Plan oluşturuldu ve HoM\'a atandı.'));

            return $task;
        });
    }

    public function updateTaskStatus(Task $task, array $data, User $user): Task
    {
        $newStatus = $data['status'];
        $oldStatus = $task->status;

        // Fact Updates (for HoM)
        $factQty = $data['fact_qty'] ?? null;
        $factManDay = $data['fact_man_day'] ?? null;
        $overtime = $data['overtime'] ?? null;
        $comment = $data['comment'] ?? null;

        if ($oldStatus === $newStatus && empty($factQty) && empty($factManDay)) {
            throw ValidationException::withMessages([
                'status' => ['Görevin durumu zaten ' . $newStatus],
            ]);
        }

        // Role & Transition Validation
        if ($user->role === 'hom') {
            if ($task->hom_id != $user->id) {
                throw ValidationException::withMessages(['status' => ['Bu göreve atanmış HoM değilsiniz.']]);
            }
            if (!in_array($newStatus, ['in_progress', 'pending_sc'])) {
                throw ValidationException::withMessages(['status' => ["HoM görevi sadece 'in_progress' veya 'pending_sc' yapabilir."]]);
            }
        } elseif ($user->role === 'sc') {
            if ($task->sc_id != $user->id) {
                throw ValidationException::withMessages(['status' => ['Siz bu görevin SC\'si değilsiniz.']]);
            }
            if (!in_array($newStatus, ['pending_pm', 'rejected'])) {
                throw ValidationException::withMessages(['status' => ['SC sadece onaylayıp PM\'e gönderebilir veya reddedebilir.']]);
            }
            if ($oldStatus !== 'pending_sc') {
                throw ValidationException::withMessages(['status' => ['Sadece onay bekleyen (pending_sc) görevler SC tarafından işlenebilir.']]);
            }
        } elseif ($user->role === 'pm') {
            if (!in_array($newStatus, ['approved', 'rejected'])) {
                throw ValidationException::withMessages(['status' => ['PM sadece onaylayabilir (approved) veya reddedebilir.']]);
            }
            if ($oldStatus !== 'pending_pm') {
                throw ValidationException::withMessages(['status' => ['Sadece SC tarafından onaylanmış (pending_pm) görevler PM tarafından işlenebilir.']]);
            }
        }

        return DB::transaction(function () use ($task, $user, $oldStatus, $newStatus, $factQty, $factManDay, $overtime, $comment) {
            $task->status = $newStatus;
            
            if ($user->role === 'hom') {
                if ($factQty !== null) $task->fact_qty = $factQty;
                if ($factManDay !== null) $task->fact_man_day = $factManDay;
                if ($overtime !== null) $task->overtime = $overtime;
            }
            
            if ($comment !== null) {
                $task->comment = $comment;
            }

            $task->save();

            // Fire event
            event(new TaskStatusChanged($task, $user, $oldStatus, $newStatus, $comment));

            return $task;
        });
    }
}
