 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - ScrumATHENA</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

  <div class="bg-white shadow-lg rounded-lg flex flex-col md:flex-row w-full max-w-4xl overflow-hidden">
    
    <!-- LEFT IMAGE / ILLUSTRATION -->
    <div class="hidden md:block md:w-1/2 bg-black p-10 flex flex-col justify-center items-center">
      <img src="image.png" alt="ScrumATHENA Logo" class="w-24 h-24 mb-6">
      <h2 class="text-white text-3xl font-bold mb-2">Welcome Back!</h2>
      <p class="text-green-100 text-center">Manage your projects and teams efficiently with ScrumATHENA</p>
    </div>

    <!-- LOGIN FORM -->
    <div class="w-full md:w-1/2 p-10 flex flex-col justify-center">
      <div class="mb-6 text-center md:hidden">
        <img src="image.png" alt="ScrumATHENA Logo" class="w-20 h-20 mx-auto mb-2">
        <h2 class="text-2xl font-bold">ScrumATHENA</h2>
      </div>

      <h3 class="text-2xl font-semibold mb-6 text-gray-700">Sign in to your account</h3>

      <form action="#" method="POST" class="flex flex-col gap-4">
        <!-- Email -->
        <div>
          <label class="block text-gray-600 mb-1" for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com"
                 class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Password -->
        <div>
          <label class="block text-gray-600 mb-1" for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="********"
                 class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Remember me -->
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-gray-600">
            <input type="checkbox" class="form-checkbox h-4 w-4 text-green-500">
            Remember me
          </label>
          <a href="#" class="text-green-600 hover:underline text-sm">Forgot password?</a>
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="bg-gradient-to-r from-green-500 to-green-700 text-white px-4 py-3 rounded shadow hover:from-green-600 hover:to-green-800 transition font-semibold">
          Sign In
        </button>
      </form>

      <!-- Signup Link -->
      <p class="mt-6 text-center text-gray-500 text-sm">
        Don't have an account?
        <a href="#" class="text-green-600 hover:underline font-semibold">Sign Up</a>
      </p>
    </div>

  </div>

</body>
</html>
