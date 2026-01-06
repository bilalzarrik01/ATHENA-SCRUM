<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';

// Check if user is manager
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'project_manager') {
    header("Location: index.php");
    exit;
}

$projectRepo = new ProjectRepository($pdo);
$message = $error = '';

// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the project
$project = $projectRepo->getById($projectId);

// Check if project exists and belongs to current manager
if (!$project) {
    header("Location: dashboardchef.php?error=Project+not+found");
    exit;
}

if ($project->created_by !== $_SESSION['user']['id']) {
    header("Location: dashboardchef.php?error=Unauthorized+access");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($title)) {
        $error = "Project title is required";
    } else {
        $success = $projectRepo->update($projectId, [
            'title' => $title,
            'description' => $description,
            'status' => $status
        ]);
        
        if ($success) {
            $message = "Project updated successfully!";
            // Refresh project data
            $project = $projectRepo->getById($projectId);
        } else {
            $error = "Failed to update project";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Project - ScrumATHENA</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6">Edit Project</h2>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($message): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700 mb-1">Project Title *</label>
            <input type="text" name="title" required 
                   value="<?= htmlspecialchars($project->title) ?>"
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" 
                      class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500"><?= htmlspecialchars($project->description ?? '') ?></textarea>
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500">
                <option value="active" <?= $project->status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="completed" <?= $project->status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="on_hold" <?= $project->status === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                <option value="archived" <?= $project->status === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
        
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Update Project
            </button>
            <a href="dashboardchef.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
</body>
</html>