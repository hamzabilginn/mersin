<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $planner;
    private User $worker;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Seed Users
        $this->planner = User::create([
            'name' => 'John Planner',
            'email' => 'planner@site.local',
            'password' => bcrypt('password'),
            'role' => 'planner',
        ]);

        $this->worker = User::create([
            'name' => 'Bob Worker',
            'email' => 'worker@site.local',
            'password' => bcrypt('password'),
            'role' => 'field_worker',
        ]);

        $this->manager = User::create([
            'name' => 'Alice Manager',
            'email' => 'manager@site.local',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);
    }

    /**
     * Test a Planner can create a new task successfully.
     */
    public function test_planner_can_create_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Montajı',
            'description' => 'Montaj detayları.',
            'worker_id' => $this->worker->id,
            'planner_id' => $this->planner->id,
            'due_date' => '2026-06-30',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('title', 'Test Montajı');
        $response->assertJsonPath('status', 'pending');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Montajı',
            'status' => 'pending',
            'worker_id' => $this->worker->id
        ]);
    }

    /**
     * Test the full standard workflow transition:
     * pending -> in_progress -> waiting_approval -> approved
     */
    public function test_standard_workflow_lifecycle(): void
    {
        // 1. Create a task
        $task = Task::create([
            'title' => 'Kritik Beton Dökümü',
            'description' => 'Açıklama.',
            'status' => 'pending',
            'planner_id' => $this->planner->id,
            'worker_id' => $this->worker->id,
            'manager_id' => $this->manager->id,
            'due_date' => '2026-06-30',
        ]);

        // 2. Worker transitions task to in_progress
        $response = $this->putJson("/api/tasks/{$task->id}/status", [
            'status' => 'in_progress',
            'user_id' => $this->worker->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals('in_progress', $task->refresh()->status);

        // 3. Worker transitions task to waiting_approval
        $response = $this->putJson("/api/tasks/{$task->id}/status", [
            'status' => 'waiting_approval',
            'user_id' => $this->worker->id,
            'comment' => 'Kalıplar hazır, beton döküldü.',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('waiting_approval', $task->refresh()->status);

        // 4. Manager transitions task to approved (closured)
        $response = $this->putJson("/api/tasks/{$task->id}/status", [
            'status' => 'approved',
            'user_id' => $this->manager->id,
            'comment' => 'İyi iş, onaylandı.',
        ]);
        $response->assertStatus(200);
        $this->assertEquals('approved', $task->refresh()->status);
    }

    /**
     * Test a worker cannot approve a task (should return 403 Forbidden).
     */
    public function test_worker_cannot_approve_task(): void
    {
        $task = Task::create([
            'title' => 'Saha Temizliği',
            'description' => 'Açıklama.',
            'status' => 'waiting_approval',
            'planner_id' => $this->planner->id,
            'worker_id' => $this->worker->id,
            'manager_id' => $this->manager->id,
            'due_date' => '2026-06-30',
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}/status", [
            'status' => 'approved',
            'user_id' => $this->worker->id,
        ]);

        $response->assertStatus(403);
        $this->assertEquals('waiting_approval', $task->refresh()->status);
    }

    /**
     * Test a worker cannot update a task assigned to someone else.
     */
    public function test_worker_cannot_update_others_tasks(): void
    {
        $otherWorker = User::create([
            'name' => 'Charlie Worker',
            'email' => 'charlie@site.local',
            'password' => bcrypt('password'),
            'role' => 'field_worker',
        ]);

        $task = Task::create([
            'title' => 'Dış Cephe Temizlik',
            'description' => 'Açıklama.',
            'status' => 'pending',
            'planner_id' => $this->planner->id,
            'worker_id' => $otherWorker->id,
            'manager_id' => $this->manager->id,
            'due_date' => '2026-06-30',
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}/status", [
            'status' => 'in_progress',
            'user_id' => $this->worker->id, // this worker is not assigned
        ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $task->refresh()->status);
    }
}
