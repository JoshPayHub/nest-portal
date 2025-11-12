<template>
  <div class="p-6">
    <!-- Header -->
    <div class="w-full bg-white grid place-items-center h-[340px] rounded-2xl shadow-sm border border-blue-100">
      <div class="text-center">
        <h1 class="font-bold text-brand-blue text-3xl mb-2">Supervisor Dashboard</h1>
        <p class="text-gray-500 text-lg">Monitor your team's performance, attendance, and reports.</p>
      </div>
    </div>

    <!-- Main Cards -->
    <div class="pt-7 grid grid-cols-12 gap-7">
      <!-- Team Task Progress -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Team Task Progress</h2>
        <div class="space-y-3">
          <div class="flex justify-between text-sm">
            <span>Website Redesign</span>
            <span class="text-brand-blue font-semibold">80%</span>
          </div>
          <div class="w-full h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-brand-blue rounded-full" style="width: 80%"></div>
          </div>

          <div class="flex justify-between text-sm">
            <span>Content Updates</span>
            <span class="text-brand-blue font-semibold">60%</span>
          </div>
          <div class="w-full h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-yellow-400 rounded-full" style="width: 60%"></div>
          </div>

          <div class="flex justify-between text-sm">
            <span>Bug Fixes</span>
            <span class="text-brand-blue font-semibold">45%</span>
          </div>
          <div class="w-full h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-red-400 rounded-full" style="width: 45%"></div>
          </div>
        </div>
      </div>

      <!-- Attendance Overview -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Attendance Overview</h2>
        <div class="flex justify-around text-center">
          <div>
            <h3 class="text-3xl font-bold text-green-500">95%</h3>
            <p class="text-gray-500 text-sm">Present</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-yellow-500">3%</h3>
            <p class="text-gray-500 text-sm">Late</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-red-500">2%</h3>
            <p class="text-gray-500 text-sm">Absent</p>
          </div>
        </div>
      </div>

      <!-- Reports Summary -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Reports Summary</h2>
        <ul class="text-gray-600 text-sm space-y-2">
          <li>📊 Weekly Task Report - <span class="font-medium text-green-600">Submitted</span></li>
          <li>📝 Performance Feedback - <span class="font-medium text-yellow-600">Pending Review</span></li>
          <li>📈 Monthly Metrics - <span class="font-medium text-brand-blue">In Progress</span></li>
          <li>📋 Incident Log - <span class="font-medium text-green-600">Completed</span></li>
          <li>⚙️ Maintenance Report - <span class="font-medium text-red-600">Overdue</span></li>
        </ul>
      </div>

      <!-- Announcements -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Announcements</h2>
        <ul class="text-gray-600 text-sm space-y-2">
          <li>📢 <span class="font-medium text-brand-blue">Team Meeting</span> — Nov 13, 2:00 PM</li>
          <li>🎉 <span class="font-medium text-green-600">Performance Bonus</span> to be released next week</li>
          <li>⚙️ System Maintenance on Nov 15, 10 PM</li>
          <li>📅 Reminder: Submit Weekly Report by Friday</li>
        </ul>
      </div>
    </div>

    <!-- Bottom Section -->
    <div class="pt-7 grid grid-cols-12 gap-7">
      <!-- Team Overview -->
      <div class="col-span-8 bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Team Overview</h2>
        <table class="w-full text-sm text-gray-600">
          <thead class="bg-blue-50 text-brand-blue font-semibold">
            <tr>
              <th class="p-2 text-left rounded-l-lg">Name</th>
              <th class="p-2 text-left">Role</th>
              <th class="p-2 text-left">Status</th>
              <th class="p-2 text-left rounded-r-lg">Tasks</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b">
              <td class="p-2 font-medium">Maria Santos</td>
              <td class="p-2">Frontend Dev</td>
              <td class="p-2 text-green-600 font-medium">Active</td>
              <td class="p-2">8 / 10</td>
            </tr>
            <tr class="border-b">
              <td class="p-2 font-medium">John Dela Cruz</td>
              <td class="p-2">Backend Dev</td>
              <td class="p-2 text-yellow-600 font-medium">Review</td>
              <td class="p-2">5 / 8</td>
            </tr>
            <tr>
              <td class="p-2 font-medium">Ella Mae</td>
              <td class="p-2">QA Tester</td>
              <td class="p-2 text-green-600 font-medium">Active</td>
              <td class="p-2">9 / 10</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Quick Tools -->
      <div class="col-span-4 bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Quick Tools</h2>
        <div class="flex flex-col gap-3">
          <button class="bg-brand-blue text-white py-2 rounded-md hover:bg-blue-700 transition">Add Task</button>
          <button class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition">Mark Attendance</button>
          <button class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition">Send Update</button>
        </div>
      </div>
    </div>
  </div>
</template>
