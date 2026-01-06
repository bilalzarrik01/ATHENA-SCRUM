<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';

// Check if user is manager
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'project_manager') {
    header("Location: index.php");
    exit;
}

$managerId = $_SESSION['user']['id'];

$projectRepo = new ProjectRepository($pdo);
$sprintRepo  = new SprintRepository($pdo);
$taskRepo    = new TaskRepository($pdo);

// Get manager's projects
$projects = $projectRepo->getByManager($managerId);
$totalProjects = count($projects);

// Get sprints for these projects
$sprints = [];
$sprintCountByProject = [];
foreach ($projects as $project) {
    if ($project->id > 0) {
        $projectSprints = $sprintRepo->getByProject($project->id);
        $sprints = array_merge($sprints, $projectSprints);
        $sprintCountByProject[$project->id] = count($projectSprints);
    }
}
$totalSprints = count($sprints);

// Get tasks for these sprints
$tasks = [];
$taskCountBySprint = [];
foreach ($sprints as $sprint) {
    if ($sprint->id > 0) {
        $sprintTasks = $taskRepo->getBySprint($sprint->id);
        $tasks = array_merge($tasks, $sprintTasks);
        $taskCountBySprint[$sprint->id] = count($sprintTasks);
    }
}
$totalTasks = count($tasks);
$completedTasks = 0;
$todoTasks = 0;
$inProgressTasks = 0;

foreach ($tasks as $task) {
    if ($task->status === 'done') $completedTasks++;
    if ($task->status === 'todo') $todoTasks++;
    if ($task->status === 'in_progress') $inProgressTasks++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manager Dashboard - ScrumATHENA</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<div class="flex h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-black text-white p-6 hidden md:block">
    <div class="flex items-center gap-3 mb-10">
      <img src="image.png" alt="Logo" class="w-10 h-10 object-contain">
      <span class="text-xl font-bold">ScrumATHENA</span>
    </div>
    <nav class="space-y-4">
      <a href="#" class="block hover:text-green-400">Dashboard</a>
      <a href="#projects" class="block hover:text-green-400">Projects</a>
      <a href="#sprints" class="block hover:text-green-400">Sprints</a>
      <a href="#tasks" class="block hover:text-green-400">Tasks</a>
      <a href="#team" class="block hover:text-green-400">Team</a>
    </nav>
    <div class="border-t border-gray-700 pt-4">
      <form action="logout.php" method="POST">
        <button type="submit" class="w-full text-left text-red-400 hover:text-red-600 font-semibold">Logout</button>
      </form>
    </div>
  </aside>

  <!-- Main content -->
  <main class="flex-1 p-6 overflow-y-auto">
    <h2 class="text-3xl font-semibold mb-6">Manager Dashboard</h2>

    <!-- STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Projects</p>
        <h3 class="text-2xl font-bold"><?= $totalProjects ?></h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Sprints</p>
        <h3 class="text-2xl font-bold"><?= $totalSprints ?></h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Tasks</p>
        <h3 class="text-2xl font-bold"><?= $totalTasks ?></h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Completed</p>
        <h3 class="text-2xl font-bold text-green-600"><?= $completedTasks ?></h3>
      </div>
    </div>

    <!-- TASK STATUS BREAKDOWN -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">To Do</p>
        <h3 class="text-2xl font-bold text-yellow-600"><?= $todoTasks ?></h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">In Progress</p>
        <h3 class="text-2xl font-bold text-blue-600"><?= $inProgressTasks ?></h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Completed</p>
        <h3 class="text-2xl font-bold text-green-600"><?= $completedTasks ?></h3>
      </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="bg-white p-6 rounded shadow mb-8">
      <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
      <div class="flex flex-wrap gap-4">
        <a href="addproject.php" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
          + Add Project
        </a>
        <a href="addsprint.php" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
          + Add Sprint
        </a>
        <a href="addtask.php" class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
          + Add Task
        </a>
      </div>
    </div>

    <!-- PROJECTS -->
    <div id="projects" class="bg-white p-6 rounded shadow mb-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">My Projects</h3>
        <a href="addproject.php" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
          + Add Project
        </a>
      </div>
      
      <?php if(empty($projects)): ?>
        <div class="text-center py-8">
          <p class="text-gray-500 mb-4">You don't have any projects yet.</p>
          <a href="addproject.php" class="inline-block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            Create Your First Project
          </a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach($projects as $project): ?>
            <?php 
              $sprintCount = $sprintCountByProject[$project->id] ?? 0;
              $taskCount = 0;
              
              // Calculate total tasks for this project
              $projectSprints = $sprintRepo->getByProject($project->id);
              foreach ($projectSprints as $sprint) {
                $taskCount += $taskCountBySprint[$sprint->id] ?? 0;
              }
            ?>
            <div class="border rounded p-4 hover:shadow-lg transition">
              <div class="flex justify-between items-start mb-2">
                <h4 class="font-medium text-lg"><?= htmlspecialchars($project->title) ?></h4>
                <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-800">
                  Active
                </span>
              </div>
              
              <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($project->description) ?></p>
              
              <div class="flex justify-between items-center text-sm text-gray-500">
                <div>
                  <span class="mr-3">Sprints: <?= $sprintCount ?></span>
                  <span>Tasks: <?= $taskCount ?></span>
                </div>
                <div class="flex gap-2">
                  <a href="editproject.php?id=<?= $project->id ?>" 
                     class="text-blue-600 hover:underline text-sm">Edit</a>
                  <a href="deleteproject.php?id=<?= $project->id ?>" 
                     class="text-red-600 hover:underline text-sm"
                     onclick="return confirm('Delete this project?')">Delete</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- RECENT SPRINTS -->
    <div id="sprints" class="bg-white p-6 rounded shadow mb-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Recent Sprints</h3>
        <a href="addsprint.php" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
          + Add Sprint
        </a>
      </div>
      
      <?php if(empty($sprints)): ?>
        <p class="text-gray-500">No sprints yet.</p>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-3">Name</th>
                <th class="p-3">Project</th>
                <th class="p-3">Start Date</th>
                <th class="p-3">End Date</th>
                <th class="p-3">Tasks</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $recentSprints = array_slice($sprints, 0, 5);
                foreach($recentSprints as $sprint): 
                  $taskCount = $taskCountBySprint[$sprint->id] ?? 0;
              ?>
                <tr class="border-t hover:bg-gray-50">
                  <td class="p-3"><?= htmlspecialchars($sprint->name) ?></td>
                  <td class="p-3">
                    <?php 
                      // Get project name for this sprint
                      $project = $projectRepo->getById($sprint->project_id);
                      echo $project ? htmlspecialchars($project->title) : "Project #{$sprint->project_id}";
                    ?>
                  </td>
                  <td class="p-3"><?= $sprint->start_date ?></td>
                  <td class="p-3"><?= $sprint->end_date ?></td>
                  <td class="p-3"><?= $taskCount ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- RECENT TASKS -->
    <div id="tasks" class="bg-white p-6 rounded shadow">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Recent Tasks</h3>
        <a href="addtask.php" class="px-3 py-1 bg-purple-500 text-white rounded hover:bg-purple-600">
          + Add Task
        </a>
      </div>
      
      <?php if(empty($tasks)): ?>
        <p class="text-gray-500">No tasks yet.</p>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php 
            $recentTasks = array_slice($tasks, 0, 6);
            foreach($recentTasks as $task): 
          ?>
            <div class="border rounded p-4 hover:shadow-lg transition">
              <div class="flex justify-between items-start mb-2">
                <h4 class="font-medium"><?= htmlspecialchars($task->title) ?></h4>
                <span class="text-xs px-2 py-1 rounded <?= 
                  $task->priority === 'high' ? 'bg-red-100 text-red-800' :
                  ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')
                ?>">
                  <?= ucfirst($task->priority) ?>
                </span>
              </div>
              
              <p class="text-sm text-gray-600 mb-3 truncate"><?= htmlspecialchars($task->description) ?></p>
              
              <div class="flex justify-between items-center text-sm">
                <span class="px-2 py-1 rounded text-xs <?= 
                  $task->status === 'todo' ? 'bg-yellow-100 text-yellow-800' :
                  ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')
                ?>">
                  <?= ucfirst(str_replace('_', ' ', $task->status)) ?>
                </span>
                <div class="flex gap-2">
                  <a href="edittask.php?id=<?= $task->id ?>" 
                     class="text-blue-600 hover:underline text-xs">Edit</a>
                  <a href="deletetask.php?id=<?= $task->id ?>" 
                     class="text-red-600 hover:underline text-xs"
                     onclick="return confirm('Delete this task?')">Delete</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </main>
</div>
</body>
</html>