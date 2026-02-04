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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
</script>

<template>
    <div class="p-6 space-y-7">
        <Alert variant="destructive" class="border-red-200 bg-red-50/50">
            <AlertTitle class="font-bold"
                >Pending Compliance Actions</AlertTitle
            >
            <AlertDescription>
                You have 2 pending notices that require review before the Jan
                15, 2026 deadline.
            </AlertDescription>
        </Alert>

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
                                256
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Active
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-green-500">14</p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                New Hires
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-red-500">3</p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                On Leave
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="col-span-12 md:col-span-6 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >Attendance & Leave</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <div class="flex justify-around text-center">
                        <div>
                            <p class="text-3xl font-bold text-brand-blue">
                                234
                            </p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Present
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-yellow-500">18</p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Late
                            </p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-red-500">4</p>
                            <p
                                class="text-gray-500 text-xs uppercase tracking-wide"
                            >
                                Absent
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="col-span-12 md:col-span-6 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >Recruitment & Onboarding</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <ul class="text-gray-600 text-sm space-y-3">
                        <li class="flex items-center gap-2">
                            📄 12 new applications
                        </li>
                        <li class="flex items-center gap-2">
                            🧪 5 candidates for interview
                        </li>
                        <li class="flex items-center gap-2">
                            ✔️ 3 for final screening
                        </li>
                    </ul>
                </CardContent>
            </Card>

            <Card class="col-span-12 md:col-span-6 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >Policies & Compliance</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <ul class="text-gray-600 text-sm space-y-3">
                        <li class="flex items-center gap-2">
                            📘 Handbook: Updated
                        </li>
                        <li class="flex items-center gap-2">
                            🛡️ Labor Compliance: Good
                        </li>
                        <li class="flex items-center gap-2">
                            📅 Review: Jan 15, 2026
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-12 gap-7">
            <Card class="col-span-12 lg:col-span-8 border-blue-100">
                <CardHeader>
                    <CardTitle class="text-xl text-brand-blue"
                        >HR Activity Logs</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableBody>
                            <TableRow>
                                <TableCell class="font-medium"
                                    >Leave Approved</TableCell
                                >
                                <TableCell
                                    class="text-right text-green-600 font-semibold"
                                    >Completed</TableCell
                                >
                            </TableRow>
                            <TableRow>
                                <TableCell class="font-medium"
                                    >Employee Record Updated</TableCell
                                >
                                <TableCell
                                    class="text-right text-green-600 font-semibold"
                                    >Completed</TableCell
                                >
                            </TableRow>
                            <TableRow>
                                <TableCell class="font-medium"
                                    >Attendance Sync</TableCell
                                >
                                <TableCell
                                    class="text-right text-blue-600 font-semibold"
                                    >Automated</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

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
                        <Link href="/hr/recruitment/create"
                            >Post Job Opening</Link
                        >
                    </Button>

                    <Button
                        variant="outline"
                        as-child
                        class="border-brand-blue text-brand-blue w-full"
                    >
                        <Link href="/hr/attendance/approvals"
                            >Review Leave Requests</Link
                        >
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
