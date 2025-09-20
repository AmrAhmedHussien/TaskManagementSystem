<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_manager;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:tasks,id'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required',
            'title.max' => 'Task title cannot exceed 255 characters',
            'assigned_to.required' => 'Please assign the task to a user',
            'assigned_to.exists' => 'Selected user does not exist',
            'due_date.date' => 'Please provide a valid due date',
            'due_date.after_or_equal' => 'Due date must be today or in the future',
            'dependencies.array' => 'Dependencies must be an array',
            'dependencies.*.exists' => 'One or more dependency tasks do not exist'
        ];
    }
}
