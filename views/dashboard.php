<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ScrumATHENA Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<!-- MOBILE HEADER -->
<div class="md:hidden flex justify-between items-center p-4 bg-black text-white">
  <span class="text-xl font-bold">ScrumATHENA</span>
  <div class="flex items-center gap-4">
    <!-- Notifications -->
    <div class="relative">
      <button id="notif-btn" class="focus:outline-none relative">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 00-5-5.917V4a1 1 0 10-2 0v1.083A6 6 0 006 11v3c0 .386-.149.735-.395 1.001L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1 rounded-full">3</span>
      </button>
      <div id="notif-menu" class="hidden absolute right-0 mt-2 w-60 bg-white rounded shadow-lg p-2">
        <p class="text-gray-700 text-sm mb-1">New comment on task "API Auth"</p>
        <p class="text-gray-700 text-sm mb-1">Task "Database Schema" completed</p>
        <p class="text-gray-700 text-sm">New task assigned: "Design login"</p>
      </div>
    </div>

    <button id="menu-btn" class="focus:outline-none">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </div>
</div>

<div class="flex h-screen">

  <!-- SIDEBAR -->
  <aside class="w-64 bg-black text-white p-6 hidden md:block transition-transform duration-300" id="sidebar">
    <div class="flex items-center gap-3 mb-10">
      <img src="image.png" alt="Logo" class="w-10 h-10 object-contain">
      <span class="text-xl font-bold">ScrumATHENA</span>
    </div>

    <nav class="space-y-4">
      <a href="#" class="block hover:text-green-400 relative">Dashboard</a>
      <a href="#" class="block hover:text-green-400 relative">
        Projects
        <span class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 rounded-full">3</span>
      </a>
      <a href="#" class="block hover:text-green-400">Sprints</a>
      <a href="#" class="block hover:text-green-400">Tasks</a>
      <a href="#" class="block hover:text-green-400">Profile</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="flex-1 p-6 overflow-y-auto">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <h2 class="text-3xl font-semibold">Dashboard</h2>
      <button class="bg-gradient-to-r from-green-500 to-green-700 text-white px-4 py-2 rounded shadow hover:from-green-600 hover:to-green-800 transition">
        + New Task
      </button>
    </div>

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

    <!-- SEARCH & FILTER -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
      <input type="text" placeholder="Search tasks..." class="flex-1 p-2 rounded border border-gray-300">
      <select class="p-2 rounded border border-gray-300">
        <option>All Status</option>
        <option>To Do</option>
        <option>In Progress</option>
        <option>Done</option>
      </select>
      <select class="p-2 rounded border border-gray-300">
        <option>All Members</option>
        <option>Bilal</option>
        <option>Amina</option>
      </select>
    </div>

    <!-- PROJECT LIST -->
    <div class="mb-8">
      <h3 class="text-xl font-semibold mb-4">Projects</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-4 rounded shadow hover:shadow-lg transition">
          <h4 class="font-medium text-lg mb-2">Project Alpha</h4>
          <p class="text-sm text-gray-500 mb-2">Status: <span class="text-green-600">Active</span></p>
          <p class="text-sm text-gray-500 mb-2">Assigned to: Bilal</p>
          <p class="text-sm text-gray-400 mb-4">Sprint: 2 | Tasks: 8</p>
          <div class="flex gap-2">
            <button class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Edit</button>
            <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- SCRUM BOARD -->
    <div>
      <h3 class="text-xl font-semibold mb-4">Scrum Board</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- TODO -->
        <div class="bg-gray-200 p-4 rounded">
          <h3 class="font-semibold mb-4">To Do</h3>
          <div class="bg-white p-4 rounded shadow mb-3 hover:scale-105 transition-transform">
            <div class="flex justify-between items-center mb-2">
              <h4 class="font-medium">Design login page</h4>
              <span class="text-xs bg-red-200 text-red-800 px-2 py-1 rounded">High</span>
            </div>
            <div class="flex items-center gap-2 mb-2">
              <img src="https://i.pravatar.cc/32?img=1" alt="user" class="w-8 h-8 rounded-full">
              <p class="text-sm text-gray-500">Amina</p>
            </div>
            <p class="text-sm text-gray-400 mb-2">Due: 31 Dec 2025</p>
            <div class="flex justify-between items-center">
              <div class="w-full bg-gray-200 rounded h-2 mr-2">
                <div class="bg-green-500 h-2 rounded" style="width:0%"></div>
              </div>
              <button class="text-green-600 hover:underline text-sm">Mark Done</button>
            </div>
          </div>
        </div>

        <!-- IN PROGRESS -->
        <div class="bg-gray-200 p-4 rounded">
          <h3 class="font-semibold mb-4">In Progress</h3>
          <div class="bg-white p-4 rounded shadow mb-3 hover:scale-105 transition-transform">
            <div class="flex justify-between items-center mb-2">
              <h4 class="font-medium">API authentication</h4>
              <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded">Medium</span>
            </div>
            <div class="flex items-center gap-2 mb-2">
              <img src="https://i.pravatar.cc/32?img=2" alt="user" class="w-8 h-8 rounded-full">
              <p class="text-sm text-gray-500">Bilal</p>
            </div>
            <p class="text-sm text-gray-400 mb-2">Due: 28 Dec 2025</p>
            <div class="flex justify-between items-center">
              <div class="w-full bg-gray-200 rounded h-2 mr-2">
                <div class="bg-yellow-500 h-2 rounded" style="width:50%"></div>
              </div>
              <button class="text-green-600 hover:underline text-sm">Mark Done</button>
            </div>
            <p class="text-xs text-gray-400 mt-2">2 comments</p>
          </div>
        </div>

        <!-- DONE -->
        <div class="bg-gray-200 p-4 rounded">
          <h3 class="font-semibold mb-4">Done</h3>
          <div class="bg-white p-4 rounded shadow mb-3 border-l-4 border-green-500 hover:scale-105 transition-transform">
            <h4 class="font-medium line-through">Database schema</h4>
            <div class="flex items-center gap-2 mt-1">
              <img src="https://i.pravatar.cc/32?img=3" alt="user" class="w-8 h-8 rounded-full">
              <p class="text-sm text-gray-500">Amina</p>
            </div>
            <p class="text-xs text-gray-400 mt-2">1 comment</p>
          </div>
        </div>

      </div>
    </div>

  </main>
</div>

<!-- SCRIPTS -->
<script>
  // Toggle sidebar
  const btn = document.getElementById('menu-btn');
  const sidebar = document.getElementById('sidebar');
  btn.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
  });

  // Toggle notifications dropdown
  const notifBtn = document.getElementById('notif-btn');
  const notifMenu = document.getElementById('notif-menu');
  notifBtn.addEventListener('click', () => {
    notifMenu.classList.toggle('hidden');
  });
</script>

</body>
</html>
