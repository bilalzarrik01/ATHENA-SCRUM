<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manager Dashboard - ScrumATHENA</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<div class="flex h-screen">

  <aside class="w-64 bg-black text-white p-6 hidden md:block">
    <div class="flex items-center gap-3 mb-10">
      <img src="image.png" alt="Logo" class="w-10 h-10 object-contain">
      <span class="text-xl font-bold">ScrumATHENA</span>
    </div>
    <nav class="space-y-4">
      <a href="#" class="block hover:text-green-400">Dashboard</a>
      <a href="#" class="block hover:text-green-400">Projects</a>
      <a href="#" class="block hover:text-green-400">Sprints</a>
      <a href="#" class="block hover:text-green-400">Tasks</a>
      <a href="#" class="block hover:text-green-400">Team</a>
      <a href="#" class="block hover:text-green-400">Notifications</a>
    </nav>
  </aside>

  <main class="flex-1 p-6 overflow-y-auto">
    <h2 class="text-3xl font-semibold mb-6">Manager Dashboard</h2>

    <!-- STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Projects</p>
        <h3 class="text-2xl font-bold">5</h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Sprints</p>
        <h3 class="text-2xl font-bold">12</h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Tasks</p>
        <h3 class="text-2xl font-bold">48</h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Completed</p>
        <h3 class="text-2xl font-bold text-green-600">32</h3>
      </div>
    </div>

    <!-- KANBAN BOARD -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gray-200 p-4 rounded">
        <h3 class="font-semibold mb-4">To Do</h3>
        <div class="bg-white p-4 rounded shadow mb-3 hover:scale-105 transition-transform">
          <div class="flex justify-between items-center mb-2">
            <h4 class="font-medium">Design login page</h4>
            <span class="text-xs bg-red-200 text-red-800 px-2 py-1 rounded">High</span>
          </div>
          <p class="text-sm text-gray-500">Assigned: Amina</p>
          <button class="mt-2 text-green-600 hover:underline">Mark Done</button>
        </div>
      </div>
    </div>
  </main>
</div>
</body>
</html>
