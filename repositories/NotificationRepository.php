<?php
// Check if Notification.php exists in different locations
$notificationPaths = [
    __DIR__ . '/../entities/Notification.php',
    __DIR__ . '/Notification.php', 
    __DIR__ . '/../Notification.php',
    'C:/xampp/htdocs/athena/entities/Notification.php'
];

foreach ($notificationPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

// If still not found, create a simple Notification class inline
if (!class_exists('Notification')) {
    class Notification {
        public function __construct($data) {}
        public function isUnread() { return false; }
        public function getIcon() { return '🔔'; }
    }
}

// Rest of your NotificationRepository code...
?>
<?php
// repositories/NotificationRepository.php - SIMPLE VERSION

class NotificationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Get unread count - SIMPLEST
    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}