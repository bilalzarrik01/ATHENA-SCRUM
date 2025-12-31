<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Dashboard - ScrumATHENA</title>
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
      <a href="#" class="block hover:text-green-400">Notifications</a>
    </nav>
  </aside>

  <main class="flex-1 p-6 overflow-y-auto">
    <h2 class="text-3xl font-semibold mb-6">Member Dashboard</h2>

    <!-- KANBAN BOARD -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gray-200 p-4 rounded">
        <h3 class="font-semibold mb-4">To Do</h3>
        <div class="bg-white p-4 rounded shadow mb-3 hover:scale-105 transition-transform">
          <h4 class="font-medium mb-2">API Authentication</h4>
          <p class="text-sm text-gray-500 mb-1">Due: 28 Dec 2025</p>
          <div class="w-full bg-gray-200 rounded h-2 mb-2">
            <div class="bg-green-500 h-2 rounded" style="width:50%"></div>
          </div>
          <button class="text-green-600 hover:underline text-sm">Mark Done</button>
          <p class="text-xs text-gray-400 mt-2">2 comments</p>
        </div>
      </div>
    </div>
  </main>
</div>
</body>
</html>
