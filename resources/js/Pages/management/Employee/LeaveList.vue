<script setup>
import { useForm, usePage, router, Link } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
import {
    Plus,
    Search,
    Calendar,
    Pencil,
    Lock,
    Eye,
    Clock,
    FileText,
    Plane,
    FileTextIcon,
} from "lucide-vue-next";
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
import Pagination from "@/Components/Pagination/Index.vue";

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/Components/ui/dialog";

const props = defineProps({
    leaves: {
        type: Object,
        required: true,
    },
    auth_user_type_id: Number,
});

const routeMap = {
    2: "/employee",
    3: "/head",
};

const search = ref("");

const isViewOpen = ref(false);
const selectedLeave = ref(null);

const filteredLeaves = computed(() => {
    const data = props.leaves.data || [];
    if (!search.value) return data;
    const term = search.value.toLowerCase();
    return data.filter(
        (req) =>
            req.date_filed.toLowerCase().includes(term) ||
            req.type_leave.toLowerCase().includes(term) ||
            req.start_date.toLowerCase().includes(term) ||
            req.end_date.toLowerCase().includes(term),
    );
});

const openView = (req) => {
    selectedLeave.value = req;
    isViewOpen.value = true;

    const url = new URL(window.location);
    if (url.searchParams.has("open")) {
        url.searchParams.delete("open");
        window.history.replaceState({}, "", url);
    }
};

const canEdit = (req) => {
    const leader = req.leader_status?.toLowerCase();
    const hr = req.hr_status?.toLowerCase();

    if (leader === "rejected" || hr === "rejected") return true;
    if (leader === "approved" || hr === "approved") return false;

    return true; // Default to true for Pending
};

const getStatusClass = (status) => {
    const s = status?.toLowerCase();
    if (s === "approved") return "bg-emerald-100 text-emerald-700";
    if (s === "rejected") return "bg-red-100 text-red-700";
    if (s === "pending") return "bg-amber-100 text-amber-700";
    return "bg-slate-100 text-slate-600";
};

const checkUrlForModal = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const leaveIdToOpen = urlParams.get("open");

    if (leaveIdToOpen) {
        const leave = props.leaves.data.find(
            (r) => r.id === parseInt(leaveIdToOpen),
        );
        if (leave) {
            openView(leave);
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
    <div class="p-6">
        <Card class="shadow-sm border-blue-100">
            <CardHeader class="border-b border-slate-100">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                        >
                            Leave Requests
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Track your leave applications and approval status.
                        </CardDescription>
                    </div>
                    <Link
                        :href="`${routeMap[props.auth_user_type_id]}/leaves/create`"
                    >
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                        >
                            <Plus class="w-5 h-5 mr-2" /> File Leave
                        </Button>
                    </Link>
                </div>
            </CardHeader>

            <CardContent class="mt-3">
                <div class="flex flex-col md:flex-row gap-3 mb-6 items-center">
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search leave type or status..."
                            class="h-12 pl-10 w-full"
                        />
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="w-[180px] font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >DATE</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >LEAVE TYPE</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >PERIOD</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >DEPT. HEAD</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >HR STATUS</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs tracking-wider px-6"
                                    >ACTIONS</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredLeaves.length > 0">
                                <TableRow
                                    v-for="leave in filteredLeaves"
                                    :key="leave.id"
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
                                                {{ leave.date_filed }}
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <div
                                            class="font-semibold text-slate-700"
                                        >
                                            {{ leave.type_leave }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ leave.pay_type }}
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <div
                                            class="text-sm font-semibold text-brand-blue"
                                        >
                                            {{ leave.start_date }} -
                                            {{ leave.end_date }}
                                        </div>
                                        <div
                                            class="text-xs text-slate-500 font-bold"
                                        >
                                            Total: {{ leave.total_days }} Day(s)
                                        </div>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <Badge
                                            :class="
                                                getStatusClass(
                                                    leave.leader_status,
                                                )
                                            "
                                        >
                                            {{ leave.leader_status }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <Badge
                                            :class="
                                                getStatusClass(leave.hr_status)
                                            "
                                        >
                                            {{ leave.hr_status }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="text-right px-6">
                                        <Button
                                            v-if="canEdit(leave)"
                                            variant="ghost"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `${routeMap[props.auth_user_type_id]}/leaves/edit/${leave.id}`,
                                                )
                                            "
                                            class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4 mr-1" />
                                        </Button>

                                        <Button
                                            v-else
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0 text-brand-blue"
                                            disabled
                                        >
                                            <Lock class="w-4 h-4" />
                                        </Button>

                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openView(leave)"
                                            class="h-8 w-8 p-0 text-brand-blue hover:bg-blue-50"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Button>
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
                                    <p>No leave requests found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- pagination import -->
                <Pagination :links="leaves" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue">
                            Leave Details
                        </DialogTitle>
                        <DialogDescription>
                            Submitted on {{ selectedLeave?.date_filed }}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-4 py-4 border-y border-slate-100 mt-4"
                >
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Period Covered
                        </p>
                        <p class="text-sm font-semibold">
                            {{ selectedLeave?.start_date }} to
                            {{ selectedLeave?.end_date }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1">
                            Total: {{ selectedLeave?.total_days }} Day(s) ({{
                                selectedLeave?.pay_type
                            }})
                        </p>
                    </div>

                    <div class="flex md:justify-end gap-6">
                        <div class="md:text-right">
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Leader Status
                            </p>
                            <Badge
                                :class="
                                    getStatusClass(selectedLeave?.leader_status)
                                "
                            >
                                {{ selectedLeave?.leader_status }}
                            </Badge>
                        </div>
                        <div class="md:text-right">
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                HR Status
                            </p>
                            <Badge
                                :class="
                                    getStatusClass(selectedLeave?.hr_status)
                                "
                            >
                                {{ selectedLeave?.hr_status }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white border border-slate-200 rounded-xl p-4 mt-4 mb-2"
                >
                    <div class="border-b pb-2 mb-2">
                        <span
                            class="text-sm font-bold text-slate-700 flex items-center gap-1"
                        >
                            <FileTextIcon class="w-3.5 h-3.5 text-brand-blue" />
                            Reason for Leave:
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 whitespace-pre-wrap">
                        {{ selectedLeave?.reason || "No reason provided." }}
                    </p>
                </div>

                <DialogFooter class="mt-6 print:hidden">
                    <Button variant="secondary" @click="isViewOpen = false"
                        >Close</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
