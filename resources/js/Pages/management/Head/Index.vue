<template>
  <div class="p-6">
    <!-- Header Section -->
    <div class="w-full bg-white grid place-items-center h-[340px] rounded-2xl shadow-sm border border-blue-100">
      <div class="text-center">
        <h1 class="font-bold text-brand-blue text-3xl mb-2">Department Dashboard</h1>
        <p class="text-gray-500 text-lg">Monitor performance, requests, and reports across your department.</p>
      </div>
    </div>

    <!-- Overview Cards -->
    <div class="pt-7 grid grid-cols-12 gap-7">
      <!-- Employee Performance -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Employee Performance</h2>
        <div class="flex justify-around text-center">
          <div>
            <h3 class="text-3xl font-bold text-green-500">92%</h3>
            <p class="text-gray-500 text-sm">Avg. Efficiency</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-blue-500">7</h3>
            <p class="text-gray-500 text-sm">Top Performers</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-yellow-500">2</h3>
            <p class="text-gray-500 text-sm">Needs Attention</p>
          </div>
        </div>
      </div>

      <!-- Pending Requests / Approvals -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Pending Requests / Approvals</h2>
        <ul class="text-gray-600 text-sm space-y-2">
          <li>📄 Leave Request - <span class="font-medium text-brand-blue">Maria S.</span> (Pending)</li>
          <li>🕒 Overtime Approval - <span class="font-medium text-brand-blue">Kevin D.</span></li>
          <li>💰 Budget Request - <span class="font-medium text-brand-blue">IT Dept.</span></li>
          <li>✅ Shift Swap - <span class="font-medium text-green-600">Approved</span></li>
          <li>❌ Schedule Change - <span class="font-medium text-red-500">Declined</span></li>
        </ul>
      </div>

      <!-- Department Reports -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Department Reports</h2>
        <div class="flex justify-around text-center">
          <div>
            <h3 class="text-3xl font-bold text-brand-blue">12</h3>
            <p class="text-gray-500 text-sm">Reports This Month</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-green-500">5</h3>
            <p class="text-gray-500 text-sm">Performance Reports</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-yellow-500">2</h3>
            <p class="text-gray-500 text-sm">Pending Submission</p>
          </div>
        </div>
      </div>

      <!-- Messages / Announcements -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Messages / Announcements</h2>
        <ul class="text-gray-600 text-sm space-y-2">
          <li>📢 <span class="font-medium text-brand-blue">Team Meeting</span> scheduled for Nov 15 (9:00 AM)</li>
          <li>💼 New project <span class="font-medium text-green-600">“Skyline Revamp”</span> launched</li>
          <li>🎉 <span class="font-medium text-brand-blue">Employee of the Month:</span> Sarah L.</li>
          <li>⚙️ Department system maintenance on Nov 18</li>
        </ul>
      </div>
    </div>

    <!-- Lower Section -->
    <div class="pt-7 grid grid-cols-12 gap-7">
      <!-- Performance Chart Placeholder -->
      <div class="col-span-8 bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Performance Trends</h2>
        <div class="h-[250px] w-full bg-light-blue/20 border border-dashed border-brand-blue grid place-items-center rounded-lg">
          <p class="text-gray-500">📊 Chart Placeholder (e.g., productivity by week)</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="col-span-4 bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Quick Actions</h2>
        <div class="flex flex-col gap-3">
          <button class="bg-brand-blue text-white py-2 rounded-md hover:bg-blue-700 transition">Approve Requests</button>
          <button class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition">Generate Report</button>
          <button class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition">Send Announcement</button>
        </div>
      </div>
    </div>
  </div>
</template>
