<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/NotificationRepository.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$notificationRepo = new NotificationRepository($pdo);

// Get notifications
$notifications = $notificationRepo->getByUserId($userId);
$unreadCount = $notificationRepo->getUnreadCount($userId);

// Handle GET actions (simple way)
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'mark_read' && isset($_GET['id'])) {
        $notificationRepo->markAsRead($_GET['id'], $userId);
        header("Location: notifications.php");
        exit;
    }
    if ($_GET['action'] === 'mark_all_read') {
        $notificationRepo->markAllAsRead($userId);
        header("Location: notifications.php");
        exit;
    }
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $notificationRepo->delete($_GET['id'], $userId);
        header("Location: notifications.php");
        exit;
    }
    if ($_GET['action'] === 'clear_read') {
        $notificationRepo->deleteAllRead($userId);
        header("Location: notifications.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Notifications</h1>
                        <?php if ($unreadCount > 0): ?>
                            <span class="text-sm text-blue-600"><?= $unreadCount ?> unread</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="dashboardmember.php" class="text-blue-600 hover:underline">← Dashboard</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-3xl mx-auto p-4">
            <!-- Actions -->
            <div class="bg-white p-4 rounded shadow mb-4">
                <div class="flex justify-between">
                    <h2 class="font-bold">My Notifications</h2>
                    <div class="space-x-2">
                        <?php if ($unreadCount > 0): ?>
                            <a href="?action=mark_all_read" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">Mark All Read</a>
                        <?php endif; ?>
                        <a href="?action=clear_read" 
                           onclick="return confirm('Clear all read notifications?')"
                           class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm">Clear Read</a>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="space-y-3">
                <?php if (empty($notifications)): ?>
                    <div class="bg-white p-8 text-center rounded shadow">
                        <p class="text-gray-500">No notifications</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="bg-white p-4 rounded shadow <?= $notification->isUnread() ? 'border-l-4 border-blue-500' : '' ?>">
                            <div class="flex justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <span class="mr-2"><?= $notification->getIcon() ?></span>
                                        <h3 class="font-bold <?= $notification->isUnread() ? 'text-blue-700' : 'text-gray-700' ?>">
                                            <?= htmlspecialchars($notification->title) ?>
                                        </h3>
                                        <?php if ($notification->isUnread()): ?>
                                            <span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-gray-600 mt-1"><?= htmlspecialchars($notification->message) ?></p>
                                    <div class="text-sm text-gray-500 mt-2">
                                        <?= $notification->time_ago ?>
                                    </div>
                                </div>
                                <div class="ml-4 flex flex-col space-y-2">
                                    <?php if ($notification->isUnread()): ?>
                                        <a href="?action=mark_read&id=<?= $notification->id ?>" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">Mark Read</a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?= $notification->id ?>" 
                                       onclick="return confirm('Delete this notification?')"
                                       class="text-red-600 hover:text-red-800 text-sm">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>