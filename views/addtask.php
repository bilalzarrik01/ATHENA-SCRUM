<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

// Check if user is manager
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'project_manager') {
    header("Location: index.php");
    exit;
}

$projectRepo = new ProjectRepository($pdo);
$sprintRepo = new SprintRepository($pdo);
$taskRepo = new TaskRepository($pdo);

// Get manager's projects
$projects = $projectRepo->getByManager($_SESSION['user']['id']);

// Get sprints for these projects
$sprints = [];
foreach ($projects as $project) {
    $projectSprints = $sprintRepo->getByProject($project->id);
    $sprints = array_merge($sprints, $projectSprints);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sprint_id = $_POST['sprint_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $assigned_to = $_POST['assigned_to'] ?? null;
    
    if (empty($sprint_id) || empty($title)) {
        $error = "Sprint and title are required";
    } else {
        try {
            // Create task data array
            $taskData = [
                'sprint_id' => $sprint_id,
                'title' => $title,
                'description' => $description,
                'priority' => $priority,
                'status' => 'todo'
            ];
            
            // If user assignment is provided, add it to the data
            if ($assigned_to) {
                $taskData['assigned_to'] = $assigned_to;
            }
            
            // Create task
            $task = $taskRepo->create($taskData);
            
            // Redirect after successful creation
            header("Location: dashboardchef.php?success=Task+created+successfully");
            exit;
            
        } catch (Exception $e) {
            $error = "Error creating task: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Task - ScrumATHENA</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6">Add New Task</h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($sprints)): ?>
        <div class="bg-yellow-100 text-yellow-700 p-4 rounded mb-4">
            <p class="font-semibold">No sprints available!</p>
            <p class="mt-2">You need to:</p>
            <ol class="list-decimal ml-5 mt-1">
                <li>Create a project</li>
                <li>Create a sprint in that project</li>
                <li>Then you can add tasks</li>
            </ol>
            <div class="mt-3 flex gap-2">
                <a href="addproject.php" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                    Create Project
                </a>
                <a href="addsprint.php" class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600">
                    Create Sprint
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700 mb-1">Select Sprint *</label>
            <select name="sprint_id" required 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    <?= empty($sprints) ? 'disabled' : '' ?>>
                <option value="">Choose a sprint</option>
                <?php foreach ($sprints as $sprint): ?>
                    <option value="<?= $sprint->id ?>">
                        <?= htmlspecialchars($sprint->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($sprints)): ?>
                <p class="text-sm text-red-500 mt-1">No sprints available. Create one first.</p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Task Title *</label>
            <input type="text" name="title" required 
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                   placeholder="e.g., Implement login form"
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" 
                      class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                      placeholder="Task details..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Priority</label>
            <select name="priority" 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="low" <?= ($_POST['priority'] ?? 'medium') === 'low' ? 'selected' : '' ?>>Low</option>
                <option value="medium" <?= ($_POST['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                <option value="high" <?= ($_POST['priority'] ?? 'medium') === 'high' ? 'selected' : '' ?>>High</option>
            </select>
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Assign to (Optional)</label>
            <select name="assigned_to" 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Unassigned</option>
                <?php
                // Get members directly from database since UserRepository might not have getAll()
                try {
                    $stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE role = 'member'");
                    $members = $stmt->fetchAll(PDO::FETCH_OBJ);
                    
                    foreach ($members as $member):
                ?>
                    <option value="<?= $member->id ?>" <?= (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $member->id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($member->name) ?> (<?= $member->email ?>)
                    </option>
                <?php 
                    endforeach;
                } catch (Exception $e) {
                    echo '<option value="">Error loading members</option>';
                }
                ?>
            </select>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" 
                    class="flex-1 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 font-medium transition duration-200 <?= empty($sprints) ? 'opacity-50 cursor-not-allowed' : '' ?>"
                    <?= empty($sprints) ? 'disabled' : '' ?>>
                Create Task
            </button>
            <a href="dashboardchef.php" 
               class="flex-1 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 font-medium text-center transition duration-200">
                Cancel
            </a>
        </div>
    </form>
    
    <!-- Debug info -->
    <div class="mt-8 pt-4 border-t text-sm text-gray-500">
        <p><strong>Debug Info:</strong></p>
        <p>Projects: <?= count($projects) ?></p>
        <p>Sprints: <?= count($sprints) ?></p>
        <?php if (!empty($sprints)): ?>
            <p>Available sprints:</p>
            <ul class="ml-4">
                <?php foreach ($sprints as $sprint): ?>
                    <li>ID: <?= $sprint->id ?> - <?= htmlspecialchars($sprint->name) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
</body>
</html>