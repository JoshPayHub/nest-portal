<template>
  <div class="p-6">
    <!-- Header -->
    <div class="w-full bg-white grid place-items-center h-[340px] rounded-2xl shadow-sm border border-blue-100">
      <div class="text-center">
        <h1 class="font-bold text-brand-blue text-3xl mb-2">Client Portal</h1>
        <p class="text-gray-500 text-lg">Manage your services, payments, and support tickets here.</p>
      </div>
    </div>

    <!-- Main Cards -->
    <div class="pt-7 grid grid-cols-12 gap-7">
      <!-- Browse Services -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Browse Services</h2>
        <ul class="text-gray-600 text-sm space-y-3">
          <li>💼 <span class="font-medium text-brand-blue">Business Website Package</span> – ₱25,000</li>
          <li>🛒 <span class="font-medium text-brand-blue">E-Commerce System</span> – ₱40,000</li>
          <li>📱 <span class="font-medium text-brand-blue">Mobile App Integration</span> – ₱30,000</li>
          <li>🔧 <span class="font-medium text-brand-blue">Maintenance Plan</span> – ₱5,000/month</li>
        </ul>
        <div class="mt-5 text-right">
          <button class="bg-brand-blue text-white px-4 py-1 rounded-md hover:bg-blue-700 transition">
            View All Services
          </button>
        </div>
      </div>

      <!-- Payment Overview -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Payment Overview</h2>
        <div class="grid grid-cols-3 text-center">
          <div>
            <h3 class="text-3xl font-bold text-green-500">₱80K</h3>
            <p class="text-gray-500 text-sm">Total Paid</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-yellow-500">₱15K</h3>
            <p class="text-gray-500 text-sm">Pending</p>
          </div>
          <div>
            <h3 class="text-3xl font-bold text-red-500">₱0</h3>
            <p class="text-gray-500 text-sm">Overdue</p>
          </div>
        </div>
        <div class="mt-5 flex justify-center">
          <button class="bg-light-blue text-brand-blue border border-brand-blue px-4 py-1 rounded-md hover:bg-blue-50 transition">
            View Payment History
          </button>
        </div>
      </div>

      <!-- My Transactions -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">My Transactions</h2>
        <table class="w-full text-sm text-left text-gray-600">
          <thead class="text-gray-500 border-b">
            <tr>
              <th class="py-2">Date</th>
              <th>Service</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr class="hover:bg-gray-50">
              <td class="py-2">Nov 10</td>
              <td>Website Package</td>
              <td>₱25,000</td>
              <td><span class="text-green-600 font-medium">Paid</span></td>
            </tr>
            <tr class="hover:bg-gray-50">
              <td class="py-2">Oct 28</td>
              <td>App Integration</td>
              <td>₱30,000</td>
              <td><span class="text-green-600 font-medium">Paid</span></td>
            </tr>
            <tr class="hover:bg-gray-50">
              <td class="py-2">Oct 20</td>
              <td>Maintenance Plan</td>
              <td>₱5,000</td>
              <td><span class="text-yellow-600 font-medium">Pending</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Support & Help Desk -->
      <div class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Support & Help Desk</h2>
        <ul class="text-gray-600 text-sm space-y-3">
          <li>📩 <span class="font-medium text-brand-blue">Ticket #1248</span> – “Issue with invoice” – <span class="text-green-600 font-medium">Resolved</span></li>
          <li>💬 <span class="font-medium text-brand-blue">Ticket #1290</span> – “Service request update” – <span class="text-yellow-600 font-medium">In Progress</span></li>
          <li>🛠️ <span class="font-medium text-brand-blue">Ticket #1305</span> – “Feature enhancement inquiry” – <span class="text-gray-500 font-medium">Open</span></li>
        </ul>
        <div class="mt-5 text-right">
          <button class="bg-brand-blue text-white px-4 py-1 rounded-md hover:bg-blue-700 transition">
            Submit New Ticket
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom Section -->
    <div class="pt-7 grid grid-cols-12 gap-7">
      <!-- Recent Notifications -->
      <div class="col-span-8 bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Recent Notifications</h2>
        <ul class="text-gray-600 text-sm space-y-2">
          <li>📢 Service “Maintenance Plan” updated</li>
          <li>💰 Payment for “App Integration” confirmed</li>
          <li>🕒 New project milestone scheduled for Nov 20</li>
          <li>📨 Message from support team: “Invoice issue resolved.”</li>
        </ul>
      </div>

      <!-- Quick Actions -->
      <div class="col-span-4 bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
        <h2 class="font-semibold text-brand-blue text-xl mb-4">Quick Actions</h2>
        <div class="flex flex-col gap-3">
          <button class="bg-brand-blue text-white py-2 rounded-md hover:bg-blue-700 transition">New Service Request</button>
          <button class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition">View Invoices</button>
          <button class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition">Contact Support</button>
        </div>
      </div>
    </div>
  </div>
</template>
