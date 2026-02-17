<script setup>
import { ref, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import {
    Plus,
    Search,
    Calendar,
    Pencil,
    Clock,
    FileText,
} from "lucide-vue-next";

// UI Components
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Badge } from "@/Components/ui/badge";

const props = defineProps({
    requests: {
        type: Array,
        default: () => [],
    },
});

const search = ref("");

const filteredRequests = computed(() => {
    if (!search.value) return props.requests;
    const term = search.value.toLowerCase();
    return props.requests.filter(
        (req) =>
            req.date_filed.toLowerCase().includes(term) ||
            req.original_date.toLowerCase().includes(term) ||
            req.leader_status.toLowerCase().includes(term),
    );
});

const getStatusClass = (status) => {
    if (!status) return "bg-amber-100 text-amber-800 hover:bg-amber-100";
    const s = status.toLowerCase();
    if (s === "approved")
        return "bg-green-100 text-green-800 hover:bg-green-100";
    if (s === "rejected" || s === "denied" || s === "destructive")
        return "bg-red-100 text-red-800 hover:bg-red-100";
    return "bg-amber-100 text-amber-800 hover:bg-amber-100";
};

const canEdit = (req) => {
    // Only allow editing if both Head and HR have not approved it yet
    return (
        req.hr_status.toLowerCase() !== "approved" &&
        req.leader_status.toLowerCase() !== "approved"
    );
};

/**
 * Helper to join schedule parts (Day | Time)
 * Only shows the separator if both exist
 */
const formatScheduleSub = (day, time) => {
    const parts = [];
    if (day && day !== "N/A") parts.push(day);
    if (time && time !== "N/A") parts.push(time);
    return parts.join(" | ");
};
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
                            Change Off Requests
                        </CardTitle>
                        <CardDescription class="text-lg mt-2">
                            Manage and track your schedule change applications.
                        </CardDescription>
                    </div>
                    <Link href="/employee/change-off/create">
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                        >
                            <Plus class="w-5 h-5 mr-2" /> New Request
                        </Button>
                    </Link>
                </div>
            </CardHeader>

            <CardContent class="mt-6">
                <div class="flex flex-col md:flex-row gap-3 mb-6 items-center">
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search by date or status..."
                            class="h-12 pl-10 w-full"
                        />
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead class="font-bold text-slate-700"
                                    >DATE FILED</TableHead
                                >
                                <TableHead class="font-bold text-slate-700"
                                    >ORIGINAL SCHEDULE</TableHead
                                >
                                <TableHead class="font-bold text-slate-700"
                                    >REQUESTED SCHEDULE</TableHead
                                >
                                <TableHead
                                    class="font-bold text-center text-slate-700"
                                    >DEPT. HEAD</TableHead
                                >
                                <TableHead
                                    class="font-bold text-center text-slate-700"
                                    >HR STATUS</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-700 px-6"
                                    >ACTIONS</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredRequests.length > 0">
                                <TableRow
                                    v-for="req in filteredRequests"
                                    :key="req.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="p-2 bg-blue-50 rounded text-brand-blue"
                                            >
                                                <Clock class="w-5 h-5" />
                                            </div>
                                            <div
                                                class="font-bold text-slate-800"
                                            >
                                                {{ req.date_filed }}
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <div class="text-sm">
                                            <div
                                                class="font-semibold text-slate-700"
                                            >
                                                {{ req.original_date }}
                                            </div>
                                            <div
                                                v-if="
                                                    formatScheduleSub(
                                                        req.original_day,
                                                        req.original_time,
                                                    )
                                                "
                                                class="text-slate-500 text-xs"
                                            >
                                                {{
                                                    formatScheduleSub(
                                                        req.original_day,
                                                        req.original_time,
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <div class="text-sm">
                                            <div
                                                class="font-bold text-brand-blue"
                                            >
                                                {{ req.new_date }}
                                            </div>
                                            <div
                                                v-if="
                                                    formatScheduleSub(
                                                        req.new_day,
                                                        req.new_time,
                                                    )
                                                "
                                                class="text-slate-500 text-xs"
                                            >
                                                {{
                                                    formatScheduleSub(
                                                        req.new_day,
                                                        req.new_time,
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <Badge
                                            :class="
                                                getStatusClass(
                                                    req.leader_status,
                                                )
                                            "
                                        >
                                            {{ req.leader_status }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <Badge
                                            :class="
                                                getStatusClass(req.hr_status)
                                            "
                                        >
                                            {{ req.hr_status }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="text-right px-6">
                                        <Button
                                            v-if="canEdit(req)"
                                            variant="outline"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `/employee/change-off/edit/${req.id}`,
                                                )
                                            "
                                            class="text-amber-600 border-amber-200 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4 mr-1" /> Edit
                                        </Button>
                                        <span
                                            v-else
                                            class="text-xs font-medium text-slate-400 italic"
                                            >Locked</span
                                        >
                                    </TableCell>
                                </TableRow>
                            </template>

                            <TableRow v-else>
                                <TableCell
                                    colspan="6"
                                    class="text-center text-slate-500 py-10"
                                >
                                    <FileText
                                        class="w-10 h-10 mx-auto mb-2 opacity-20"
                                    />
                                    <p>No change off requests found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
