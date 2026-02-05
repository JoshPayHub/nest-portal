<script setup>
import { Link } from "@inertiajs/vue3";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Table, TableBody, TableCell, TableRow } from "@/Components/ui/table";
import { Alert, AlertTitle, AlertDescription } from "@/Components/ui/alert";

// Props passed from DashboardController
defineProps({
    employeeStats: Object,
    departmentStats: Object,
    positionStats: Object,
    announcementCount: Number,
    policyCount: Number,
});
</script>

<template>
    <div class="p-6 space-y-7">
        <!-- HR Center Card -->
        <Card
            class="w-full flex flex-col items-center justify-center h-[300px] text-center border-blue-100 shadow-sm"
        >
            <CardTitle class="text-4xl font-bold text-brand-blue"
                >HR Management Center</CardTitle
            >
            <CardDescription class="text-lg mt-2">
                Centralized control for employee records, recruitment,
                attendance, and compliance.
            </CardDescription>
        </Card>

        <div class="grid grid-cols-12 gap-7">
            <!-- Employee Records Card -->
            <Card class="col-span-12 md:col-span-6 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >Employee Records</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div>
                            <p class="text-3xl font-bold text-brand-blue">
                                {{ employeeStats.active }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Active
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-green-500">
                                {{ employeeStats.total - employeeStats.active }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Inactive
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-blue-500">
                                {{ employeeStats.total }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Total
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Departments Card -->
            <Card class="col-span-12 md:col-span-6 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >Departments</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div>
                            <p class="text-3xl font-bold text-brand-blue">
                                {{ departmentStats.active }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Active
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-red-500">
                                {{ departmentStats.inactive }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Inactive
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-blue-500">
                                {{ departmentStats.total }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Total
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Positions Card -->
            <Card class="col-span-12 md:col-span-6 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >Positions</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div>
                            <p class="text-3xl font-bold text-brand-blue">
                                {{ positionStats.active }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Active
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-red-500">
                                {{ positionStats.inactive }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Inactive
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-blue-500">
                                {{ positionStats.total }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Total
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Announcements & Policies Card -->
            <Card class="col-span-12 md:col-span-6 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >Announcements & Policies</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div>
                            <p class="text-3xl font-bold text-brand-blue">
                                {{ announcementCount }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Announcements
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-blue-500">
                                {{ policyCount }}
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Policies
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- HR Quick Actions -->
        <div class="grid grid-cols-12 gap-7">
            <Card class="col-span-12 lg:col-span-4 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >HR Quick Actions</CardTitle
                    >
                </CardHeader>
                <CardContent class="flex flex-col gap-3">
                    <Link
                        href="/hr/add-employees"
                        class="bg-brand-blue hover:bg-brand-blue/90 w-full text-white text-center py-2 rounded-md"
                    >
                        Add Employee
                    </Link>

                    <Button
                        variant="outline"
                        as-child
                        class="border-brand-blue text-brand-blue w-full"
                    >
                        <Link href="/hr/department">Manage Departments</Link>
                    </Button>

                    <Button
                        variant="outline"
                        as-child
                        class="border-brand-blue text-brand-blue w-full"
                    >
                        <Link href="/hr/position">Manage Positions</Link>
                    </Button>

                    <Button
                        variant="outline"
                        as-child
                        class="border-brand-blue text-brand-blue w-full"
                    >
                        <Link href="/hr/announcement-policy"
                            >Manage Announcements & Policies</Link
                        >
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
