<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'assignee' => [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
                'email' => $this->assignee->email
            ],
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email
            ],
            'dependencies' => $this->whenLoaded('dependencies', function () {
                return $this->dependencies->map(function ($dependency) {
                    return [
                        'id' => $dependency->id,
                        'dependency_task' => [
                            'id' => $dependency->dependency->id,
                            'title' => $dependency->dependency->title,
                            'status' => $dependency->dependency->status
                        ]
                    ];
                });
            }),
            'can_be_completed' => $this->canBeCompleted()
        ];
    }
}
