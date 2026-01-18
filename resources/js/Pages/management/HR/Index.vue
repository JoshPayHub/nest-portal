<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div
            class="w-full bg-white grid place-items-center h-[340px] rounded-2xl shadow-sm border border-blue-100"
        >
            <div class="text-center">
                <h1 class="font-bold text-brand-blue text-3xl mb-2">
                    HR Management Center
                </h1>
                <p class="text-gray-500 text-lg">
                    Centralized control for employee records, recruitment,
                    attendance, compliance, and HR operations.
                </p>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="pt-7 grid grid-cols-12 gap-7">
            <!-- Employee Records -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Employee Records
                </h2>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div>
                        <h3 class="text-3xl font-bold text-brand-blue">256</h3>
                        <p class="text-gray-500 text-sm">Active Employees</p>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-green-500">14</h3>
                        <p class="text-gray-500 text-sm">Newly Hired</p>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-red-500">3</h3>
                        <p class="text-gray-500 text-sm">Inactive / On Leave</p>
                    </div>
                </div>
            </div>

            <!-- Recruitment -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Recruitment & Onboarding
                </h2>
                <ul class="text-gray-600 text-sm space-y-2">
                    <li>📄 12 new applications</li>
                    <li>🧪 5 candidates for interview</li>
                    <li>✔️ 3 for final screening</li>
                    <li>🎉 2 hires endorsed</li>
                </ul>
            </div>

            <!-- Attendance -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Attendance & Leave
                </h2>
                <div class="flex justify-around text-center">
                    <div>
                        <h3 class="text-3xl font-bold text-brand-blue">234</h3>
                        <p class="text-gray-500 text-sm">Present</p>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-yellow-500">18</h3>
                        <p class="text-gray-500 text-sm">Late</p>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-red-500">4</h3>
                        <p class="text-gray-500 text-sm">Absent</p>
                    </div>
                </div>
            </div>

            <!-- Policies -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Policies & Compliance
                </h2>
                <ul class="text-gray-600 text-sm space-y-2">
                    <li>📘 Employee Handbook: Updated</li>
                    <li>🛡️ Labor Compliance: Good Standing</li>
                    <li>📅 Next Review: Jan 15, 2026</li>
                    <li>⚠️ Notices: 2 Pending</li>
                </ul>
            </div>
        </div>

        <!-- Logs & Actions -->
        <div class="pt-7 grid grid-cols-12 gap-7">
            <!-- Logs -->
            <div
                class="col-span-8 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    HR Activity Logs
                </h2>
                <table class="w-full text-sm text-gray-600">
                    <tbody>
                        <tr class="border-b">
                            <td class="py-2">Leave Approved</td>
                            <td class="py-2 text-green-600">Completed</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2">Employee Record Updated</td>
                            <td class="py-2 text-green-600">Completed</td>
                        </tr>
                        <tr>
                            <td class="py-2">Attendance Sync</td>
                            <td class="py-2 text-blue-600">Automated</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Actions -->
            <div
                class="col-span-4 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    HR Quick Actions
                </h2>
                <div class="flex flex-col gap-3">
                    <Link
                        href="/hr/employees/create"
                        class="bg-brand-blue text-white py-2 rounded-md text-center"
                    >
                        Add Employee
                    </Link>
                    <Link
                        href="/hr/recruitment/create"
                        class="border border-brand-blue py-2 rounded-md text-brand-blue text-center"
                    >
                        Post Job Opening
                    </Link>
                    <Link
                        href="/hr/attendance/approvals"
                        class="border border-brand-blue py-2 rounded-md text-brand-blue text-center"
                    >
                        Review Leave Requests
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
