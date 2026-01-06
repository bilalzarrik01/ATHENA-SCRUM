<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$taskRepo = new TaskRepository($pdo);
$sprintRepo = new SprintRepository($pdo);
$projectRepo = new ProjectRepository($pdo);

// Get user's assigned tasks
$myTasks = $taskRepo->getByUser($userId);
$todoTasks = array_filter($myTasks, fn($t) => $t->status === 'todo');
$inProgressTasks = array_filter($myTasks, fn($t) => $t->status === 'in_progress');
$doneTasks = array_filter($myTasks, fn($t) => $t->status === 'done');

// Get projects user is involved in
// Line 26 in dashboardmember.php - REPLACE THIS:
// $myProjects = $projectRepo->getByMember($userId);

// WITH THIS (temporary fix):
try {
    $myProjects = $projectRepo->getByManager($userId); // Only shows projects user manages
} catch (Exception $e) {
    // If method doesn't exist, create an empty array
    $myProjects = [];
    
    // Or use getAll() if user is admin
    if ($_SESSION['user']['role'] === 'admin') {
        $myProjects = $projectRepo->getAll();
    }
}

// Get active sprints from user's projects
$myActiveSprints = [];
foreach ($myProjects as $project) {
    $projectSprints = $sprintRepo->getActiveSprints($project->id);
    $myActiveSprints = array_merge($myActiveSprints, $projectSprints);
}

// Get recent activities (tasks assigned to user)
$recentAssignedTasks = array_slice($myTasks, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">ScrumATHENA</h1>
                <span class="bg-green-800 px-3 py-1 rounded-full text-sm">Member</span>
            </div>
            <div class="flex items-center space-x-4">
                <span>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
        <div class="container mx-auto mt-2">
            <div class="flex space-x-4">
                <a href="dashboard-member.php" class="px-4 py-2 bg-green-700 rounded">Dashboard</a>
                <a href="my-tasks.php" class="px-4 py-2 hover:bg-green-700 rounded">My Tasks</a>
                <a href="my-projects.php" class="px-4 py-2 hover:bg-green-700 rounded">Projects</a>
                <a href="my-sprints.php" class="px-4 py-2 hover:bg-green-700 rounded">Sprints</a>
                <a href="profile.php" class="px-4 py-2 hover:bg-green-700 rounded">Profile</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Welcome Message -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h1 class="text-2xl font-bold mb-2">Welcome back, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h1>
            <p class="text-gray-600">Here's an overview of your work and assigned tasks.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
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

            <div class="bg-white p-6 rounded-lg shadow">
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

            <div class="bg-white p-6 rounded-lg shadow">
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

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Active Projects</h3>
                        <p class="text-3xl font-bold"><?= count($myProjects) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- My Tasks -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">My Tasks</h2>
                        <a href="my-tasks.php" class="text-green-600 hover:underline">View all</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left">Task</th>
                                    <th class="p-2 text-left">Project</th>
                                    <th class="p-2 text-left">Priority</th>
                                    <th class="p-2 text-left">Status</th>
                                    <th class="p-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($myTasks, 0, 5) as $task): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-2">
                                        <div class="font-medium"><?= htmlspecialchars($task->title) ?></div>
                                        <div class="text-gray-500 text-sm"><?= substr($task->description, 0, 50) ?>...</div>
                                    </td>
                                    <td class="p-2"><?= htmlspecialchars($task->sprint_name) ?></td>
                                    <td class="p-2">
                                        <?php 
                                        $priorityColors = [
                                            'high' => 'bg-red-100 text-red-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'low' => 'bg-green-100 text-green-800'
                                        ];
                                        $color = $priorityColors[$task->priority] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 py-1 rounded text-xs <?= $color ?>">
                                            <?= ucfirst($task->priority) ?>
                                        </span>
                                    </td>
                                    <td class="p-2">
                                        <?php 
                                        $statusColors = [
                                            'todo' => 'bg-gray-100 text-gray-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'done' => 'bg-green-100 text-green-800'
                                        ];
                                        $color = $statusColors[$task->status] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 py-1 rounded text-xs <?= $color ?>">
                                            <?= ucfirst(str_replace('_', ' ', $task->status)) ?>
                                        </span>
                                    </td>
                                    <td class="p-2">
                                        <a href="view-task.php?id=<?= $task->id ?>" class="text-green-600 hover:underline">View</a>
                                        <a href="update-task.php?id=<?= $task->id ?>" class="ml-2 text-blue-600 hover:underline">Update</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Active Sprints -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Active Sprints</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach (array_slice($myActiveSprints, 0, 4) as $sprint): ?>
                        <div class="border rounded-lg p-4 hover:shadow-md">
                            <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($sprint->name) ?></h3>
                            <p class="text-gray-600 mb-2">Project: <?= htmlspecialchars($sprint->project_name) ?></p>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Start: <?= $sprint->getFormattedStartDate() ?></span>
                                <span>End: <?= $sprint->getFormattedEndDate() ?></span>
                            </div>
                            <div class="mt-3">
                                <a href="sprint-tasks.php?id=<?= $sprint->id ?>" class="text-green-600 hover:underline">View Tasks →</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- My Projects -->
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <h2 class="text-xl font-bold mb-4">My Projects</h2>
                    <div class="space-y-3">
                        <?php foreach (array_slice($myProjects, 0, 5) as $project): ?>
                        <div class="flex items-center p-3 border rounded hover:bg-gray-50">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                            <div>
                                <h4 class="font-medium"><?= htmlspecialchars($project->title) ?></h4>
                                <p class="text-gray-500 text-sm"><?= $project->manager_name ?? 'N/A' ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($myProjects) > 5): ?>
                    <div class="mt-4 text-center">
                        <a href="my-projects.php" class="text-green-600 hover:underline">View all projects</a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="addtask.php" class="block w-full bg-green-500 hover:bg-green-600 text-white text-center py-2 rounded">
                            + Create New Task
                        </a>
                        <a href="update-status.php" class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-2 rounded">
                            Update Task Status
                        </a>
                        <a href="report-issue.php" class="block w-full bg-yellow-500 hover:bg-yellow-600 text-white text-center py-2 rounded">
                            Report Issue
                        </a>
                        <a href="calendar.php" class="block w-full bg-purple-500 hover:bg-purple-600 text-white text-center py-2 rounded">
                            View Calendar
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white p-6 rounded-lg shadow mt-6">
                    <h2 class="text-xl font-bold mb-4">Recent Activity</h2>
                    <div class="space-y-4">
                        <?php foreach ($recentAssignedTasks as $task): ?>
                        <div class="border-l-4 border-green-500 pl-4 py-2">
                            <p class="font-medium">Assigned to you</p>
                            <p class="text-gray-600 text-sm">"<?= htmlspecialchars($task->title) ?>"</p>
                            <p class="text-gray-500 text-xs"><?= date('M d, H:i', strtotime($task->created_at)) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>