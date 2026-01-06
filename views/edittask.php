<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

// Check if user is manager
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'project_manager') {
    header("Location: index.php");
    exit;
}

$taskRepo = new TaskRepository($pdo);
$sprintRepo = new SprintRepository($pdo);
$projectRepo = new ProjectRepository($pdo);
$userRepo = new UserRepository($pdo);

$message = $error = '';

// Get task ID from URL
$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the task
$task = $taskRepo->getById($taskId);

// Check if task exists
if (!$task) {
    header("Location: dashboardchef.php?error=Task+not+found");
    exit;
}

// Get the sprint and project to check ownership
$sprint = $sprintRepo->getById($task->sprint_id);
$project = $projectRepo->getById($sprint->project_id);
if (!$project || $project->created_by !== $_SESSION['user']['id']) {
    header("Location: dashboardchef.php?error=Unauthorized+access");
    exit;
}

// Get manager's projects and sprints
$projects = $projectRepo->getByManager($_SESSION['user']['id']);
$sprints = [];
foreach ($projects as $proj) {
    $projectSprints = $sprintRepo->getByProject($proj->id);
    $sprints = array_merge($sprints, $projectSprints);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sprint_id = $_POST['sprint_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $status = $_POST['status'] ?? 'todo';
    
    if (empty($sprint_id) || empty($title)) {
        $error = "Sprint and title are required";
    } else {
        try {
            $success = $taskRepo->update($taskId, [
                'title' => $title,
                'description' => $description,
                'priority' => $priority,
                'status' => $status
            ]);
            
            if ($success) {
                $message = "Task updated successfully!";
                // Refresh task data
                $task = $taskRepo->getById($taskId);
            } else {
                $error = "Failed to update task";
            }
            
        } catch (Exception $e) {
            $error = "Error updating task: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Task - ScrumATHENA</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6">Edit Task</h2>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($message): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700 mb-1">Select Sprint *</label>
            <select name="sprint_id" required 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Choose a sprint</option>
                <?php foreach ($sprints as $sprintOption): ?>
                    <option value="<?= $sprintOption->id ?>" 
                        <?= $sprintOption->id == $task->sprint_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sprintOption->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Task Title *</label>
            <input type="text" name="title" required 
                   value="<?= htmlspecialchars($task->title) ?>"
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                   placeholder="e.g., Implement login form">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" 
                      class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                      placeholder="Task details..."><?= htmlspecialchars($task->description ?? '') ?></textarea>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1">Priority</label>
                <select name="priority" 
                        class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="low" <?= $task->priority === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $task->priority === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $task->priority === 'high' ? 'selected' : '' ?>>High</option>
                </select>
            </div>
            
            <div>
                <label class="block text-gray-700 mb-1">Status</label>
                <select name="status" 
                        class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="todo" <?= $task->status === 'todo' ? 'selected' : '' ?>>To Do</option>
                    <option value="in_progress" <?= $task->status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="done" <?= $task->status === 'done' ? 'selected' : '' ?>>Done</option>
                </select>
            </div>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" 
                    class="flex-1 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 font-medium transition duration-200">
                Update Task
            </button>
            <a href="dashboardchef.php" 
               class="flex-1 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 font-medium text-center transition duration-200">
                Cancel
            </a>
        </div>
    </form>
</div>
</body>
</html>