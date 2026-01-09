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
$userRole = $_SESSION['user']['role']; // 'project_manager' or 'team_member'
$userName = $_SESSION['user']['name'];

$sprintRepo = new SprintRepository($pdo);
$projectRepo = new ProjectRepository($pdo);
$taskRepo = new TaskRepository($pdo);

// Get sprints based on user role
if ($userRole === 'project_manager' || $userRole === 'admin') {
    // Manager/Admin sees all sprints from their projects
    if ($userRole === 'admin') {
        $myProjects = $projectRepo->getAll();
    } else {
        $myProjects = $projectRepo->getByManager($userId);
    }
    
    // Get all sprints from user's projects
    $allSprints = [];
    foreach ($myProjects as $project) {
        $projectSprints = $sprintRepo->getByProject($project->id);
        foreach ($projectSprints as $sprint) {
            $allSprints[] = $sprint;
        }
    }
} else {
    // Team member sees only sprints where they have assigned tasks
    // First, get all tasks assigned to this user
    $userTasks = $taskRepo->getByUser($userId);
    
    // Get unique sprint IDs from user's tasks
    $sprintIds = [];
    foreach ($userTasks as $task) {
        if ($task->sprint_id > 0 && !in_array($task->sprint_id, $sprintIds)) {
            $sprintIds[] = $task->sprint_id;
        }
    }
    
    // Get sprints by IDs
    $allSprints = [];
    foreach ($sprintIds as $sprintId) {
        $sprint = $sprintRepo->getById($sprintId);
        if ($sprint) {
            $allSprints[] = $sprint;
        }
    }
}

// Filter by status
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'active') {
    $mySprints = array_filter($allSprints, function($sprint) {
        $today = date('Y-m-d');
        return $sprint->start_date <= $today && $sprint->end_date >= $today;
    });
} elseif ($filter === 'upcoming') {
    $mySprints = array_filter($allSprints, function($sprint) {
        $today = date('Y-m-d');
        return $sprint->start_date > $today;
    });
} elseif ($filter === 'past') {
    $mySprints = array_filter($allSprints, function($sprint) {
        $today = date('Y-m-d');
        return $sprint->end_date < $today;
    });
} else {
    $mySprints = $allSprints;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sprints - ScrumATHENA</title>
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
                    <?php 
                    if ($userRole === 'project_manager' || $userRole === 'admin') {
                        echo 'All Sprints';
                    } else {
                        echo 'My Sprints';
                    }
                    ?>
                </h1>
                <span class="bg-green-800 px-3 py-1 rounded-full text-sm">
                    <?php 
                    // Format role for display
                    if ($userRole === 'project_manager') {
                        echo 'Manager';
                    } elseif ($userRole === 'admin') {
                        echo 'Admin';
                    } else {
                        echo 'Team Member';
                    }
                    ?>
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-300">Welcome, <?= htmlspecialchars($userName) ?></span>
                <a href="<?php 
                    if ($userRole === 'project_manager' || $userRole === 'admin') {
                        echo 'dashboardchef.php';
                    } else {
                        echo 'dashboardmember.php';
                    }
                ?>" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded text-white">
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
                        <?php 
                        if ($userRole === 'project_manager' || $userRole === 'admin') {
                            echo 'All Project Sprints';
                        } else {
                            echo 'My Assigned Sprints';
                        }
                        ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($userRole === 'project_manager' || $userRole === 'admin') {
                            echo "View and manage all sprints from your projects";
                        } else {
                            echo "View sprints where you have assigned tasks";
                        }
                        ?>
                    </p>
                </div>
                <?php if ($userRole === 'project_manager' || $userRole === 'admin'): ?>
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
                       class="px-6 py-3 <?= $filter === 'all' ? 'active-tab border-green-500 text-green-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        All Sprints (<?= count($allSprints) ?>)
                    </a>
                    <a href="?filter=active" 
                       class="px-6 py-3 <?= $filter === 'active' ? 'active-tab border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        Active
                    </a>
                    <a href="?filter=upcoming" 
                       class="px-6 py-3 <?= $filter === 'upcoming' ? 'active-tab border-yellow-500 text-yellow-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        Upcoming
                    </a>
                    <a href="?filter=past" 
                       class="px-6 py-3 <?= $filter === 'past' ? 'active-tab border-gray-500 text-gray-600' : 'text-gray-500 hover:text-gray-700' ?>">
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
                        <?php 
                        if ($userRole === 'project_manager' || $userRole === 'admin') {
                            echo "No sprints created yet for your projects.";
                        } else {
                            echo "You don't have any assigned tasks in any sprints yet.";
                        }
                        ?>
                    <?php endif; ?>
                </p>
                <?php if ($userRole === 'project_manager' || $userRole === 'admin'): ?>
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
                                <th class="p-4 text-left">My Tasks</th>
                                <th class="p-4 text-left">Status</th>
                                <th class="p-4 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mySprints as $sprint): 
                                $project = $projectRepo->getById($sprint->project_id);
                                $allTasks = $taskRepo->getBySprint($sprint->id);
                                $completedTasks = count(array_filter($allTasks, fn($t) => $t->status === 'done'));
                                
                                // Get user's tasks in this sprint (for team members)
                                $myTasksInSprint = [];
                         $myTasksInSprint = [];
if ($userRole !== 'project_manager' && $userRole !== 'admin') {
    // Use the repository method
    $myTasksInSprint = $taskRepo->getByUserAndSprint($userId, $sprint->id);
                                }
                                
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
                                            <div class="font-medium"><?= count($allTasks) ?> total</div>
                                            <div class="text-gray-500"><?= $completedTasks ?> completed</div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($userRole !== 'project_manager' && $userRole !== 'admin'): ?>
                                            <div class="text-sm">
                                                <div class="font-medium text-blue-600"><?= count($myTasksInSprint) ?> assigned</div>
                                                <?php 
                                                $myCompleted = count(array_filter($myTasksInSprint, fn($t) => $t->status === 'done'));
                                                ?>
                                                <div class="text-gray-500"><?= $myCompleted ?> completed</div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">-</span>
                                        <?php endif; ?>
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
                                            <?php if ($userRole === 'project_manager' || $userRole === 'admin'): ?>
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