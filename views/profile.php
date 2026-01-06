<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$userRepo = new UserRepository($pdo);
$message = $error = '';

// Get current user data
$userData = $userRepo->getById($userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Basic validation
    if (empty($name) || empty($email)) {
        $error = "Name and email are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists (excluding current user)
        $existingUser = $userRepo->getByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            $error = "Email already exists";
        } else {
            $updateData = [
                'name' => $name,
                'email' => $email,
                'role' => $userData['role'] // Keep existing role
            ];
            
            // Update password if provided
            if (!empty($currentPassword) && !empty($newPassword)) {
                if (!password_verify($currentPassword, $userData['password'])) {
                    $error = "Current password is incorrect";
                } elseif ($newPassword !== $confirmPassword) {
                    $error = "New passwords do not match";
                } elseif (strlen($newPassword) < 6) {
                    $error = "New password must be at least 6 characters";
                } else {
                    $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
            }
            
            if (!$error) {
                $success = $userRepo->update($userId, $updateData);
                if ($success) {
                    $message = "Profile updated successfully!";
                    // Update session
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['email'] = $email;
                    // Refresh user data
                    $userData = $userRepo->getById($userId);
                } else {
                    $error = "Failed to update profile";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ScrumATHENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">My Profile</h1>
                <span class="bg-green-800 px-3 py-1 rounded-full text-sm"><?= ucfirst($_SESSION['user']['role']) ?></span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="dashboardmember.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded">Dashboard</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-4xl">
        <!-- Header -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h1 class="text-2xl font-bold">My Profile</h1>
            <p class="text-gray-600">Update your personal information and password</p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Profile Form -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <form method="POST" class="space-y-6">
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Full Name *</label>
                                <input type="text" name="name" required 
                                       value="<?= htmlspecialchars($userData['name'] ?? '') ?>"
                                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Email Address *</label>
                                <input type="email" name="email" required 
                                       value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Role</label>
                                <input type="text" value="<?= ucfirst($userData['role'] ?? 'member') ?>" 
                                       class="w-full p-3 border rounded-lg bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Member Since</label>
                                <input type="text" value="<?= date('M d, Y', strtotime($userData['created_at'] ?? 'now')) ?>" 
                                       class="w-full p-3 border rounded-lg bg-gray-50" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="pt-6 border-t">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                        <p class="text-gray-600 mb-4">Leave blank if you don't want to change your password</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Current Password</label>
                                <input type="password" name="current_password" 
                                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">New Password</label>
                                <input type="password" name="new_password" 
                                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Confirm New Password</label>
                                <input type="password" name="confirm_password" 
                                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-6 border-t flex justify-end space-x-3">
                        <a href="dashboard-member.php" 
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">User ID</span>
                        <span class="font-medium">#<?= $userData['id'] ?? 'N/A' ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Account Status</span>
                        <span class="px-3 py-1 rounded-full text-sm <?= ($userData['active'] ?? 1) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= ($userData['active'] ?? 1) ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Last Updated</span>
                        <span class="font-medium"><?= date('M d, Y H:i', strtotime($userData['updated_at'] ?? 'now')) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>