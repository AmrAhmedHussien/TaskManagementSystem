<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        
        if (!$user) return false;
        
        if ($user->is_manager) return true;
        
        $taskId = $this->route('task');
        $task = \App\Models\Task::find($taskId);
        
        return $task && $task->assigned_to === $user->id;
    }

    public function rules(): array
    {
        $user = auth()->user();
        
        if ($user->is_manager) {
            return [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'assigned_to' => 'sometimes|exists:users,id',
                'due_date' => 'sometimes|nullable|date|after_or_equal:today',
                'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
                'dependencies' => 'sometimes|array',
                'dependencies.*' => 'exists:tasks,id'
            ];
        }
        
        $rules = [
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Task title cannot exceed 255 characters',
            'assigned_to.exists' => 'Selected user does not exist',
            'due_date.date' => 'Please provide a valid due date',
            'due_date.after_or_equal' => 'Due date must be today or in the future',
            'status.required' => 'Task status is required',
            'status.in' => 'Invalid task status',
            'dependencies.array' => 'Dependencies must be an array',
            'dependencies.*.exists' => 'One or more dependency tasks do not exist'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->status === 'completed') {
                $taskId = $this->route('task');
                $task = \App\Models\Task::find($taskId);
                if ($task && !$task->canBeCompleted()) {
                    $validator->errors()->add('status', 'Cannot complete task: dependencies are not completed');
                }
            }
        });
    }
}
