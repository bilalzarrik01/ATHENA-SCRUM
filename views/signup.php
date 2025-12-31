<?php
require_once __DIR__ . '/../config/db.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? '';

    $allowedRoles = ['admin', 'project_manager', 'member'];

    if (!$name || !$email || !$password || !$confirm || !$role) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email";
    } elseif (!in_array($role, $allowedRoles)) {
        $error = "Invalid role";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {

        // check email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email already exists";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $hashedPassword,
                $role
            ]);

            $success = "Account created successfully. You can login now.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - ScrumATHENA</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white shadow-lg rounded-lg flex flex-col md:flex-row w-full max-w-5xl overflow-hidden">

  <!-- LEFT -->
  <div class="hidden md:flex md:w-1/2 bg-black p-10 flex-col justify-center items-center">
    <img src="image.png" class="w-24 h-24 mb-6">
    <h2 class="text-white text-3xl font-bold mb-2">Join ScrumATHENA</h2>
    <p class="text-green-100 text-center">
      Manage your projects and collaborate efficiently.
    </p>
  </div>

  <!-- FORM -->
  <div class="w-full md:w-1/2 p-10">

    <h3 class="text-2xl font-semibold mb-4 text-gray-700">
      Create your account
    </h3>

    <!-- Messages -->
    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="grid grid-cols-1 gap-4">

      <input type="text" name="name" placeholder="Full Name" required
             class="w-full p-3 border rounded">

      <input type="email" name="email" placeholder="Email" required
             class="w-full p-3 border rounded">

      <!-- ROLE -->
      <select name="role" required
              class="w-full p-3 border rounded">
        <option value="">Select role</option>
        <option value="admin">Admin</option>
        <option value="project_manager">Project Manager</option>
        <option value="member">Team Member</option>
      </select>

      <input type="password" name="password" placeholder="Password" required
             class="w-full p-3 border rounded">

      <input type="password" name="confirm_password" placeholder="Confirm Password" required
             class="w-full p-3 border rounded">

      <button
        class="bg-gradient-to-r from-green-500 to-green-700 text-white py-3 rounded font-semibold hover:from-green-600 hover:to-green-800 transition">
        Create Account
      </button>
    </form>

    <p class="mt-6 text-center text-gray-500 text-sm">
      Already have an account?
      <a href="../views/index.php" class="text-green-600 font-semibold hover:underline">
        Sign In
      </a>
    </p>

  </div>
</div>

</body>
</html>
