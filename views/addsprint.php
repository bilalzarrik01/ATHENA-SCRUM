<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';
require_once __DIR__ . '/../repositories/SprintRepository.php';

// Check if user is manager
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'project_manager') {
    header("Location: index.php");
    exit;
}

$projectRepo = new ProjectRepository($pdo);
$sprintRepo = new SprintRepository($pdo);

// Get manager's projects for dropdown
$projects = $projectRepo->getByManager($_SESSION['user']['id']);
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    if (empty($project_id) || empty($name) || empty($start_date) || empty($end_date)) {
        $error = "All fields are required";
    } elseif ($end_date < $start_date) {
        $error = "End date must be after start date";
    } else {
        $sprintRepo->create([
            'project_id' => $project_id,
            'name' => $name,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
        $message = "Sprint created successfully!";
        
        // Redirect after successful creation
        header("Location: dashboardchef.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Sprint - ScrumATHENA</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6">Add New Sprint</h2>
    
    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700 mb-1">Select Project *</label>
            <select name="project_id" required class="w-full p-2 border rounded">
                <option value="">Choose a project</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= $project->id ?>"><?= htmlspecialchars($project->title) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-gray-700 mb-1">Sprint Name *</label>
            <input type="text" name="name" required 
                   class="w-full p-2 border rounded" 
                   placeholder="e.g., Sprint 1 - Authentication">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1">Start Date *</label>
                <input type="date" name="start_date" required 
                       class="w-full p-2 border rounded">
            </div>
            
            <div>
                <label class="block text-gray-700 mb-1">End Date *</label>
                <input type="date" name="end_date" required 
                       class="w-full p-2 border rounded">
            </div>
        </div>
        
        <div class="flex gap-3">
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Create Sprint
            </button>
            <a href="dashboardchef.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
</body>
</html>