<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($taskId <= 0) {
    header("Location: dashboardmember.php?error=Invalid+task+ID");
    exit;
}

$taskRepo = new TaskRepository($pdo);
$task = $taskRepo->getById($taskId);

if (!$task) {
    header("Location: dashboardmember.php?error=Task+not+found");
    exit;
}

$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    
    if (empty($newStatus)) {
        $error = "Please select a status";
    } else {
        try {
            $success = $taskRepo->updateStatus($taskId, $newStatus);
            if ($success) {
                $message = "Task status updated successfully!";
                $task = $taskRepo->getById($taskId); // Refresh task data
            } else {
                $error = "Failed to update task status";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Task Status - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Update Task Status</h2>
                <p class="text-gray-600 mb-6">Update the status for: <strong><?= htmlspecialchars($task->title) ?></strong></p>
                
                <?php if ($message): ?>
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Current Status</label>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <span class="px-3 py-1 rounded-full text-sm <?= 
                                $task->status === 'todo' ? 'bg-gray-100 text-gray-800' :
                                ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                'bg-green-100 text-green-800')
                            ?>">
                                <?= ucfirst(str_replace('_', ' ', $task->status)) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">New Status *</label>
                        <select name="status" required class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Select new status</option>
                            <option value="todo" <?= $task->status === 'todo' ? 'selected' : '' ?>>To Do</option>
                            <option value="in_progress" <?= $task->status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="done" <?= $task->status === 'done' ? 'selected' : '' ?>>Done</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 bg-green-500 text-white p-3 rounded-lg hover:bg-green-600">
                            Update Status
                        </button>
                        <a href="dashboardmember.php" class="flex-1 bg-gray-500 text-white p-3 rounded-lg hover:bg-gray-600 text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>