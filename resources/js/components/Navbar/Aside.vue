<template>
  <aside class="bg-brand-blue p-4 w-[300px] flex flex-col h-full min-h-0">
    <!-- Scrollable sidebar buttons Start -->
    <div class="flex-1 overflow-y-auto min-h-0 scrollbar-custom">
      <div class="grid gap-2">

        <!-- Loop through filtered menu -->
        <button
          v-for="(item, index) in filteredMenu"
          :key="index"
          class="bg-white hover:bg-gray-100 text-brand-blue cursor-pointer text-xl py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2"
          @click="goTo(item.href)"
        >
          <div class="ps-1">
            <i :class="item.icon"></i>
          </div>
          <div>{{ item.label }}</div>
        </button>
      </div>
    </div>
    <!-- Scrollable sidebar buttons End -->

    <!-- Logout pinned at bottom Start -->
    <div class="mt-4">
      <a
        href="/"
        class="bg-white hover:bg-gray-100 text-brand-blue text-xl py-2 px-3 flex gap-3 justify-center items-center rounded-md w-full"
      >
        <div class="ps-1">
          <img src="@/assets/dashboard/logout.png" class="h-6" alt="">
        </div>
        <div>Log Out</div>
      </a>
    </div>
    <!-- Logout pinned at bottom End -->
  </aside>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3'

// Get the logged-in user from Inertia
const user = usePage().props.auth.user

// Define all possible menu items
const menuItems = [

  // --- Admin, Manager, Supervisor, Employee, Client ---
  {
    label: 'Dashboard',
    href: '/management',
    icon: 'fa-solid fa-gauge',
    roles: ['Admin', 'Manager', 'Supervisor', 'Employee', 'Client']
  },

  // --- ADMIN ---
  {
    label: 'User Management',
    href: '/admin/users',
    icon: 'fa-solid fa-users',
    roles: ['Admin']
  },
  {
    label: 'Departments',
    href: '#',
    icon: 'fa-solid fa-building',
    roles: ['Admin']
  },
  {
    label: 'Reports & Analytics',
    href: '#',
    icon: 'fa-solid fa-flag',
    roles: ['Admin']
  },
  {
    label: 'System Settings',
    href: '#',
    icon: 'fa-solid fa-gear',
    roles: ['Admin']
  },
  {
    label: 'Audit Logs',
    href: '#',
    icon: 'fa-regular fa-font-awesome',
    roles: ['Admin']
  },

  // --- MANAGER ---
  {
    label: 'Employees',
    href: '#',
    icon: 'fa-solid fa-users',
    roles: ['Manager']
  },
  {
    label: 'Department Reports',
    href: '#',
    icon: 'fa-solid fa-building',
    roles: ['Manager']
  },
  {
    label: 'Approvals/Requests',
    href: '#',
    icon: 'fa-solid fa-clock',
    roles: ['Manager']
  },
  {
    label: 'Messages',
    href: '#',
    icon: 'fa-solid fa-message',
    roles: ['Manager']
  },
  {
    label: 'Department Settings',
    href: '#',
    icon: 'fa-solid fa-building',
    roles: ['Manager']
  },
  // --- Supervisor ---
  {
    label: 'Team Overview',
    href: '#',
    icon: 'fa-solid fa-binoculars',
    roles: ['Supervisor']
  },
  {
    label: 'Tasks',
    href: '#',
    icon: 'fa-solid fa-list-check',
    roles: ['Supervisor']
  },
  {
    label: 'Attendance',
    href: '#',
    icon: 'fa-solid fa-clipboard-user',
    roles: ['Supervisor']
  },
  {
    label: 'Reports',
    href: '#',
    icon: 'fa-solid fa-flag',
    roles: ['Supervisor']
  },
  {
    label: 'Announcements',
    href: '#',
    icon: 'fa-solid fa-scroll',
    roles: ['Supervisor']
  },

  // --- Employee ---
  {
    label: 'My Tasks',
    href: '#',
    icon: 'fa-solid fa-list-check',
    roles: ['Employee']
  },
  {
    label: 'Attendance',
    href: '#',
    icon: 'fa-solid fa-clipboard-user',
    roles: ['Employee']
  },
  {
    label: 'Performance',
    href: '#',
    icon: 'fa-solid fa-chart-simple',
    roles: ['Employee']
  },
  {
    label: 'Announcements',
    href: '#',
    icon: 'fa-solid fa-scroll',
    roles: ['Employee']
  },
  {
    label: 'Support / Help Desk',
    href: '#',
    icon: 'fa-solid fa-gear',
    roles: ['Employee']
  },

  // --- Client ---
  {
    label: 'Browse Services',
    href: '#',
    icon: 'fa-solid fa-earth-americas',
    roles: ['Client']
  },
  {
    label: 'Make a Payment',
    href: '#',
    icon: 'fa-solid fa-peso-sign',
    roles: ['Client']
  },
  {
    label: 'My Transactions',
    href: '#',
    icon: 'fa-solid fa-tent-arrow-left-right',
    roles: ['Client']
  },
  {
    label: 'Support',
    href: '#',
    icon: 'fa-solid fa-gear',
    roles: ['Client']
  },
  {
    label: 'Profile Settings',
    href: '#',
    icon: 'fa-solid fa-user',
    roles: ['Client']
  }
]

// Filter menu based on user type
const filteredMenu = menuItems.filter(item => item.roles.includes(user?.type))

// Navigation handler
const goTo = (href) => {
  router.visit(href)
}
</script>
