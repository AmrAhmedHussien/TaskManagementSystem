<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskManagementApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $manager;
    protected $user1;
    protected $user2;
    protected $user3;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'is_manager' => true
        ]);

        $this->user1 = User::create([
            'name' => 'Regular User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'is_manager' => false
        ]);

        $this->user2 = User::create([
            'name' => 'Regular User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'is_manager' => false
        ]);

        $this->user3 = User::create([
            'name' => 'Regular User 3',
            'email' => 'user3@example.com',
            'password' => bcrypt('password'),
            'is_manager' => false
        ]);
    }

    protected function authenticateManager()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'manager@example.com',
            'password' => 'password'
        ]);

        return $response->json('token');
    }

    protected function authenticateUser()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'user1@example.com',
            'password' => 'password'
        ]);

        return $response->json('token');
    }

    public function test_manager_can_create_task()
    {
        $token = $this->authenticateManager();

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assigned_to' => $this->user1->id,
            'due_date' => '2025-12-31'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_date',
                    'assignee' => ['id', 'name', 'email'],
                    'creator' => ['id', 'name', 'email']
                ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);
    }

    public function test_user_cannot_create_task()
    {
        $token = $this->authenticateUser();

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assigned_to' => $this->user2->id,
            'due_date' => '2025-12-31'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/tasks', $taskData);

        $response->assertStatus(403)
                ->assertJsonFragment(['message' => 'This action is unauthorized.']);
    }

    public function test_manager_can_retrieve_all_tasks()
    {
        $token = $this->authenticateManager();

        Task::create([
            'title' => 'Task 1',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        Task::create([
            'title' => 'Task 2',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
                ->assertJsonCount(2)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'title',
                        'status',
                        'assignee' => ['id', 'name', 'email'],
                        'creator' => ['id', 'name', 'email']
                    ]
                ]);
    }

    public function test_user_can_only_retrieve_assigned_tasks()
    {
        $token = $this->authenticateUser();

        Task::create([
            'title' => 'Task for User 1',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        Task::create([
            'title' => 'Task for User 2',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment(['title' => 'Task for User 1'])
                ->assertJsonMissing(['title' => 'Task for User 2']);
    }

    public function test_task_filtering_by_status()
    {
        $token = $this->authenticateManager();

        Task::create([
            'title' => 'Pending Task',
            'status' => 'pending',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        Task::create([
            'title' => 'Completed Task',
            'status' => 'completed',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/tasks?status=pending');

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment(['title' => 'Pending Task'])
                ->assertJsonMissing(['title' => 'Completed Task']);
    }

    public function test_task_filtering_by_assigned_user()
    {
        $token = $this->authenticateManager();

        Task::create([
            'title' => 'Task for User 1',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        Task::create([
            'title' => 'Task for User 2',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/tasks?assigned_user=' . $this->user1->id);

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment(['title' => 'Task for User 1'])
                ->assertJsonMissing(['title' => 'Task for User 2']);
    }

    public function test_task_filtering_by_due_date_range()
    {
        $token = $this->authenticateManager();

        Task::create([
            'title' => 'Task Due Soon',
            'due_date' => '2025-01-15',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        Task::create([
            'title' => 'Task Due Later',
            'due_date' => '2025-12-31',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/tasks?due_date_from=2025-01-01&due_date_to=2025-06-30');

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment(['title' => 'Task Due Soon'])
                ->assertJsonMissing(['title' => 'Task Due Later']);
    }

    public function test_manager_can_add_task_dependency()
    {
        $token = $this->authenticateManager();

        $task = Task::create([
            'title' => 'Main Task',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        $dependency = Task::create([
            'title' => 'Dependency Task',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/tasks/' . $task->id . '/dependencies', [
            'dependency_id' => $dependency->id
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'dependency' => ['id', 'title', 'status']
                ]);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task->id,
            'dependency_id' => $dependency->id
        ]);
    }

    public function test_user_cannot_add_task_dependency()
    {
        $token = $this->authenticateUser();

        $task = Task::create([
            'title' => 'Main Task',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        $dependency = Task::create([
            'title' => 'Dependency Task',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/tasks/' . $task->id . '/dependencies', [
            'dependency_id' => $dependency->id
        ]);

        $response->assertStatus(403)
                ->assertJsonFragment(['message' => 'This action is unauthorized.']);
    }

    public function test_task_cannot_be_completed_with_incomplete_dependencies()
    {
        $token = $this->authenticateUser();

        $dependency = Task::create([
            'title' => 'Dependency Task',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id,
            'status' => 'pending'
        ]);

        $task = Task::create([
            'title' => 'Main Task',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_id' => $dependency->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->putJson('/api/tasks/' . $task->id, ['status' => 'completed']);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'pending'
        ]);
    }

    public function test_task_can_be_completed_when_dependencies_are_completed()
    {
        $token = $this->authenticateUser();

        $dependency = Task::create([
            'title' => 'Dependency Task',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id,
            'status' => 'completed'
        ]);

        $task = Task::create([
            'title' => 'Main Task',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_id' => $dependency->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->putJson('/api/tasks/' . $task->id, ['status' => 'completed']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed'
        ]);
    }

    public function test_manager_can_assign_task_to_user()
    {
        $token = $this->authenticateManager();

        $task = Task::create([
            'title' => 'Task to Assign',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->patchJson('/api/tasks/' . $task->id . '/assign', [
            'assigned_to' => $this->user2->id
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'assignee' => [
                        'id' => $this->user2->id,
                        'name' => $this->user2->name,
                        'email' => $this->user2->email
                    ]
                ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $this->user2->id
        ]);
    }

    public function test_user_cannot_assign_task()
    {
        $token = $this->authenticateUser();

        $task = Task::create([
            'title' => 'Task to Assign',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->patchJson('/api/tasks/' . $task->id . '/assign', [
            'assigned_to' => $this->user2->id
        ]);

        $response->assertStatus(403)
                ->assertJsonFragment(['message' => 'This action is unauthorized.']);
    }

    public function test_manager_can_update_task_with_dependencies()
    {
        $token = $this->authenticateManager();

        $task = Task::create([
            'title' => 'Main Task',
            'assigned_to' => $this->user1->id,
            'created_by' => $this->manager->id
        ]);

        $dependency1 = Task::create([
            'title' => 'Dependency 1',
            'assigned_to' => $this->user2->id,
            'created_by' => $this->manager->id
        ]);

        $dependency2 = Task::create([
            'title' => 'Dependency 2',
            'assigned_to' => $this->user3->id,
            'created_by' => $this->manager->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->putJson('/api/tasks/' . $task->id, [
            'dependencies' => [$dependency1->id, $dependency2->id]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task->id,
            'dependency_id' => $dependency1->id
        ]);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task->id,
            'dependency_id' => $dependency2->id
        ]);
    }
}
