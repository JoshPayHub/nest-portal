<script setup>
import { usePage } from "@inertiajs/vue3";

// Import dashboards (adjust paths to your actual structure)
import AdminDashboard from "@/Pages/management/Admin/Index.vue";
import ManagerDashboard from "@/Pages/management/Manager/Index.vue";
import HRDashboard from "@/Pages/management/HR/Index.vue";
import SupervisorDashboard from "@/Pages/management/Supervisor/Index.vue";
import EmployeeDashboard from "@/Pages/management/Employee/Index.vue";
import ClientDashboard from "@/Pages/management/Client/Index.vue";

// Get user data from Inertia
const page = usePage();
const user = page.props.auth.user;

// Map roles to dashboards
const dashboards = {
    Admin: AdminDashboard,
    Manager: ManagerDashboard,
    HR: HRDashboard,
    Supervisor: SupervisorDashboard,
    Employee: EmployeeDashboard,
    Client: ClientDashboard,
};

// Determine which dashboard to render
const currentDashboard = dashboards[user?.type] || EmployeeDashboard;
</script>

<template>
    <div>
        <component :is="currentDashboard" />
    </div>
</template>
