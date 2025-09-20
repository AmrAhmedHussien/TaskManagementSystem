<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('is_manager', true)->first();
        $users = User::where('is_manager', false)->get();

        $task1 = Task::create([
            'title' => 'Design Database Schema',
            'description' => 'Create the initial database schema for the project',
            'status' => 'completed',
            'assigned_to' => $users[0]->id,
            'created_by' => $manager->id,
            'due_date' => now()->addDays(5)
        ]);

        $task2 = Task::create([
            'title' => 'Implement User Authentication',
            'description' => 'Set up user authentication system with Sanctum',
            'status' => 'completed',
            'assigned_to' => $users[0]->id,
            'created_by' => $manager->id,
            'due_date' => now()->addDays(7)
        ]);

        $task3 = Task::create([
            'title' => 'Create Task Management API',
            'description' => 'Develop RESTful API for task management',
            'status' => 'in_progress',
            'assigned_to' => $users[1]->id,
            'created_by' => $manager->id,
            'due_date' => now()->addDays(10)
        ]);

        $task4 = Task::create([
            'title' => 'Implement Task Dependencies',
            'description' => 'Add functionality for task dependencies',
            'status' => 'pending',
            'assigned_to' => $users[1]->id,
            'created_by' => $manager->id,
            'due_date' => now()->addDays(12)
        ]);

        $task5 = Task::create([
            'title' => 'Write API Documentation',
            'description' => 'Create comprehensive API documentation',
            'status' => 'pending',
            'assigned_to' => $users[2]->id,
            'created_by' => $manager->id,
            'due_date' => now()->addDays(15)
        ]);

        $task6 = Task::create([
            'title' => 'Create Frontend Interface',
            'description' => 'Develop user interface for task management',
            'status' => 'pending',
            'assigned_to' => $users[2]->id,
            'created_by' => $manager->id,
            'due_date' => now()->addDays(20)
        ]);

        TaskDependency::create([
            'task_id' => $task3->id,
            'dependency_id' => $task1->id
        ]);

        TaskDependency::create([
            'task_id' => $task3->id,
            'dependency_id' => $task2->id
        ]);

        TaskDependency::create([
            'task_id' => $task4->id,
            'dependency_id' => $task3->id
        ]);

        TaskDependency::create([
            'task_id' => $task5->id,
            'dependency_id' => $task3->id
        ]);

        TaskDependency::create([
            'task_id' => $task6->id,
            'dependency_id' => $task4->id
        ]);

        TaskDependency::create([
            'task_id' => $task6->id,
            'dependency_id' => $task5->id
        ]);
    }
}
