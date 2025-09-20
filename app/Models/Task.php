<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'assigned_to',
        'created_by',
        'due_date'
    ];

    protected $casts = [
        'due_date' => 'date'
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependencyTasks()
    {
        return $this->hasManyThrough(Task::class, TaskDependency::class, 'task_id', 'id', 'id', 'dependency_id');
    }

    public function canBeCompleted(): bool
    {
        $dependencies = $this->dependencies()->with('dependency')->get();
        
        foreach ($dependencies as $dependency) {
            if ($dependency->dependency->status !== 'completed') {
                return false;
            }
        }
        
        return true;
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_user'])) {
            $query->where('assigned_to', $filters['assigned_user']);
        }

        if (isset($filters['due_date_from'])) {
            $query->where('due_date', '>=', $filters['due_date_from']);
        }

        if (isset($filters['due_date_to'])) {
            $query->where('due_date', '<=', $filters['due_date_to']);
        }

        return $query;
    }
}
