<?php
session_start();
// Load notification helper
require_once __DIR__ . '/../utils/notifications.php';
$unreadCount = NotificationHelper::getCurrentUserUnreadCount();
$recentNotifications = NotificationHelper::getCurrentUserNotifications(5);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$projectRepo = new ProjectRepository($pdo);
$sprintRepo = new SprintRepository($pdo);
$taskRepo = new TaskRepository($pdo);
$userRepo = new UserRepository($pdo);

// Get statistics
$totalProjects = $projectRepo->countAll();
$totalUsers = $userRepo->countAll();
$totalSprints = $sprintRepo->countAll();
$totalTasks = $taskRepo->countAll();

// Get recent data
$recentProjects = $projectRepo->getRecent(5);
$recentSprints = $sprintRepo->getRecent(5);
$recentTasks = $taskRepo->getRecent(5);
$recentUsers = $userRepo->getRecent(5);

// Get active sprints
$activeSprints = $sprintRepo->getActiveSprints();

// Get task statistics
$todoTasks = $taskRepo->countByStatus('todo');
$inProgressTasks = $taskRepo->countByStatus('in_progress');
$doneTasks = $taskRepo->countByStatus('done');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">ScrumATHENA Admin</h1>
                <span class="bg-blue-800 px-3 py-1 rounded-full text-sm">Admin</span>
            </div>
            <div class="flex items-center space-x-4">
                <span>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
        <div class="container mx-auto mt-2">
            <div class="flex space-x-4">
                <a href="dashboard-admin.php" class="px-4 py-2 bg-blue-700 rounded">Dashboard</a>
                <a href="my-projects.php" class="px-4 py-2 hover:bg-blue-700 rounded">Projects</a>
                <a href="users.php" class="px-4 py-2 hover:bg-blue-700 rounded">Users</a>
                <a href="my-spints.php" class="px-4 py-2 hover:bg-blue-700 rounded">Sprints</a>
                <a href="my-tasks.php" class="px-4 py-2 hover:bg-blue-700 rounded">Tasks</a>
                <a href="reports.php" class="px-4 py-2 hover:bg-blue-700 rounded">Reports</a>
            </div>
        </div>
        <!-- Notification Bell -->
<div class="relative ml-4">
    <button id="notificationBell" class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <?php if ($unreadCount > 0): ?>
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </button>
    
    <!-- Notification Dropdown -->
    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50 border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Notifications</h3>
                <?php if ($unreadCount > 0): ?>
                    <button onclick="markAllNotificationsRead()" class="text-sm text-blue-600 hover:text-blue-800">
                        Mark all as read
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="max-h-96 overflow-y-auto">
            <?php if (empty($recentNotifications)): ?>
                <div class="p-4 text-center text-gray-500">
                    No notifications
                </div>
            <?php else: ?>
                <?php foreach ($recentNotifications as $notification): ?>
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 notification-item <?= $notification->isUnread() ? 'bg-blue-50' : '' ?>" 
                         data-id="<?= $notification->id ?>"
                         onclick="markNotificationAsRead(<?= $notification->id ?>, this)">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-3">
                                <span class="text-lg"><?= $notification->getIcon() ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($notification->title) ?>
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?= htmlspecialchars($notification->message) ?>
                                </p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-gray-500">
                                        <?= $notification->time_ago ?>
                                    </span>
                                    <?php if ($notification->isUnread()): ?>
                                        <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="p-3 border-t border-gray-200 text-center">
            <a href="notifications.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                View all notifications
            </a>
        </div>
    </div>
</div>

<script>
// Notification Bell functionality
document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');
    
    // Toggle dropdown
    if (bell) {
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (dropdown && bell && !dropdown.contains(e.target) && !bell.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
});

function markNotificationAsRead(notificationId, element) {
    fetch('ajax/mark-notification-read.php?id=' + notificationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.classList.remove('bg-blue-50');
                const dot = element.querySelector('.bg-blue-500');
                if (dot) dot.remove();
                
                // Update unread count
                updateUnreadCount();
            }
        });
}

function markAllNotificationsRead() {
    fetch('ajax/mark-all-notifications-read.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove unread styling from all notifications
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('bg-blue-50');
                    const dot = item.querySelector('.bg-blue-500');
                    if (dot) dot.remove();
                });
                
                // Update unread count
                updateUnreadCount();
                
                // Hide the "Mark all as read" button
                const markAllBtn = document.querySelector('button[onclick="markAllNotificationsRead()"]');
                if (markAllBtn) markAllBtn.remove();
            }
        });
}

function updateUnreadCount() {
    // This would need to fetch the new count from server
    // For now, just decrease the count by 1 for each read
    const badge = document.querySelector('#notificationBell .bg-red-500');
    if (badge) {
        let count = parseInt(badge.textContent) - 1;
        if (count <= 0) {
            badge.remove();
        } else {
            badge.textContent = count;
        }
    }
}
</script>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Total Projects</h3>
                        <p class="text-3xl font-bold"><?= $totalProjects ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 2.5l-2.5 2.5m-10-10l2.5-2.5"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Total Users</h3>
                        <p class="text-3xl font-bold"><?= $totalUsers ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Active Sprints</h3>
                        <p class="text-3xl font-bold"><?= count($activeSprints) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Total Tasks</h3>
                        <p class="text-3xl font-bold"><?= $totalTasks ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Task Status Chart -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Task Status Distribution</h2>
                <div class="h-64">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Recent Activities</h2>
                <div class="space-y-4">
                    <?php foreach ($recentTasks as $task): ?>
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <p class="font-medium">New task created</p>
                        <p class="text-gray-600 text-sm">"<?= htmlspecialchars($task->title) ?>" in <?= htmlspecialchars($task->sprint_name) ?></p>
                        <p class="text-gray-500 text-xs"><?= date('M d, Y H:i', strtotime($task->created_at)) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Data Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Projects -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Recent Projects</h2>
                    <a href="projects.php" class="text-blue-600 hover:underline">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2 text-left">Name</th>
                                <th class="p-2 text-left">Manager</th>
                                <th class="p-2 text-left">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentProjects as $project): ?>
                            <tr class="border-b">
                                <td class="p-2"><?= htmlspecialchars($project->title) ?></td>
                                <td class="p-2"><?= htmlspecialchars($project->manager_name ?? 'N/A') ?></td>
                                <td class="p-2"><?= date('M d, Y', strtotime($project->created_at)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Active Sprints -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Active Sprints</h2>
                    <a href="my-sprints.php" class="text-blue-600 hover:underline">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2 text-left">Sprint</th>
                                <th class="p-2 text-left">Project</th>
                                <th class="p-2 text-left">End Date</th>
                                <th class="p-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeSprints as $sprint): ?>
                            <tr class="border-b">
                                <td class="p-2"><?= htmlspecialchars($sprint->name) ?></td>
                                <td class="p-2"><?= htmlspecialchars($sprint->project_name) ?></td>
                                <td class="p-2"><?= $sprint->getFormattedEndDate() ?></td>
                                <td class="p-2">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Active</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Task Status Chart
        const taskCtx = document.getElementById('taskChart').getContext('2d');
        const taskChart = new Chart(taskCtx, {
            type: 'doughnut',
            data: {
                labels: ['To Do', 'In Progress', 'Done'],
                datasets: [{
                    data: [<?= $todoTasks ?>, <?= $inProgressTasks ?>, <?= $doneTasks ?>],
                    backgroundColor: [
                        '#F87171', 
                        '#FBBF24', 
                        '#10B981'  
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>