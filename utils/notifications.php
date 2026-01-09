<?php
require_once __DIR__ . '/../config/db.php';

class NotificationHelper
{
    public static function getCurrentUserUnreadCount(): int
    {
        if (!isset($_SESSION['user'])) {
            return 0;
        }

        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM notifications 
             WHERE user_id = ? AND is_read = 0"
        );
        $stmt->execute([$_SESSION['user']['id']]);
        return (int) $stmt->fetchColumn();
    }

    public static function getCurrentUserNotifications(int $limit = 5): array
    {
        if (!isset($_SESSION['user'])) {
            return [];
        }

        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT *,
                created_at as time_ago
             FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $_SESSION['user']['id'], PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(function ($n) {
            return new Notification($n);
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
