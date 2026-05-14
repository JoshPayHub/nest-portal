<script setup>
import { ref, watch, onMounted } from "vue";
import { Link, usePage, useForm, router } from "@inertiajs/vue3";
import {
    Pencil,
    Search,
    Calendar,
    Eye,
    Lock,
    Clock,
    AlertCircle,
} from "lucide-vue-next";

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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import Pagination from "@/Components/Pagination/Index.vue";
import { Badge } from "@/Components/ui/badge";

const props = defineProps({
    cutoffs: Object,
    filters: Object,
    auth_user_type_id: Number,
});

const routeMap = {
    2: "/employee",
    3: "/head",
};

const isViewOpen = ref(false);
const selectedItem = ref(null);
const search = ref(props.filters.search || "");

watch(search, (value) => {
    router.get(
        `${routeMap[props.auth_user_type_id]}/payroll-cut-offs`,
        { search: value },
        { preserveState: true, replace: true },
    );
});

const viewAttendance = (id) => {
    router.get(
        `${routeMap[props.auth_user_type_id]}/payroll-cut-offs/${id}/attendance`,
    );
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        weekday: "short",
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const openView = (item) => {
    selectedItem.value = item;
    isViewOpen.value = true;

    const url = new URL(window.location);
    if (url.searchParams.has("open")) {
        url.searchParams.delete("open");
        window.history.replaceState({}, "", url);
    }
};

const canEdit = (item) => {
    const leader = item.leader_status_name?.toLowerCase();
    const hr = item.hr_status_name?.toLowerCase();

    if (leader === "rejected" || hr === "rejected") return true;
    if (leader === "approved" || hr === "approved") return false;

    return true;
};

const getStatusClass = (status) => {
    const s = status?.toLowerCase();
    if (s === "approved") return "bg-emerald-100 text-emerald-700";
    if (s === "rejected") return "bg-red-100 text-red-700";
    if (s === "pending") return "bg-amber-100 text-amber-700";
    if (s === "no record") return "bg-slate-100 text-slate-500 italic";
    return "bg-slate-100 text-slate-600";
};

const formatTime = (timeString) => {
    if (!timeString) return "--:--";

    // We use a dummy date so we can use the built-in formatter
    const [hours, minutes] = timeString.split(":");
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));

    return date.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    });
};

const checkUrlForModal = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const cutoffIdToOpen = urlParams.get("open");

    if (cutoffIdToOpen) {
        const cutoff = props.cutoffs.data.find(
            (r) => r.id === parseInt(cutoffIdToOpen),
        );
        if (cutoff) {
            openView(cutoff);
        }
    }
};

onMounted(() => {
    checkUrlForModal();
});

watch(
    () => usePage().props,
    () => {
        checkUrlForModal();
    },
);
</script>

<template>
    <div class="p-6 space-y-8">
        <Card class="shadow-sm border-blue-100">
            <CardHeader class="border-b border-slate-50">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                        >
                            Payroll Cut Off
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            View and manage your attendance for payroll periods.
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>

            <CardContent>
                <div
                    class="flex flex-col md:flex-row gap-3 mb-6 items-center pt-3"
                >
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search cut off..."
                            class="pl-10 h-12"
                        />
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Name</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Period</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >Dept. Head</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >HR Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="cutoffs.data.length > 0">
                                <TableRow
                                    v-for="item in cutoffs.data"
                                    :key="item.id"
                                    class="hover:bg-blue-50/30"
                                >
                                    <TableCell
                                        class="font-semibold text-slate-800"
                                    >
                                        {{
                                            item.name === "first_cutoff"
                                                ? "First Cut Off"
                                                : "Second Cut Off"
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(item.from_cutoff_date) }}
                                        - {{ formatDate(item.to_cutoff_date) }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    item.leader_status_name,
                                                )
                                            "
                                        >
                                            {{ item.leader_status_name }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    item.hr_status_name,
                                                )
                                            "
                                        >
                                            {{ item.hr_status_name }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        class="text-right px-6 space-x-1"
                                    >
                                        <Button
                                            v-if="canEdit(item)"
                                            variant="ghost"
                                            size="sm"
                                            @click="viewAttendance(item.id)"
                                            class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                            title="Edit Attendance"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            v-else
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0 text-slate-400"
                                            disabled
                                        >
                                            <Lock class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openView(item)"
                                            class="h-8 w-8 p-0 text-brand-blue hover:bg-blue-50"
                                            title="View Details"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    colspan="5"
                                    class="text-center text-slate-500 py-10 italic"
                                >
                                    No records found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination :links="cutoffs" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue"
                            >Attendance Details</DialogTitle
                        >
                        <DialogDescription
                            >Submitted on
                            {{ formatDate(selectedItem?.created_at) }}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <div
                    class="grid grid-cols-2 gap-4 py-4 border-y border-slate-100 mt-4"
                >
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Summary for
                            {{
                                selectedItem?.name === "first_cutoff"
                                    ? "First Cut Off"
                                    : "Second Cut Off"
                            }}
                        </p>
                        <p class="text-sm font-semibold">
                            ({{ formatDate(selectedItem?.from_cutoff_date) }} to
                            {{ formatDate(selectedItem?.to_cutoff_date) }})
                        </p>
                    </div>

                    <div class="flex justify-end gap-6">
                        <div class="md:text-right">
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                DEPT. HEAD
                            </p>
                            <Badge
                                variant="outline"
                                :class="
                                    getStatusClass(
                                        selectedItem?.leader_status_name,
                                    )
                                "
                            >
                                {{ selectedItem?.leader_status_name }}
                            </Badge>
                        </div>
                        <div class="md:text-right">
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                HR STATUS
                            </p>
                            <Badge
                                variant="outline"
                                :class="
                                    getStatusClass(selectedItem?.hr_status_name)
                                "
                            >
                                {{ selectedItem?.hr_status_name }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div v-if="selectedItem?.has_record">
                    <div class="space-y-3">
                        <div
                            v-for="log in selectedItem.attendance_list"
                            :key="log.id"
                            class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-50 pb-3 mb-3"
                            >
                                <div
                                    class="flex items-center gap-1.5 text-sm font-bold text-slate-700"
                                >
                                    <Calendar
                                        class="w-3.5 h-3.5 text-brand-blue"
                                    />
                                    {{ formatDate(log.attendance_date) }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    class="text-sm text-slate-600 leading-relaxed break-words whitespace-pre-wrap"
                                >
                                    <p
                                        class="text-[10px] font-bold uppercase text-slate-400 mb-1"
                                    >
                                        Time in
                                    </p>
                                    {{
                                        log.time_in
                                            ? formatTime(log.time_in)
                                            : "--:--"
                                    }}
                                </div>
                                <div
                                    class="text-sm text-slate-600 leading-relaxed break-words whitespace-pre-wrap"
                                >
                                    <p
                                        class="text-[10px] font-bold uppercase text-slate-400 mb-1"
                                    >
                                        Time out
                                    </p>
                                    {{
                                        log.time_out
                                            ? formatTime(log.time_out)
                                            : "--:--"
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="py-12 flex flex-col items-center justify-center text-slate-400 bg-slate-50 rounded-lg border-2 border-dashed border-slate-200"
                >
                    <AlertCircle class="w-12 h-12 mb-2 opacity-20" />
                    <div class="text-center">
                        <p class="font-medium italic">
                            No attendance record has been submitted for this
                            period.
                        </p>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="secondary" @click="isViewOpen = false"
                        >Close</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
