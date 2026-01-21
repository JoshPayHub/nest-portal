<script setup>
import { usePage } from "@inertiajs/vue3";

// Import dashboards (adjust paths to your actual structure)
import ProfileEmployee from "@/Pages/management/Employee/Profile.vue";
import ProfileHR from "@/Pages/management/HR/Profile.vue";

// Get user data from Inertia
const page = usePage();
const user = page.props.auth.user;

// Map roles to dashboards
const dashboards = {
    // Admin: AdminDashboard,
    Employee: ProfileEmployee,
    HR: ProfileHR,
};

// Determine which dashboard to render
const currentDashboard = dashboards[user?.type] || EmployeeDashboard;
</script>

<template>
    <div>
        <component :is="currentDashboard" />
    </div>
</template>
