<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role']; // This is 'project_manager' or 'team_member'
$userName = $_SESSION['user']['name']; // From your dashboard, user has 'name'

$taskRepo = new TaskRepository($pdo);
$sprintRepo = new SprintRepository($pdo);

// Get tasks based on user role
if ($userRole === 'project_manager') {
    // Manager sees all tasks
    $allTasks = $taskRepo->getAll();
} else {
    // Team member sees only assigned tasks
    $allTasks = $taskRepo->getByUser($userId);
}

// Filter tasks by status
$status = $_GET['status'] ?? 'all';
if ($status !== 'all') {
    $myTasks = array_filter($allTasks, fn($t) => $t->status === $status);
} else {
    $myTasks = $allTasks;
}

// Group tasks by status for counting
$todoTasks = array_filter($allTasks, fn($t) => $t->status === 'todo');
$inProgressTasks = array_filter($allTasks, fn($t) => $t->status === 'in_progress');
$doneTasks = array_filter($allTasks, fn($t) => $t->status === 'done');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .active-tab {
            border-bottom: 3px solid;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Navigation - Matching your dashboard -->
    <nav class="bg-black text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div class="flex items-center gap-3">
                    <img src="image.png" alt="Logo" class="w-8 h-8 object-contain">
                    <span class="text-xl font-bold">ScrumATHENA</span>
                </div>
                <h1 class="text-xl font-bold ml-4">
                    <?php echo $userRole === 'project_manager' ? 'All Tasks' : 'My Tasks'; ?>
                </h1>
                <span class="bg-green-800 px-3 py-1 rounded-full text-sm">
                    <?php 
                    // Format role for display
                    echo $userRole === 'project_manager' ? 'Manager' : 'Team Member';
                    ?>
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-300">Welcome, <?= htmlspecialchars($userName) ?></span>
                <a href="<?php echo $userRole === 'project_manager' ? 'dashboardchef.php' : 'dashboardmember.php'; ?>" 
                   class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded text-white">
                    Dashboard
                </a>
                <form action="logout.php" method="POST" class="m-0">
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-white cursor-pointer">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">
                        <?php echo $userRole === 'project_manager' ? 'All System Tasks' : 'My Assigned Tasks'; ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($userRole === 'project_manager') {
                            echo "View and manage all tasks across all projects";
                        } else {
                            echo "View and manage tasks assigned to you";
                        }
                        ?>
                    </p>
                </div>
                <?php if ($userRole === 'project_manager'): ?>
                    <div class="flex gap-2">
                        <a href="addtask.php" 
                           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            + New Task
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="?status=todo" class="block">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-lg">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">To Do</h3>
                            <p class="text-3xl font-bold"><?= count($todoTasks) ?></p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="?status=in_progress" class="block">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">In Progress</h3>
                            <p class="text-3xl font-bold"><?= count($inProgressTasks) ?></p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="?status=done" class="block">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Completed</h3>
                            <p class="text-3xl font-bold"><?= count($doneTasks) ?></p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="border-b">
                <nav class="flex">
                    <a href="?status=all" 
                       class="px-6 py-3 <?= $status === 'all' ? 'active-tab border-green-500 text-green-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        All Tasks (<?= count($allTasks) ?>)
                    </a>
                    <a href="?status=todo" 
                       class="px-6 py-3 <?= $status === 'todo' ? 'active-tab border-red-500 text-red-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        To Do (<?= count($todoTasks) ?>)
                    </a>
                    <a href="?status=in_progress" 
                       class="px-6 py-3 <?= $status === 'in_progress' ? 'active-tab border-yellow-500 text-yellow-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        In Progress (<?= count($inProgressTasks) ?>)
                    </a>
                    <a href="?status=done" 
                       class="px-6 py-3 <?= $status === 'done' ? 'active-tab border-green-500 text-green-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        Completed (<?= count($doneTasks) ?>)
                    </a>
                </nav>
            </div>
        </div>

        <!-- Tasks Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-4 text-left">Task</th>
                            <th class="p-4 text-left">Sprint</th>
                            <th class="p-4 text-left">Priority</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Created</th>
                            <?php if ($userRole === 'project_manager'): ?>
                                <th class="p-4 text-left">Assigned To</th>
                            <?php endif; ?>
                            <th class="p-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myTasks)): ?>
                            <tr>
                                <td colspan="<?php echo $userRole === 'project_manager' ? '7' : '6'; ?>" 
                                    class="p-8 text-center text-gray-500">
                                    No tasks found.
                                    <?php if ($status !== 'all'): ?>
                                        <a href="?status=all" class="text-green-600 hover:underline ml-2">
                                            View all tasks
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($myTasks as $task): 
                                // Get sprint info for display
                                $sprint = $sprintRepo->getById($task->sprint_id);
                            ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="p-4">
                                        <div class="font-medium"><?= htmlspecialchars($task->title) ?></div>
                                        <div class="text-gray-500 text-sm mt-1">
                                            <?= substr($task->description ?? 'No description', 0, 80) ?>...
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <?= $sprint ? htmlspecialchars($sprint->name) : 'N/A' ?>
                                    </td>
                                    <td class="p-4">
                                        <?php 
                                        $priorityColors = [
                                            'high' => 'bg-red-100 text-red-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'low' => 'bg-green-100 text-green-800'
                                        ];
                                        $color = $priorityColors[$task->priority] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-sm <?= $color ?>">
                                            <?= ucfirst($task->priority) ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <?php 
                                        $statusColors = [
                                            'todo' => 'bg-yellow-100 text-yellow-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'done' => 'bg-green-100 text-green-800'
                                        ];
                                        $color = $statusColors[$task->status] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-sm <?= $color ?>">
                                            <?= ucfirst(str_replace('_', ' ', $task->status)) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($task->created_at)) ?>
                                    </td>
                                    <?php if ($userRole === 'project_manager'): ?>
                                        <td class="p-4">
                                            <?php
                                            // Get assigned users for this task
                                            $stmt = $pdo->prepare("
                                                SELECT u.name 
                                                FROM users u
                                                INNER JOIN task_users tu ON u.id = tu.user_id
                                                WHERE tu.task_id = ?
                                            ");
                                            $stmt->execute([$task->id]);
                                            $assignedUsers = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                                            
                                            if (!empty($assignedUsers)): ?>
                                                <div class="text-sm">
                                                    <?= implode(', ', array_map('htmlspecialchars', $assignedUsers)) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-sm">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="p-4">
                                        <div class="flex space-x-2">
                                            <a href="view-task.php?id=<?= $task->id ?>" 
                                               class="text-green-600 hover:text-green-800 hover:underline">View</a>
                                            <a href="update-status.php?id=<?= $task->id ?>" 
                                               class="text-blue-600 hover:text-blue-800 hover:underline">Update Status</a>
                                            <?php if ($userRole === 'project_manager'): ?>
                                                <a href="edit-task.php?id=<?= $task->id ?>" 
                                                   class="text-yellow-600 hover:text-yellow-800 hover:underline">Edit</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>