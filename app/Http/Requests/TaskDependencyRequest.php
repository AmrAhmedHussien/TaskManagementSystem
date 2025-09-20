<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskDependencyRequest extends FormRequest
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
            'dependency_id' => 'required|exists:tasks,id|different:task_id'
        ];
    }

    public function messages(): array
    {
        return [
            'dependency_id.required' => 'Please select a task to depend on',
            'dependency_id.exists' => 'Selected task does not exist',
            'dependency_id.different' => 'A task cannot depend on itself'
        ];
    }
}
