<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - ScrumATHENA</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<div class="flex h-screen">

  <!-- SIDEBAR -->
  <aside class="w-64 bg-black text-white p-6 hidden md:block">
    <div class="flex items-center gap-3 mb-10">
      <img src="image.png" alt="Logo" class="w-10 h-10 object-contain">
      <span class="text-xl font-bold">ScrumATHENA</span>
    </div>
    <nav class="space-y-4">
      <a href="#" class="block hover:text-green-400">Dashboard</a>
      <a href="#" class="block hover:text-green-400">Users</a>
      <a href="#" class="block hover:text-green-400">Projects</a>
      <a href="#" class="block hover:text-green-400">Sprints</a>
      <a href="#" class="block hover:text-green-400">Tasks</a>
      <a href="#" class="block hover:text-green-400">Reports</a>
      <a href="#" class="block hover:text-green-400">Settings</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="flex-1 p-6 overflow-y-auto">
    <h2 class="text-3xl font-semibold mb-6">Admin Dashboard</h2>

    <!-- STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Total Users</p>
        <h3 class="text-2xl font-bold">25</h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Projects</p>
        <h3 class="text-2xl font-bold">12</h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Sprints</p>
        <h3 class="text-2xl font-bold">24</h3>
      </div>
      <div class="bg-white p-6 rounded shadow">
        <p class="text-gray-500">Tasks Completed</p>
        <h3 class="text-2xl font-bold text-green-600">110</h3>
      </div>
    </div>

    <!-- USERS TABLE -->
    <div class="bg-white p-6 rounded shadow mb-8">
      <h3 class="text-xl font-semibold mb-4">User Management</h3>
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-3">Name</th>
            <th class="p-3">Email</th>
            <th class="p-3">Role</th>
            <th class="p-3">Status</th>
            <th class="p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-t">
            <td class="p-3">Bilal Zarrik</td>
            <td class="p-3">bilal@example.com</td>
            <td class="p-3">Admin</td>
            <td class="p-3"><span class="text-green-600 font-semibold">Active</span></td>
            <td class="p-3 flex gap-2">
              <button class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Edit</button>
              <button class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Deactivate</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- PROJECTS OVERVIEW -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white p-4 rounded shadow hover:shadow-lg transition">
        <h4 class="font-medium mb-2">Project Alpha</h4>
        <p class="text-sm text-gray-500 mb-1">Manager: Bilal</p>
        <p class="text-sm text-gray-500 mb-2">Tasks: 8 | Sprint: 2</p>
        <div class="flex gap-2">
          <button class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Edit</button>
          <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
        </div>
      </div>
    </div>

  </main>
</div>
</body>
</html>
