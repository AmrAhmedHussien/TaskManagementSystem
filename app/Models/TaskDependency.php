<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    protected $fillable = [
        'task_id',
        'dependency_id'
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'dependency_id');
    }
}
