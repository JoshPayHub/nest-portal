<script setup>
import { usePage } from "@inertiajs/vue3";

// Import dashboards (adjust paths to your actual structure)
// import AdminDashboard from "@/Pages/management/Admin/Index.vue";
import HRRegister from "@/Pages/management/HR/Recruitment.vue";

// Get user data from Inertia
const page = usePage();
const user = page.props.auth.user;

// Map roles to dashboards
const dashboards = {
    // Admin: AdminDashboard,
    // Manager: ManagerDashboard,
    HR: HRRegister,
};

// Determine which dashboard to render
const currentDashboard = dashboards[user?.type] || EmployeeDashboard;
</script>

<template>
    <div>
        <component :is="currentDashboard" />
    </div>
</template>
