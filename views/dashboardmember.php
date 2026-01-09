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

// If no tasks assigned, show a message but don't crash
if (empty($myTasks)) {
    $noTasksMessage = "No tasks assigned to you yet.";
}

// Count tasks by status
$todoTasks = array_filter($myTasks, fn($t) => $t->status === 'todo');
$inProgressTasks = array_filter($myTasks, fn($t) => $t->status === 'in_progress');
$doneTasks = array_filter($myTasks, fn($t) => $t->status === 'done');

// Get projects user manages (for managers) or all projects (for admins)
$myProjects = [];
if ($_SESSION['user']['role'] === 'admin') {
    $myProjects = $projectRepo->getAll();
} elseif ($_SESSION['user']['role'] === 'project_manager') {
    $myProjects = $projectRepo->getByManager($userId);
}

// Get active sprints from user's projects
$myActiveSprints = [];
foreach ($myProjects as $project) {
    $projectSprints = $sprintRepo->getActiveSprints($project->id);
    $myActiveSprints = array_merge($myActiveSprints, $projectSprints);
}

// Get recent assigned tasks
$recentAssignedTasks = array_slice($myTasks, 0, 5);

// Check for messages
$successMessage = $_GET['success'] ?? '';
$errorMessage = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">ScrumATHENA</h1>
                <span class="bg-green-800 px-3 py-1 rounded-full text-sm">
                    <?= ucfirst($_SESSION['user']['role']) ?>
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <span>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
        <div class="container mx-auto mt-2">
            <div class="flex space-x-4">
                <a href="dashboardmember.php" class="px-4 py-2 bg-green-700 rounded">Dashboard</a>
                <a href="my-tasks.php" class="px-4 py-2 hover:bg-green-700 rounded">My Tasks</a>
                <a href="my-projects.php" class="px-4 py-2 hover:bg-green-700 rounded">Projects</a>
                <a href="my-sprints.php" class="px-4 py-2 hover:bg-green-700 rounded">Sprints</a>
                <a href="profile.php" class="px-4 py-2 hover:bg-green-700 rounded">Profile</a>
                
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Messages -->
        <?php if ($successMessage): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Message -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h1 class="text-2xl font-bold mb-2">Welcome back, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h1>
            <p class="text-gray-600">Here's an overview of your work and assigned tasks.</p>
            
            <?php if (isset($noTasksMessage)): ?>
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-yellow-800"><?= $noTasksMessage ?></p>
                    <p class="text-sm text-yellow-600 mt-1">
                        Tasks will appear here once they are assigned to you by a project manager.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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

            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
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
                    
                    <?php if (empty($myTasks)): ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500 mb-4">No tasks assigned to you yet.</p>
                            <p class="text-sm text-gray-400">Tasks will appear here once assigned by a manager.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 text-left">Task</th>
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
                                            <div class="text-gray-500 text-sm"><?= substr($task->description ?? 'No description', 0, 50) ?>...</div>
                                        </td>
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
                                            <div class="flex space-x-2">
                                                <a href="view-task.php?id=<?= $task->id ?>" 
                                                   class="text-green-600 hover:text-green-800 hover:underline text-sm">View</a>
                                                <a href="update-status.php?id=<?= $task->id ?>" 
                                                   class="text-blue-600 hover:text-blue-800 hover:underline text-sm">Update</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Active Sprints -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Active Sprints</h2>
                    <?php if (empty($myActiveSprints)): ?>
                        <p class="text-gray-500">No active sprints in your projects.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach (array_slice($myActiveSprints, 0, 4) as $sprint): ?>
                            <div class="border rounded-lg p-4 hover:shadow-md transition">
                                <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($sprint->name) ?></h3>
                                <p class="text-gray-600 mb-2">Project: <?= htmlspecialchars($sprint->project_title) ?></p>
                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>Start: <?= $sprint->start_date ?></span>
                                    <span>End: <?= $sprint->end_date ?></span>
                                </div>
                                <div class="mt-3">
                                    <a href="sprint-tasks.php?id=<?= $sprint->id ?>" 
                                       class="text-green-600 hover:text-green-800 hover:underline text-sm">View Tasks →</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- My Projects -->
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <h2 class="text-xl font-bold mb-4">My Projects</h2>
                    <?php if (empty($myProjects)): ?>
                        <p class="text-gray-500">No projects assigned.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($myProjects, 0, 5) as $project): ?>
                            <div class="flex items-center p-3 border rounded hover:bg-gray-50 transition">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium truncate"><?= htmlspecialchars($project->title) ?></h4>
                                    <p class="text-gray-500 text-sm truncate"><?= htmlspecialchars($project->description ?? 'No description') ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($myProjects) > 5): ?>
                        <div class="mt-4 text-center">
                            <a href="my-projects.php" class="text-green-600 hover:underline">View all projects</a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <?php if ($_SESSION['user']['role'] === 'project_manager'): ?>
                            <a href="addtask.php" class="block w-full bg-green-500 hover:bg-green-600 text-white text-center py-2 rounded transition">
                                + Create New Task
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($myTasks)): ?>
                            <a href="update-status.php" class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-2 rounded transition">
                                Update Task Status
                            </a>
                        <?php endif; ?>
                        <a href="profile.php" class="block w-full bg-purple-500 hover:bg-purple-600 text-white text-center py-2 rounded transition">
                            Edit Profile
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white p-6 rounded-lg shadow mt-6">
                    <h2 class="text-xl font-bold mb-4">Recent Activity</h2>
                    <div class="space-y-4">
                        <?php if (empty($recentAssignedTasks)): ?>
                            <p class="text-gray-500 text-center py-2">No recent activity</p>
                        <?php else: ?>
                            <?php foreach ($recentAssignedTasks as $task): ?>
                            <div class="border-l-4 border-green-500 pl-4 py-2 hover:bg-gray-50">
                                <p class="font-medium text-sm">Task assigned</p>
                                <p class="text-gray-600 text-sm">"<?= htmlspecialchars($task->title) ?>"</p>
                                <p class="text-gray-500 text-xs"><?= date('M d, H:i', strtotime($task->created_at)) ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
        
    </div>
</body>
</html>
