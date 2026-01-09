<?php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';

// Check if user is manager
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'project_manager') {
    header("Location: index.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboardchef.php");
    exit;
}

$projectId = (int)$_GET['id'];
$projectRepo = new ProjectRepository($pdo);

// Confirm before deleting
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // Actually delete
    $success = $projectRepo->delete($projectId);
    
    if ($success) {
        // Success message
        echo "<script>
            alert('Project deleted successfully!');
            window.location.href = 'dashboardchef.php';
        </script>";
    } else {
        // Error message
        echo "<script>
            alert('Error deleting project!');
            window.location.href = 'dashboardchef.php';
        </script>";
    }
    exit;
} else {
    // Show confirmation page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirm Delete</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <div class="text-center mb-6">
                <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h2 class="text-xl font-bold text-gray-900 mt-4">Delete Project?</h2>
                <p class="text-gray-600 mt-2">Are you sure you want to delete this project? This action cannot be undone.</p>
            </div>
            
            <div class="flex justify-center space-x-4">
                <a href="dashboardchef.php" 
                   class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                    Cancel
                </a>
                <a href="deleteproject.php?id=<?= $projectId ?>&confirm=yes" 
                   class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Yes, Delete
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>