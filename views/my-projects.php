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
$projectRepo = new ProjectRepository($pdo);
$sprintRepo = new SprintRepository($pdo);
$taskRepo = new TaskRepository($pdo);

// Get user's projects (if manager) or all projects (if admin)
if ($_SESSION['user']['role'] === 'admin') {
    $myProjects = $projectRepo->getAll();
} else {
    $myProjects = $projectRepo->getByManager($userId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">My Projects</h1>
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
                    <h1 class="text-2xl font-bold">My Projects</h1>
                    <p class="text-gray-600">View all projects you're involved in</p>
                </div>
                <?php if ($_SESSION['user']['role'] === 'project_manager'): ?>
                    <a href="addproject.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        + New Project
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Projects Grid -->
        <?php if (empty($myProjects)): ?>
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="text-gray-500 mb-4">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No projects yet</h3>
                <p class="text-gray-500 mb-4">
                    <?php if ($_SESSION['user']['role'] === 'project_manager'): ?>
                        Create your first project to get started
                    <?php else: ?>
                        You're not assigned to any projects yet
                    <?php endif; ?>
                </p>
                <?php if ($_SESSION['user']['role'] === 'project_manager'): ?>
                    <a href="addproject.php" class="inline-block bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Create Your First Project
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($myProjects as $project): 
                    // Get project statistics
                    $sprints = $sprintRepo->getByProject($project->id);
                    $totalTasks = 0;
                    $completedTasks = 0;
                    
                    foreach ($sprints as $sprint) {
                        $sprintTasks = $taskRepo->getBySprint($sprint->id);
                        $totalTasks += count($sprintTasks);
                        $completedTasks += count(array_filter($sprintTasks, fn($t) => $t->status === 'done'));
                    }
                ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition duration-200">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($project->title) ?></h3>
                                    <div class="flex items-center mt-1">
                                        <span class="text-xs px-2 py-1 rounded <?= 
                                            $project->status === 'active' ? 'bg-green-100 text-green-800' :
                                            ($project->status === 'completed' ? 'bg-blue-100 text-blue-800' : 
                                            'bg-gray-100 text-gray-800')
                                        ?>">
                                            <?= ucfirst($project->status) ?>
                                        </span>
                                        <span class="text-xs text-gray-500 ml-2">
                                            Created: <?= date('M d, Y', strtotime($project->created_at)) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 mb-4 text-sm">
                                <?= htmlspecialchars($project->description ?? 'No description provided') ?>
                            </p>
                            
                            <!-- Project Stats -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900"><?= count($sprints) ?></div>
                                    <div class="text-xs text-gray-500">Sprints</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900"><?= $totalTasks ?></div>
                                    <div class="text-xs text-gray-500">Total Tasks</div>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <?php if ($totalTasks > 0): ?>
                                <div class="mb-4">
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>Progress</span>
                                        <span><?= round(($completedTasks / $totalTasks) * 100) ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" 
                                             style="width: <?= ($completedTasks / $totalTasks) * 100 ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div class="flex justify-between items-center pt-4 border-t">
                                <a href="project-details.php?id=<?= $project->id ?>" 
                                   class="text-green-600 hover:text-green-800 hover:underline text-sm">
                                    View Details
                                </a>
                                <?php if ($_SESSION['user']['role'] === 'project_manager' || $_SESSION['user']['role'] === 'admin'): ?>
                                    <div class="flex space-x-2">
                                        <a href="editproject.php?id=<?= $project->id ?>" 
                                           class="text-blue-600 hover:text-blue-800 hover:underline text-sm">Edit</a>
                                        <a href="deleteproject.php?id=<?= $project->id ?>" 
                                           class="text-red-600 hover:text-red-800 hover:underline text-sm"
                                           onclick="return confirm('Delete this project?')">Delete</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>