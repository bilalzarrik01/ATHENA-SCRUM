<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../utils/Auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "All fields are required";
    } else {
        $authService = new AuthService($pdo);
        $user = $authService->login($email, $password); // **returns User object or null**

        if ($user instanceof User) {
            Auth::login($user); // User object passed

            // Redirect based on role
            switch ($user->role) {
                case 'admin':
                    header("Location: dashboardadmin.php");
                    break;
                case 'project_manager':
                    header("Location: dashboardchef.php");
                    break;
                default:
                    header("Location: dashboardmember.php");
            }
            exit;
        } else {
            $error = "Email or password incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - ScrumATHENA</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white shadow-lg rounded-lg flex flex-col md:flex-row w-full max-w-4xl overflow-hidden">

  <!-- LEFT IMAGE -->
  <div class="hidden md:flex md:w-1/2 bg-black p-10 flex-col justify-center items-center">
    <img src="image.png" class="w-24 h-24 mb-6">
    <h2 class="text-white text-3xl font-bold mb-2">Welcome Back!</h2>
    <p class="text-green-100 text-center">
      When worst comes to worst , squad comes first
    </p>
  </div>

  <!-- LOGIN FORM -->
  <div class="w-full md:w-1/2 p-10">
    <h3 class="text-2xl font-semibold mb-6 text-gray-700">
      Sign in to your account
    </h3>

    <!-- ERROR -->
    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="flex flex-col gap-4">
      <div>
        <label class="block text-gray-600 mb-1">Email</label>
        <input type="email" name="email" required
               class="w-full p-3 border rounded">
      </div>

      <div>
        <label class="block text-gray-600 mb-1">Password</label>
        <input type="password" name="password" required
               class="w-full p-3 border rounded">
      </div>

      <button type="submit"
        class="bg-gradient-to-r from-green-500 to-green-700 text-white py-3 rounded font-semibold hover:from-green-600 hover:to-green-800 transition">
        Sign In
      </button>
    </form>

    <p class="mt-6 text-center text-gray-500 text-sm">
      Don't have an account?
      <a href="signup.php" class="text-green-600 font-semibold hover:underline">
        Sign Up
      </a>
    </p>

  </div>
</div>

</body>
</html>
