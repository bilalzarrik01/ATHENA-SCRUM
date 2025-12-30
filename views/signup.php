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

    <!-- LEFT SECTION -->
    <div class="hidden md:flex md:w-1/2 bg-black p-10 flex-col justify-center items-center">
      <img src="image.png" alt="ScrumATHENA Logo" class="w-24 h-24 mb-6">
      <h2 class="text-white text-3xl font-bold mb-2">Join ScrumATHENA</h2>
      <p class="text-green-100 text-center">
        Create your account and start managing projects with your team efficiently.
      </p>
    </div>

    <!-- SIGN UP FORM -->
    <div class="w-full md:w-1/2 p-10 flex flex-col justify-center">

      <!-- Mobile Logo -->
      <div class="mb-6 text-center md:hidden">
        <img src="image.png" alt="ScrumATHENA Logo" class="w-20 h-20 mx-auto mb-2">
        <h2 class="text-2xl font-bold">ScrumATHENA</h2>
      </div>

      <h3 class="text-2xl font-semibold mb-6 text-gray-700">Create your account</h3>

      <form action="#" method="POST" class="grid grid-cols-1 gap-4">

        <!-- Full Name -->
        <div>
          <label class="block text-gray-600 mb-1">Full Name</label>
          <input type="text" name="name" placeholder="John Doe"
                 class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Email -->
        <div>
          <label class="block text-gray-600 mb-1">Email</label>
          <input type="email" name="email" placeholder="you@example.com"
                 class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Role -->
        <div>
          <label class="block text-gray-600 mb-1">Role</label>
          <select name="role"
                  class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="member">Team Member</option>
            <option value="manager">Project Manager</option>
          </select>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-gray-600 mb-1">Password</label>
          <input type="password" name="password" placeholder="********"
                 class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block text-gray-600 mb-1">Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="********"
                 class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Terms -->
        <div class="flex items-center gap-2 text-gray-600">
          <input type="checkbox" required class="form-checkbox h-4 w-4 text-green-500">
          <span>
            I agree to the
            <a href="#" class="text-green-600 hover:underline">Terms & Conditions</a>
          </span>
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="bg-gradient-to-r from-green-500 to-green-700 text-white px-4 py-3 rounded shadow hover:from-green-600 hover:to-green-800 transition font-semibold mt-2">
          Create Account
        </button>

      </form>

      <!-- Login link -->
      <p class="mt-6 text-center text-gray-500 text-sm">
        Already have an account?
        <a href="login.html" class="text-green-600 hover:underline font-semibold">Sign In</a>
      </p>

    </div>
  </div>

</body>
</html>
