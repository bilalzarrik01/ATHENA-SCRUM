<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';
require_once __DIR__ . '/../repositories/NotificationRepository.php';

// Check if user is admin or manager
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'project_manager'])) {
    header("Location: index.php");
    exit;
}

$projectRepo = new ProjectRepository($pdo);
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title)) {
        $error = "Project title is required";
    } else {
        $projectRepo->create([
            'title' => $title,
            'description' => $description,
            'created_by' => $_SESSION['user']['id'],
            'status' => 'active'
        ]);
        $message = "Project created successfully!";
        $notificationRepo = new NotificationRepository($pdo);

$notificationRepo->create(
    $_SESSION['user']['id'],
    "You created a new project: $title"
);

        
        // Redirect based on role
        if ($_SESSION['user']['role'] === 'admin') {
            header("Location: dashboardadmin.php");
        } else {
            header("Location: dashboardchef.php");
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Project - ScrumATHENA</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6">Add New Project</h2>
    
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
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" 
                      class="w-full p-2 border rounded focus:ring-2 focus:ring-green-500"></textarea>
        </div>
        
        <div class="flex gap-3">
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Create Project
            </button>
            <a href="dashboardchef.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
</body>
</html>