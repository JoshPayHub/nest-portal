<template>
  <div class="p-6">
    <!-- Header Section -->
    <div class="w-full bg-white grid place-items-center h-[340px] rounded-2xl shadow-sm border border-blue-100">
      <div class="text-center">
        <h1 class="font-bold text-brand-blue text-3xl mb-2">Admin Control Center</h1>
        <p class="text-gray-500 text-lg">Overview of system activities, performance, and user management.</p>
      </div>
    </div>

    <!-- Overview Cards -->
    <div class="pt-7 grid grid-cols-12 gap-7">
      <!-- Card 1 -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">User Management Overview</h2>
        <div class="grid grid-cols-3 gap-3 text-center">
          <div>
            <h3 class="text-3xl font-bold text-brand-blue">124</h3>
            <p class="text-gray-500 text-sm">Active Users</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-green-500">12</h3>
            <p class="text-gray-500 text-sm">New This Week</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-red-500">5</h3>
            <p class="text-gray-500 text-sm">Suspended</p>
          </div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Recent System Logs</h2>
        <ul class="text-gray-600 text-sm space-y-2">
          <li>🕒 User <span class="font-medium text-brand-blue">John D.</span> logged in (2 mins ago)</li>
          <li>⚙️ System settings updated by <span class="font-medium text-brand-blue">Admin</span></li>
          <li>📊 Report generated for <span class="font-medium text-brand-blue">Finance</span></li>
          <li>🚫 User <span class="font-medium text-red-500">Mike T.</span> suspended</li>
          <li>🔔 New audit log created (5 hrs ago)</li>
        </ul>
      </div>

      <!-- Card 3 -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Reports & Analytics Summary</h2>
        <div class="flex justify-around text-center">
          <div>
            <h3 class="text-3xl font-bold text-brand-blue">23</h3>
            <p class="text-gray-500 text-sm">Reports This Week</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-green-500">8</h3>
            <p class="text-gray-500 text-sm">Performance Alerts</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-yellow-500">3</h3>
            <p class="text-gray-500 text-sm">Pending Reviews</p>
          </div>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">System Settings Snapshot</h2>
        <ul class="text-gray-600 text-sm space-y-2">
          <li>✅ 2FA Security: <span class="font-medium text-green-600">Enabled</span></li>
          <li>🌐 Server Status: <span class="font-medium text-green-600">Online</span></li>
          <li>📦 Last Backup: <span class="font-medium text-brand-blue">2 hours ago</span></li>
          <li>⚙️ Version: <span class="font-medium text-gray-700">v3.4.8</span></li>
          <li>🧩 Maintenance Mode: <span class="font-medium text-red-600">Off</span></li>
        </ul>
      </div>
    </div>

    <!-- Footer Widgets -->
    <div class="pt-7 grid grid-cols-12 gap-7">
      <!-- Audit Section -->
      <div class="col-span-8 bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Audit Trail Overview</h2>
        <table class="w-full text-sm text-left text-gray-600 border-t border-gray-100">
          <thead class="text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-2 px-3">Date</th>
              <th class="py-2 px-3">User</th>
              <th class="py-2 px-3">Action</th>
              <th class="py-2 px-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b hover:bg-gray-50">
              <td class="py-2 px-3">Nov 12, 2025</td>
              <td class="py-2 px-3">Admin</td>
              <td class="py-2 px-3">Updated Role Permissions</td>
              <td class="py-2 px-3 text-green-600 font-medium">Success</td>
            </tr>
            <tr class="border-b hover:bg-gray-50">
              <td class="py-2 px-3">Nov 11, 2025</td>
              <td class="py-2 px-3">Manager</td>
              <td class="py-2 px-3">Edited Department Data</td>
              <td class="py-2 px-3 text-green-600 font-medium">Success</td>
            </tr>
            <tr class="border-b hover:bg-gray-50">
              <td class="py-2 px-3">Nov 10, 2025</td>
              <td class="py-2 px-3">System</td>
              <td class="py-2 px-3">Auto Backup Triggered</td>
              <td class="py-2 px-3 text-blue-600 font-medium">Automated</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Quick Actions -->
      <div class="col-span-4 bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Quick Actions</h2>
        <div class="flex flex-col gap-3">
          <button class="bg-brand-blue text-white py-2 rounded-md hover:bg-blue-700 transition">Add New User</button>
          <button class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition">Generate Report</button>
          <button class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition">System Maintenance</button>
        </div>
      </div>
    </div>
  </div>
</template>
