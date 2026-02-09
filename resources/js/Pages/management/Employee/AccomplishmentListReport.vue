<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { Plus, Search, FileText, Calendar, Eye } from "lucide-vue-next";

// UI Components
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from "@/Components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

const props = defineProps({
    reports: Array, // The list of submitted reports
});

const search = ref("");

// Filter reports based on search (Date or Name)
const filteredReports = computed(() => {
    if (!search.value) return props.reports;

    const searchTerm = search.value.toLowerCase();

    return props.reports.filter((report) => {
        const reportDate = String(report.report_date || "").toLowerCase();
        const periodFrom = String(report.period_from || "").toLowerCase();

        return (
            reportDate.includes(searchTerm) || periodFrom.includes(searchTerm)
        );
    });
});
</script>

<template>
    <div class="p-6">
        <Card class="shadow-sm border-blue-100 max-w-7xl mx-auto">
            <CardHeader class="border-b border-slate-100">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-4xl font-extrabold text-brand-blue tracking-tight"
                        >
                            My Accomplishment Reports
                        </CardTitle>
                        <CardDescription class="text-lg mt-2">
                            Review and manage your submitted activity logs.
                        </CardDescription>
                    </div>
                    <Link href="/employee/accomplishment-report/create">
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                        >
                            <Plus class="w-5 h-5 mr-2" /> New Report
                        </Button>
                    </Link>
                </div>
            </CardHeader>

            <CardContent>
                <div class="flex flex-col md:flex-row gap-3 mb-6 items-center">
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search by date (YYYY-MM-DD)..."
                            class="h-12 pl-10 w-full"
                        />
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead class="font-bold text-slate-700"
                                    >DATE SUBMITTED</TableHead
                                >
                                <TableHead class="font-bold text-slate-700"
                                    >PERIOD COVERED</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-700 text-center"
                                    >ACTIVITIES</TableHead
                                >

                                <TableHead
                                    class="font-bold text-center text-slate-700"
                                    >LEADER APPROVAL</TableHead
                                >
                                <TableHead
                                    class="font-bold text-center text-slate-700"
                                    >HR APPROVAL</TableHead
                                >

                                <TableHead
                                    class="text-right font-bold text-slate-700 px-6"
                                    >ACTIONS</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredReports.length > 0">
                                <TableRow
                                    v-for="report in filteredReports"
                                    :key="report.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="p-2 bg-blue-50 rounded text-brand-blue"
                                            >
                                                <Calendar class="w-5 h-5" />
                                            </div>
                                            <div
                                                class="font-bold text-slate-800"
                                            >
                                                {{ report.report_date }}
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-medium text-slate-600"
                                            >
                                                {{ report.period_from }} to
                                                {{ report.period_to }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800"
                                        >
                                            {{ report.activities_count || 0 }}
                                            Items
                                        </span>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="
                                                report.leader_status_name.toLowerCase() ===
                                                'pending'
                                                    ? 'bg-red-100 text-red-800'
                                                    : 'bg-green-100 text-brand-blue'
                                            "
                                        >
                                            {{ report.leader_status_name }}
                                        </span>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="
                                                report.hr_status_name.toLowerCase() ===
                                                'pending'
                                                    ? 'bg-red-100 text-red-800'
                                                    : 'bg-green-100 text-brand-blue'
                                            "
                                        >
                                            {{ report.hr_status_name }}
                                        </span>
                                    </TableCell>

                                    <TableCell class="text-right px-6">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `/accomplishment-report/view/${report.id}`,
                                                )
                                            "
                                            class="text-brand-blue hover:bg-blue-100 font-bold"
                                        >
                                            <Eye class="w-4 h-4 mr-2" /> View
                                            Details
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>

                            <TableRow v-else>
                                <TableCell
                                    colspan="4"
                                    class="text-center text-slate-500 py-10"
                                >
                                    <FileText
                                        class="w-10 h-10 mx-auto mb-2 opacity-20"
                                    />
                                    <p>
                                        No reports found. Start by creating a
                                        new one!
                                    </p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
