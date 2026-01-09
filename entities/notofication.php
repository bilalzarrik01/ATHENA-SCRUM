<?php

class Notification
{
    public int $id;
    public int $user_id;
    public string $title;
    public string $message;
    public string $created_at;
    public int $is_read;
    public string $time_ago;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->user_id = (int)$data['user_id'];
        $this->title = $data['title'] ?? 'Notification';
        $this->message = $data['message'];
        $this->created_at = $data['created_at'];
        $this->is_read = (int)$data['is_read'];
        $this->time_ago = $this->formatTimeAgo($data['created_at']);
    }

    public function isUnread(): bool
    {
        return $this->is_read == 0;
    }

    public function getIcon(): string
    {
        // Different icons based on title
        $title = strtolower($this->title);
        if (strpos($title, 'task') !== false) return '📝';
        if (strpos($title, 'sprint') !== false) return '⚡';
        if (strpos($title, 'project') !== false) return '📁';
        if (strpos($title, 'urgent') !== false) return '🚨';
        if (strpos($title, 'completed') !== false) return '✅';
        if (strpos($title, 'assigned') !== false) return '👤';
        return '🔔';
    }

    private function formatTimeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';

        return date('M d, Y', $time);
    }

    // Helper method to get CSS class for styling
    public function getCssClass(): string
    {
        return $this->isUnread() ? 'notification-unread' : 'notification-read';
    }
    
    // Get status text
    public function getStatusText(): string
    {
        return $this->isUnread() ? 'Unread' : 'Read';
    }
}