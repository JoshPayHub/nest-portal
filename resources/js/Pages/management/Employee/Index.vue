<script setup>
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
                    Employee Dashboard
                </h1>
                <p class="text-gray-500 text-lg">
                    Welcome back! Track your tasks, attendance, and performance
                    here.
                </p>
            </div>
        </div>

        <!-- Main Cards -->
        <div class="pt-7 grid grid-cols-12 gap-7">
            <!-- My Tasks -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    My Tasks
                </h2>
                <ul class="text-gray-600 text-sm space-y-2">
                    <li>
                        ✅ Finish project report —
                        <span class="text-green-600">Completed</span>
                    </li>
                    <li>
                        🕒 Review client feedback —
                        <span class="text-yellow-600">In Progress</span>
                    </li>
                    <li>
                        📅 Update team documentation —
                        <span class="text-blue-600">Pending</span>
                    </li>
                </ul>
                <div class="mt-4 text-right">
                    <Link
                        href="/employee/tasks"
                        class="bg-brand-blue text-white px-4 py-1 rounded-md hover:bg-blue-700 transition"
                    >
                        View All Tasks
                    </Link>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Attendance Summary
                </h2>
                <div class="grid grid-cols-3 text-center">
                    <div>
                        <h3 class="text-3xl font-bold text-green-500">22</h3>
                        <p class="text-gray-500 text-sm">Days Present</p>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-yellow-500">2</h3>
                        <p class="text-gray-500 text-sm">Days Late</p>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-red-500">1</h3>
                        <p class="text-gray-500 text-sm">Days Absent</p>
                    </div>
                </div>
                <div class="mt-5 flex justify-center">
                    <Link
                        href="/employee/attendance"
                        class="bg-light-blue text-brand-blue border border-brand-blue px-4 py-1 rounded-md hover:bg-blue-50 transition"
                    >
                        View Attendance Log
                    </Link>
                </div>
            </div>

            <!-- Performance Overview -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Performance Overview
                </h2>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span>Quality of Work</span>
                        <span class="font-semibold text-brand-blue">92%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded-full">
                        <div
                            class="h-2 bg-green-500 rounded-full w-[92%]"
                        ></div>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span>Task Completion</span>
                        <span class="font-semibold text-brand-blue">85%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded-full">
                        <div
                            class="h-2 bg-yellow-400 rounded-full w-[85%]"
                        ></div>
                    </div>
                </div>

                <div class="mt-4 text-right">
                    <Link
                        href="/employee/performance"
                        class="text-brand-blue hover:underline text-sm font-medium"
                    >
                        View Full Performance →
                    </Link>
                </div>
            </div>

            <!-- Company Announcements -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Company Announcements
                </h2>
                <ul class="text-gray-600 text-sm space-y-2">
                    <li>📅 Team Building Event — Nov 20</li>
                    <li>🎄 Holiday Schedule released</li>
                    <li>💰 Bonus payout this Friday</li>
                </ul>

                <div class="mt-4 text-right">
                    <Link
                        href="/management/AnnouncementAndPolicy"
                        class="text-brand-blue hover:underline text-sm font-medium"
                    >
                        View All Announcements →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="pt-7 grid grid-cols-12 gap-7">
            <!-- Recent Activity -->
            <div
                class="col-span-8 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Recent Activity
                </h2>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>🕒 Logged in at 8:05 AM</li>
                    <li>✅ Completed UI update task</li>
                    <li>📤 Submitted daily time record</li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div
                class="col-span-4 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <h2 class="font-semibold text-brand-blue text-xl mb-4">
                    Quick Links
                </h2>
                <div class="flex flex-col gap-3">
                    <Link
                        href="/employee/profile"
                        class="bg-brand-blue text-white py-2 rounded-md hover:bg-blue-700 transition text-center"
                    >
                        View Profile
                    </Link>
                    <Link
                        href="/employee/leave"
                        class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition text-center"
                    >
                        Submit Leave
                    </Link>
                    <Link
                        href="/employee/training"
                        class="bg-light-blue text-brand-blue border border-brand-blue py-2 rounded-md hover:bg-blue-50 transition text-center"
                    >
                        My Training
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
