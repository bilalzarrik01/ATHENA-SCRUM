<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$sprintRepo = new SprintRepository($pdo);
$projectRepo = new ProjectRepository($pdo);
$taskRepo = new TaskRepository($pdo);

// Get user's projects
if ($_SESSION['user']['role'] === 'admin') {
    $myProjects = $projectRepo->getAll();
} else {
    $myProjects = $projectRepo->getByManager($userId);
}

// Get all sprints from user's projects
$mySprints = [];
foreach ($myProjects as $project) {
    $projectSprints = $sprintRepo->getByProject($project->id);
    foreach ($projectSprints as $sprint) {
        $mySprints[] = $sprint;
    }
}

// Filter by status
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'active') {
    $mySprints = array_filter($mySprints, function($sprint) {
        $today = date('Y-m-d');
        return $sprint->start_date <= $today && $sprint->end_date >= $today;
    });
} elseif ($filter === 'upcoming') {
    $mySprints = array_filter($mySprints, function($sprint) {
        $today = date('Y-m-d');
        return $sprint->start_date > $today;
    });
} elseif ($filter === 'past') {
    $mySprints = array_filter($mySprints, function($sprint) {
        $today = date('Y-m-d');
        return $sprint->end_date < $today;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sprints - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">My Sprints</h1>
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
                    <h1 class="text-2xl font-bold">My Sprints</h1>
                    <p class="text-gray-600">View all sprints from your projects</p>
                </div>
                <?php if ($_SESSION['user']['role'] === 'project_manager'): ?>
                    <a href="addsprint.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        + New Sprint
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="border-b">
                <nav class="flex">
                    <a href="?filter=all" 
                       class="px-6 py-3 <?= $filter === 'all' ? 'border-b-2 border-green-500 text-green-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        All Sprints (<?= count($mySprints) ?>)
                    </a>
                    <a href="?filter=active" 
                       class="px-6 py-3 <?= $filter === 'active' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        Active
                    </a>
                    <a href="?filter=upcoming" 
                       class="px-6 py-3 <?= $filter === 'upcoming' ? 'border-b-2 border-yellow-500 text-yellow-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        Upcoming
                    </a>
                    <a href="?filter=past" 
                       class="px-6 py-3 <?= $filter === 'past' ? 'border-b-2 border-gray-500 text-gray-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        Past
                    </a>
                </nav>
            </div>
        </div>

        <!-- Sprints Table -->
        <?php if (empty($mySprints)): ?>
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="text-gray-500 mb-4">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No sprints found</h3>
                <p class="text-gray-500 mb-4">
                    <?php if ($filter !== 'all'): ?>
                        No <?= $filter ?> sprints. <a href="?filter=all" class="text-green-600 hover:underline">View all sprints</a>
                    <?php else: ?>
                        No sprints created yet for your projects.
                    <?php endif; ?>
                </p>
                <?php if ($_SESSION['user']['role'] === 'project_manager'): ?>
                    <a href="addsprint.php" class="inline-block bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Create Your First Sprint
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-4 text-left">Sprint Name</th>
                                <th class="p-4 text-left">Project</th>
                                <th class="p-4 text-left">Duration</th>
                                <th class="p-4 text-left">Tasks</th>
                                <th class="p-4 text-left">Status</th>
                                <th class="p-4 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mySprints as $sprint): 
                                $project = $projectRepo->getById($sprint->project_id);
                                $tasks = $taskRepo->getBySprint($sprint->id);
                                $completedTasks = count(array_filter($tasks, fn($t) => $t->status === 'done'));
                                
                                // Determine sprint status
                                $today = date('Y-m-d');
                                if ($sprint->start_date > $today) {
                                    $status = 'upcoming';
                                    $statusColor = 'bg-yellow-100 text-yellow-800';
                                    $statusText = 'Upcoming';
                                } elseif ($sprint->end_date < $today) {
                                    $status = 'past';
                                    $statusColor = 'bg-gray-100 text-gray-800';
                                    $statusText = 'Completed';
                                } else {
                                    $status = 'active';
                                    $statusColor = 'bg-green-100 text-green-800';
                                    $statusText = 'Active';
                                }
                            ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="p-4">
                                        <div class="font-medium"><?= htmlspecialchars($sprint->name) ?></div>
                                        <div class="text-gray-500 text-sm">
                                            ID: <?= $sprint->id ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <?= $project ? htmlspecialchars($project->title) : 'Project #' . $sprint->project_id ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm">
                                            <div><?= date('M d, Y', strtotime($sprint->start_date)) ?></div>
                                            <div class="text-gray-500">to</div>
                                            <div><?= date('M d, Y', strtotime($sprint->end_date)) ?></div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm">
                                            <div class="font-medium"><?= count($tasks) ?> tasks</div>
                                            <div class="text-gray-500"><?= $completedTasks ?> completed</div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-sm <?= $statusColor ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex space-x-2">
                                            <a href="sprint-tasks.php?id=<?= $sprint->id ?>" 
                                               class="text-green-600 hover:text-green-800 hover:underline text-sm">View Tasks</a>
                                            <?php if ($_SESSION['user']['role'] === 'project_manager' || $_SESSION['user']['role'] === 'admin'): ?>
                                                <a href="editsprint.php?id=<?= $sprint->id ?>" 
                                                   class="text-blue-600 hover:text-blue-800 hover:underline text-sm">Edit</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>