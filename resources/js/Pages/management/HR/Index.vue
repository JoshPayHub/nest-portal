<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="p-6">
        <!-- Header Section -->
        <div
            class="w-full bg-white grid place-items-center h-[340px] rounded-2xl shadow-sm border border-blue-100"
        >
            <div class="text-center">
                <h1 class="font-bold text-brand-blue text-3xl mb-2">
                    HR Management Center
                </h1>
                <p class="text-gray-500 text-lg">
                    Overview of employee records, recruitment workflow, and HR
                    operations.
                </p>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="pt-7 grid grid-cols-12 gap-7">
            <!-- Card 1: Employee Statistics -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Employee Overview
                </h2>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div>
                        <h3 class="text-3xl font-bold text-brand-blue">256</h3>
                        <p class="text-gray-500 text-sm">Total Employees</p>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-green-500">14</h3>
                        <p class="text-gray-500 text-sm">New Hires</p>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-red-500">3</h3>
                        <p class="text-gray-500 text-sm">On Leave</p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Recruitment -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Recruitment Activity
                </h2>
                <ul class="text-gray-600 text-sm space-y-2">
                    <li>
                        📄
                        <span class="font-medium text-brand-blue">12</span>
                        applications received this week
                    </li>
                    <li>🧪 5 candidates scheduled for interviews</li>
                    <li>✔️ 3 applicants moved to final screening</li>
                    <li>🎉 2 new hires confirmed</li>
                    <li>🔍 Recruitment status updated yesterday</li>
                </ul>
            </div>

            <!-- Card 3: Attendance -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Attendance Summary
                </h2>
                <div class="flex justify-around text-center">
                    <div>
                        <h3 class="text-3xl font-bold text-brand-blue">234</h3>
                        <p class="text-gray-500 text-sm">Present Today</p>
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

            <!-- Card 4: HR Policies -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    HR Policy Snapshot
                </h2>
                <ul class="text-gray-600 text-sm space-y-2">
                    <li>
                        📘 Employee Handbook:
                        <span class="font-medium text-brand-blue">Updated</span>
                    </li>
                    <li>
                        🛡️ Compliance Status:
                        <span class="font-medium text-green-600">Good</span>
                    </li>
                    <li>
                        📅 Next Policy Review:
                        <span class="font-medium text-gray-700"
                            >Jan 15, 2026</span
                        >
                    </li>
                    <li>
                        🧷 Benefits Update:
                        <span class="font-medium text-yellow-600">Pending</span>
                    </li>
                    <li>
                        ⚠️ HR Notices:
                        <span class="font-medium text-red-600">2 Unread</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Widgets -->
        <div class="pt-7 grid grid-cols-12 gap-7">
            <!-- Audit Section -->
            <div
                class="col-span-8 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    HR Activity Logs
                </h2>
                <table
                    class="w-full text-sm text-left text-gray-600 border-t border-gray-100"
                >
                    <thead class="text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="py-2 px-3">Date</th>
                            <th class="py-2 px-3">HR Staff</th>
                            <th class="py-2 px-3">Action</th>
                            <th class="py-2 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">Nov 12, 2025</td>
                            <td class="py-2 px-3">HR Admin</td>
                            <td class="py-2 px-3">Approved Leave Request</td>
                            <td class="py-2 px-3 text-green-600 font-medium">
                                Success
                            </td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">Nov 11, 2025</td>
                            <td class="py-2 px-3">HR Officer</td>
                            <td class="py-2 px-3">Updated Employee Record</td>
                            <td class="py-2 px-3 text-green-600 font-medium">
                                Success
                            </td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">Nov 10, 2025</td>
                            <td class="py-2 px-3">System</td>
                            <td class="py-2 px-3">Auto Attendance Sync</td>
                            <td class="py-2 px-3 text-blue-600 font-medium">
                                Automated
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Quick Actions -->
            <div
                class="col-span-4 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Quick Actions
                </h2>
                <div class="flex flex-col gap-3">
                    <Link
                        href="/management/register"
                        class="bg-brand-blue text-center text-white py-2 rounded-md hover:bg-blue-700 transition"
                    >
                        Add Employee Record
                    </Link>

                    <button
                        class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition"
                    >
                        Post Job Opening
                    </button>
                    <button
                        class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition"
                    >
                        Review Leave Requests
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
