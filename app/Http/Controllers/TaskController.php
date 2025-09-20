<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Requests\TaskDependencyRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Task::with(['assignee', 'creator', 'dependencies.dependency']);

        if (!$user->is_manager) {
            $query->where('assigned_to', $user->id);
        }

        $tasks = $query->filter($request->all())->get();

        return response()->json(TaskResource::collection($tasks));
    }

    public function store(TaskStoreRequest $request): JsonResponse
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
            'due_date' => $request->due_date
        ]);

        if ($request->has('dependencies')) {
            foreach ($request->dependencies as $dependencyId) {
                if ($dependencyId != $task->id) {
                    TaskDependency::create([
                        'task_id' => $task->id,
                        'dependency_id' => $dependencyId
                    ]);
                }
            }
        }

        return response()->json(new TaskResource($task->load(['assignee', 'creator', 'dependencies.dependency'])), 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $query = Task::with(['assignee', 'creator', 'dependencies.dependency']);

        if (!$user->is_manager) {
            $query->where('assigned_to', $user->id);
        }

        $task = $query->findOrFail($id);

        return response()->json(new TaskResource($task));
    }

    public function update(TaskUpdateRequest $request, string $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $user = Auth::user();

        if ($user->is_manager) {
            $task->update($request->only(['title', 'description', 'assigned_to', 'due_date', 'status']));
            
            if ($request->has('dependencies')) {
                $task->dependencies()->delete();
                
                foreach ($request->dependencies as $dependencyId) {
                    if ($dependencyId != $task->id) {
                        TaskDependency::create([
                            'task_id' => $task->id,
                            'dependency_id' => $dependencyId
                        ]);
                    }
                }
            }
        } else {
            $task->update($request->only(['status']));
        }

        return response()->json(new TaskResource($task->load(['assignee', 'creator', 'dependencies.dependency'])));
    }

    public function addDependency(TaskDependencyRequest $request, string $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $dependencyTask = Task::findOrFail($request->dependency_id);

        $dependency = TaskDependency::create([
            'task_id' => $id,
            'dependency_id' => $request->dependency_id
        ]);

        return response()->json($dependency->load(['dependency']), 201);
    }

    public function removeDependency(string $taskId, string $dependencyId): JsonResponse
    {
        if (!Auth::user()->is_manager) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $dependency = TaskDependency::where('task_id', $taskId)
            ->where('id', $dependencyId)
            ->firstOrFail();

        $dependency->delete();

        return response()->json(['message' => 'Dependency removed successfully']);
    }

    public function assignTask(Request $request, string $id): JsonResponse
    {
        if (!Auth::user()->is_manager) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = Task::findOrFail($id);
        $task->update(['assigned_to' => $request->assigned_to]);

        return response()->json(new TaskResource($task->load(['assignee', 'creator', 'dependencies.dependency'])));
    }

}
