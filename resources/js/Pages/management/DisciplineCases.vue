<script setup>
import { usePage } from "@inertiajs/vue3";

// Import dashboards (adjust paths to your actual structure)
// import AdminDashboard from "@/Pages/management/Admin/Index.vue";
import DisciplineCasesHR from "@/Pages/management/HR/DisciplineCases.vue";
import DisciplineCasesEmployee from "@/Pages/management/Employee/DisciplineCases.vue";

// Get user data from Inertia
const page = usePage();
const user = page.props.auth.user;

// Map roles to dashboards
const dashboards = {
    // Admin: AdminDashboard,
    // Manager: ManagerDashboard,
    HR: DisciplineCasesHR,
    Employee: DisciplineCasesEmployee,
};

// Determine which dashboard to render
const currentDashboard = dashboards[user?.type] || EmployeeDashboard;
</script>

<template>
    <div>
        <component :is="currentDashboard" />
    </div>
</template>
