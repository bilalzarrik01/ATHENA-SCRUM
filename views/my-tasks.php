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
$taskRepo = new TaskRepository($pdo);
$sprintRepo = new SprintRepository($pdo);

// Get user's assigned tasks
$myTasks = $taskRepo->getByUser($userId);

// Filter tasks by status
$status = $_GET['status'] ?? 'all';
if ($status !== 'all') {
    $myTasks = array_filter($myTasks, fn($t) => $t->status === $status);
}

// Group tasks by status
$todoTasks = array_filter($myTasks, fn($t) => $t->status === 'todo');
$inProgressTasks = array_filter($myTasks, fn($t) => $t->status === 'in_progress');
$doneTasks = array_filter($myTasks, fn($t) => $t->status === 'done');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">My Tasks</h1>
                <span class="bg-green-800 px-3 py-1 rounded-full text-sm">Member</span>
            </div>
            <div class="flex items-center space-x-4">
                <span>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                <a href="dashboardmember.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded">Dashboard</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">My Tasks</h1>
                    <p class="text-gray-600">View and manage all tasks assigned to you</p>
                </div>
                <div class="flex gap-2">
                    <!-- <a href="addtask.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        + New Task
                    </a> -->
                </div>
            </div>
        </div>

        <!-- Stats -->
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
                       class="px-6 py-3 <?= $status === 'all' ? 'border-b-2 border-green-500 text-green-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        All Tasks (<?= count($myTasks) ?>)
                    </a>
                    <a href="?status=todo" 
                       class="px-6 py-3 <?= $status === 'todo' ? 'border-b-2 border-red-500 text-red-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        To Do (<?= count($todoTasks) ?>)
                    </a>
                    <a href="?status=in_progress" 
                       class="px-6 py-3 <?= $status === 'in_progress' ? 'border-b-2 border-yellow-500 text-yellow-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        In Progress (<?= count($inProgressTasks) ?>)
                    </a>
                    <a href="?status=done" 
                       class="px-6 py-3 <?= $status === 'done' ? 'border-b-2 border-green-500 text-green-600' : 'text-gray-500 hover:text-gray-700' ?>">
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
                            <th class="p-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myTasks)): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    No tasks found.
                                    <?php if ($status !== 'all'): ?>
                                        <a href="?status=all" class="text-green-600 hover:underline ml-2">View all tasks</a>
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
                                            'todo' => 'bg-gray-100 text-gray-800',
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
                                    <td class="p-4">
                                        <div class="flex space-x-2">
                                            <a href="view-task.php?id=<?= $task->id ?>" 
                                               class="text-green-600 hover:text-green-800 hover:underline">View</a>
                                            <a href="update-status.php?id=<?= $task->id ?>" 
                                               class="text-blue-600 hover:text-blue-800 hover:underline">Update Status</a>
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