<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * List all tasks with optional filters (formatted as API Resources).
     */
    public function index(Request $request)
    {
        $query = Task::with(['techOffice', 'hom', 'sc', 'pm', 'logs.user']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('role') && $request->has('user_id')) {
            $role = $request->role;
            $userId = $request->user_id;

            if ($role === 'hom') {
                $query->where('hom_id', $userId);
            } elseif ($role === 'tech_office') {
                $query->where('tech_office_id', $userId);
            } elseif ($role === 'sc') {
                $query->where('sc_id', $userId);
            } elseif ($role === 'pm') {
                $query->where('pm_id', $userId);
            }
        }

        $tasks = $query->orderBy('due_date', 'asc')->get();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated());

        return (new TaskResource($task->load(['techOffice', 'hom', 'sc', 'pm'])))
            ->response()
            ->setStatusCode(201);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = User::findOrFail($request->user_id);

        try {
            $updatedTask = $this->taskService->updateTaskStatus(
                $task,
                $request->validated(),
                $user
            );

            return new TaskResource($updatedTask->load(['techOffice', 'hom', 'sc', 'pm', 'logs.user']));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => collect($e->errors())->first()[0]], 403);
        }
    }

    public function bottlenecks()
    {
        $today = Carbon::today()->format('Y-m-d');

        // Delayed/overdue tasks (status not approved, and due date has passed)
        $delayedTasks = Task::with(['hom', 'techOffice'])
            ->where('status', '!=', 'approved')
            ->where('due_date', '<', $today)
            ->get();

        // Tasks stuck in "pending_sc" or "pending_pm" for more than 24 hours
        $oneDayAgo = Carbon::now()->subDay();
        
        $stuckTasks = Task::with(['hom', 'sc', 'pm'])
            ->whereIn('status', ['pending_sc', 'pending_pm'])
            ->whereHas('logs', function ($query) use ($oneDayAgo) {
                $query->whereIn('new_status', ['pending_sc', 'pending_pm'])
                      ->where('created_at', '<', $oneDayAgo);
            })->get();

        // Status counts for chart
        $statusCounts = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $statuses = ['draft', 'assigned', 'in_progress', 'pending_sc', 'pending_pm', 'approved', 'rejected'];
        $chartData = [];
        foreach ($statuses as $status) {
            $chartData[$status] = $statusCounts->get($status, 0);
        }

        return response()->json([
            'delayed_tasks' => TaskResource::collection($delayedTasks),
            'stuck_tasks' => TaskResource::collection($stuckTasks),
            'chart_data' => $chartData,
            'summary' => [
                'total' => Task::count(),
                'draft' => Task::where('status', 'draft')->count(),
                'assigned' => Task::where('status', 'assigned')->count(),
                'in_progress' => Task::where('status', 'in_progress')->count(),
                'pending_sc' => Task::where('status', 'pending_sc')->count(),
                'pending_pm' => Task::where('status', 'pending_pm')->count(),
                'approved' => Task::where('status', 'approved')->count(),
                'rejected' => Task::where('status', 'rejected')->count(),
                'delayed_count' => Task::where('status', '!=', 'approved')->where('due_date', '<', $today)->count(),
            ]
        ]);
    }
}
