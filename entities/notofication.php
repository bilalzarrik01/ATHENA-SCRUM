<?php
class Notification
{
    public int $id;
    public int $user_id;
    public string $title;
    public string $message;
    public string $type;
    public bool $is_read;
    public string $created_at;
    public ?string $read_at;
    public ?string $time_ago;
    
    public function __construct(
        int $id,
        int $user_id,
        string $title,
        string $message,
        string $type = 'info',
        bool $is_read = false,
        string $created_at = '',
        ?string $read_at = null
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->is_read = $is_read;
        $this->created_at = $created_at;
        $this->read_at = $read_at;
        $this->time_ago = $this->calculateTimeAgo();
    }
    
    private function calculateTimeAgo(): string
    {
        $now = new DateTime();
        $created = new DateTime($this->created_at);
        $interval = $now->diff($created);
        
        if ($interval->y > 0) return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        if ($interval->m > 0) return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        if ($interval->d > 0) {
            if ($interval->d == 1) return 'Yesterday';
            return $interval->d . ' days ago';
        }
        if ($interval->h > 0) return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        if ($interval->i > 0) return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        return 'Just now';
    }
    
    public function getIcon(): string
    {
        return match($this->type) {
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            'task' => '📝',
            'project' => '📁',
            'sprint' => '⚡',
            default => 'ℹ️'
        };
    }
    
    public function getBadgeColor(): string
    {
        return match($this->type) {
            'success' => 'bg-green-100 text-green-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'error' => 'bg-red-100 text-red-800',
            'task' => 'bg-blue-100 text-blue-800',
            'project' => 'bg-purple-100 text-purple-800',
            'sprint' => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
    
    public function isUnread(): bool
    {
        return !$this->is_read;
    }
}