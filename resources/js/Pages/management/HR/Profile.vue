<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";

// User data (replace with backend props later)
const user = {
    employee_id: "EMP-10234",
    first_name: "Juan",
    last_name: "Dela Cruz",
    email: "juan.delacruz@company.com",
    phone: "09123456789",
    department: "Human Resources",
    position: "HR Staff",
    employment_status: "Regular",
    date_hired: "Jan 15, 2024",
    role: "Employee",
};

// Editable fields
const editableProfile = ref({
    email: user.email,
    phone: user.phone,
});

// Save handler (placeholder)
const saveProfile = () => {
    alert("Profile update request submitted for approval.");
};
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100 flex items-center gap-6"
        >
            <div
                class="w-20 h-20 rounded-full bg-light-blue flex items-center justify-center text-brand-blue text-2xl font-bold"
            >
                {{ user.first_name[0] }}{{ user.last_name[0] }}
            </div>

            <div>
                <h1 class="text-3xl font-bold text-brand-blue">
                    {{ user.first_name }} {{ user.last_name }}
                </h1>
                <p class="text-gray-500">
                    {{ user.position }} · {{ user.department }}
                </p>
            </div>
        </div>

        <!-- Personal Information -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Personal Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="text-gray-500">Employee ID</label>
                    <p class="font-medium">{{ user.employee_id }}</p>
                </div>

                <div>
                    <label class="text-gray-500">Email</label>
                    <input
                        v-model="editableProfile.email"
                        type="email"
                        class="mt-1 w-full p-2 border rounded-md"
                    />
                </div>

                <div>
                    <label class="text-gray-500">Phone</label>
                    <input
                        v-model="editableProfile.phone"
                        type="text"
                        class="mt-1 w-full p-2 border rounded-md"
                    />
                </div>

                <div>
                    <label class="text-gray-500">Role</label>
                    <p class="font-medium">{{ user.role }}</p>
                </div>
            </div>
        </div>

        <!-- Employment Details -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Employment Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <label class="text-gray-500">Department</label>
                    <p class="font-medium">{{ user.department }}</p>
                </div>

                <div>
                    <label class="text-gray-500">Position</label>
                    <p class="font-medium">{{ user.position }}</p>
                </div>

                <div>
                    <label class="text-gray-500">Employment Status</label>
                    <span
                        class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs"
                    >
                        {{ user.employment_status }}
                    </span>
                </div>

                <div>
                    <label class="text-gray-500">Date Hired</label>
                    <p class="font-medium">{{ user.date_hired }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end">
            <button
                @click="saveProfile"
                class="bg-brand-blue text-white px-6 py-2 rounded-md hover:bg-blue-700 transition"
            >
                Save Changes
            </button>
        </div>
    </div>
</template>
