<script setup>
import { ref, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import {
    Plus,
    Search,
    Calendar,
    Pencil,
    FileText,
    Clock,
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
    absences: {
        type: Array,
        default: () => [],
    },
});

const search = ref("");

const filteredAbsences = computed(() => {
    if (!search.value) return props.absences;
    const term = search.value.toLowerCase();
    return props.absences.filter((a) => {
        // Safe check for nulls using ?? ""
        return (
            (a.type_absence ?? "").toLowerCase().includes(term) ||
            (a.date_absence ?? "").toLowerCase().includes(term) ||
            (a.leader_status ?? "").toLowerCase().includes(term) ||
            (a.hr_status ?? "").toLowerCase().includes(term)
        );
    });
});

const getStatusClass = (status) => {
    // If status is null or undefined, default to amber (pending)
    const s = (status ?? "pending").toLowerCase();
    if (s === "approved")
        return "bg-green-100 text-green-800 hover:bg-green-100";
    if (s === "rejected" || s === "denied")
        return "bg-red-100 text-red-800 hover:bg-red-100";
    return "bg-amber-100 text-amber-800 hover:bg-amber-100";
};

const canEdit = (item) => {
    // Null-safe check: only lock if status is explicitly 'approved'
    const hr = (item.hr_status ?? "").toLowerCase();
    const leader = (item.leader_status ?? "").toLowerCase();
    return hr !== "approved" && leader !== "approved";
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
                            Leave of Absence
                        </CardTitle>
                        <CardDescription class="text-lg mt-2">
                            Track your single-day absence filings and approval
                            status.
                        </CardDescription>
                    </div>
                    <Link href="/employee/leave-of-absence/create">
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                        >
                            <Plus class="w-5 h-5 mr-2" /> File Absence
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
                            placeholder="Search absence type..."
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
                                    >ABSENCE TYPE</TableHead
                                >
                                <TableHead class="font-bold text-slate-700"
                                    >ABSENCE DATE</TableHead
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
                            <template v-if="filteredAbsences.length > 0">
                                <TableRow
                                    v-for="item in filteredAbsences"
                                    :key="item.id"
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
                                                {{ item.date_filed }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="font-semibold text-slate-700"
                                    >
                                        {{ item.type_absence }}
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            class="flex items-center gap-2 text-brand-blue font-medium"
                                        >
                                            <Calendar
                                                class="w-4 h-4 text-slate-400"
                                            />
                                            {{ item.date_absence }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            :class="
                                                getStatusClass(
                                                    item.leader_status,
                                                )
                                            "
                                        >
                                            {{
                                                item.leader_status ?? "Pending"
                                            }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            :class="
                                                getStatusClass(item.hr_status)
                                            "
                                        >
                                            {{ item.hr_status ?? "Pending" }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right px-6">
                                        <Button
                                            v-if="canEdit(item)"
                                            variant="outline"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `/employee/leave-of-absence/edit/${item.id}`,
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
                                    <p>No absence filings found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
