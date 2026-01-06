<?php
class Task
{
    public ?int $id = null;
    public int $sprint_id;
    public string $title;
    public ?string $description = null;
    public string $status = 'todo';
    public string $priority = 'medium';
    public ?string $created_at = null;
    
    // Optional: Add extra properties for display
    public string $sprint_name = '';
    public int $project_id = 0;

    // Optional: Helper method to get status color
    public function getStatusColor(): string {
        return match($this->status) {
            'todo' => 'yellow',
            'in_progress' => 'blue',
            'done' => 'green',
            default => 'gray'
        };
    }

    // Optional: Helper method to get priority color
    public function getPriorityColor(): string {
        return match($this->priority) {
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray'
        };
    }
}