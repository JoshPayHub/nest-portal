<script setup>
import { usePage } from "@inertiajs/vue3";

// Import dashboards (adjust paths to your actual structure)
import AnnouncementAndPolicyEmployee from "@/Pages/management/Employee/AnnouncementAndPolicy.vue";
import AnnouncementAndPolicyHR from "@/Pages/management/HR/AnnouncementAndPolicy.vue";

// Get user data from Inertia
const page = usePage();
const user = page.props.auth.user;

// Map roles to dashboards
const dashboards = {
    // Admin: AdminDashboard,
    Employee: AnnouncementAndPolicyEmployee,
    HR: AnnouncementAndPolicyHR,
};

// Determine which dashboard to render
const currentDashboard = dashboards[user?.type] || EmployeeDashboard;
</script>

<template>
    <div>
        <component :is="currentDashboard" />
    </div>
</template>
