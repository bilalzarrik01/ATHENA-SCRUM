<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role']; // 'project_manager' or 'team_member'
$userName = $_SESSION['user']['name'];

$projectRepo = new ProjectRepository($pdo);
$sprintRepo = new SprintRepository($pdo);
$taskRepo = new TaskRepository($pdo);

// Get projects based on user role
if ($userRole === 'project_manager' || $userRole === 'admin') {
    // Manager/Admin sees all projects (admin sees all, manager sees their projects)
    if ($userRole === 'admin') {
        $myProjects = $projectRepo->getAll();
    } else {
        $myProjects = $projectRepo->getByManager($userId);
    }
} else {
    // Team member sees only projects where they have assigned tasks
    
    // Method 1: Get projects through tasks
    $userTasks = $taskRepo->getByUser($userId);
    
    // Get unique project IDs from user's tasks via sprints
    $projectIds = [];
    foreach ($userTasks as $task) {
        if ($task->sprint_id > 0) {
            // Get sprint to find project
            $sprint = $sprintRepo->getById($task->sprint_id);
            if ($sprint && $sprint->project_id > 0) {
                if (!in_array($sprint->project_id, $projectIds)) {
                    $projectIds[] = $sprint->project_id;
                }
            }
        }
    }
    
    // Get projects by IDs
    $myProjects = [];
    foreach ($projectIds as $projectId) {
        $project = $projectRepo->getById($projectId);
        if ($project) {
            $myProjects[] = $project;
        }
    }
    
    // Alternative simpler method if you have direct task->project relationship
    // This would require modifying your database or adding a join
}

// Filter projects
$filter = $_GET['filter'] ?? 'all';
$filteredProjects = [];

if ($filter === 'active') {
    $filteredProjects = array_filter($myProjects, function($project) {
        // You might want to add an 'active' field to projects
        return true; // Default all are active
    });
} else {
    $filteredProjects = $myProjects;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - ScrumATHENA</title>
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
                        echo 'All Projects';
                    } else {
                        echo 'My Projects';
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
                            echo 'All Projects';
                        } else {
                            echo 'My Assigned Projects';
                        }
                        ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($userRole === 'project_manager' || $userRole === 'admin') {
                            echo "View and manage all projects";
                        } else {
                            echo "View projects where you have assigned tasks";
                        }
                        ?>
                    </p>
                </div>
                <?php if ($userRole === 'project_manager' || $userRole === 'admin'): ?>
                    <a href="addproject.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        + New Project
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
                        All Projects (<?= count($myProjects) ?>)
                    </a>
                    <a href="?filter=active" 
                       class="px-6 py-3 <?= $filter === 'active' ? 'active-tab border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        Active
                    </a>
                    <a href="?filter=completed" 
                       class="px-6 py-3 <?= $filter === 'completed' ? 'active-tab border-gray-500 text-gray-600' : 'text-gray-500 hover:text-gray-700' ?>">
                        Completed
                    </a>
                </nav>
            </div>
        </div>

        <!-- Projects Grid -->
        <?php if (empty($filteredProjects)): ?>
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="text-gray-500 mb-4">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No projects found</h3>
                <p class="text-gray-500 mb-4">
                    <?php if ($filter !== 'all'): ?>
                        No <?= $filter ?> projects. <a href="?filter=all" class="text-green-600 hover:underline">View all projects</a>
                    <?php else: ?>
                        <?php 
                        if ($userRole === 'project_manager' || $userRole === 'admin') {
                            echo "No projects created yet.";
                        } else {
                            echo "You don't have any assigned tasks in any projects yet.";
                        }
                        ?>
                    <?php endif; ?>
                </p>
                <?php if ($userRole === 'project_manager' || $userRole === 'admin'): ?>
                    <a href="addproject.php" class="inline-block bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Create Your First Project
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($filteredProjects as $project): 
                    // Get project stats
                    $sprints = $sprintRepo->getByProject($project->id);
                    $totalSprints = count($sprints);
                    
                    // Count all tasks in project
                    $totalTasks = 0;
                    $completedTasks = 0;
                    $myTasksInProject = 0;
                    
                    foreach ($sprints as $sprint) {
                        $tasks = $taskRepo->getBySprint($sprint->id);
                        $totalTasks += count($tasks);
                        $completedTasks += count(array_filter($tasks, fn($t) => $t->status === 'done'));
                        
                        // Count user's tasks in this project if not manager/admin
                        if ($userRole !== 'project_manager' && $userRole !== 'admin') {
                            foreach ($tasks as $task) {
                                // Check if task is assigned to user
                                $stmt = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM task_users WHERE task_id = ? AND user_id = ?");
                                $stmt->execute([$task->id, $userId]);
                                if ($stmt->fetchColumn() > 0) {
                                    $myTasksInProject++;
                                }
                            }
                        }
                    }
                    
                    // Calculate progress
                    $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    
                    // Determine project status (you might want to add a status field to projects)
                    $projectStatus = 'active';
                    $statusColor = 'bg-green-100 text-green-800';
                    $statusText = 'Active';
                ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($project->title) ?></h3>
                                    <span class="px-3 py-1 rounded-full text-sm <?= $statusColor ?>">
                                        <?= $statusText ?>
                                    </span>
                                </div>
                                <?php if ($userRole === 'project_manager' || $userRole === 'admin'): ?>
                                    <div class="flex gap-2">
                                        <a href="editproject.php?id=<?= $project->id ?>" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-gray-600 mb-6 line-clamp-2">
                                <?= htmlspecialchars($project->description) ?>
                            </p>
                            
                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-500 mb-1">
                                    <span>Progress</span>
                                    <span><?= $progress ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900"><?= $totalSprints ?></div>
                                    <div class="text-sm text-gray-500">Sprints</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900"><?= $totalTasks ?></div>
                                    <div class="text-sm text-gray-500">Tasks</div>
                                </div>
                            </div>
                            
                            <?php if ($userRole !== 'project_manager' && $userRole !== 'admin'): ?>
                                <div class="mb-6 p-3 bg-blue-50 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <span class="text-sm text-blue-700">
                                            <?= $myTasksInProject ?> tasks assigned to you
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div class="flex justify-between">
                                <a href="project-sprints.php?id=<?= $project->id ?>" 
                                   class="text-green-600 hover:text-green-800 hover:underline text-sm">
                                    View Sprints
                                </a>
                                <a href="project-details.php?id=<?= $project->id ?>" 
                                   class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                        
                        <!-- Footer with manager info -->
                        <div class="bg-gray-50 px-6 py-3 border-t">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    Created: <?= date('M d, Y', strtotime($project->created_at)) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Manager: <?= htmlspecialchars($project->manager_name ?? 'N/A') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>