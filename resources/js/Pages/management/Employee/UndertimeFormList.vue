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
    Eye,
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
    undertimes: {
        type: Object,
        required: true,
    },
});

const search = ref("");
const isViewOpen = ref(false);
const selectedUndertime = ref(null);

const filteredData = computed(() => {
    const data = props.undertimes.data || [];
    if (!search.value) return data;
    const term = search.value.toLowerCase();
    return data.filter(
        (req) =>
            req.date_filed.toLowerCase().includes(term) ||
            req.undertime_date.toLowerCase().includes(term) ||
            req.reason.toLowerCase().includes(term),
    );
});

const openView = (req) => {
    selectedUndertime.value = req;
    isViewOpen.value = true;
};

const canEdit = (req) => {
    const leader = req.leader_status?.toLowerCase();
    const hr = req.hr_status?.toLowerCase();

    if (leader === "rejected" || hr === "rejected") return true;
    if (leader === "approved" || hr === "approved") return false;

    return true;
};

const getStatusClass = (status) => {
    const s = status?.toLowerCase();
    if (s === "approved") return "bg-emerald-100 text-emerald-700";
    if (s === "rejected") return "bg-red-100 text-red-700";
    if (s === "pending") return "bg-amber-100 text-amber-700";
    return "bg-slate-100 text-slate-600";
};
</script>

<template>
    <div class="p-6">
        <Card class="max-w-7xl mx-auto shadow-sm border-blue-100">
            <CardHeader class="border-b">
                <div class="flex justify-between items-center">
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                            >Undertime Requests</CardTitle
                        >
                        <CardDescription class="text-base mt-1 text-slate-500"
                            >Track and manage your undertime
                            applications.</CardDescription
                        >
                    </div>
                    <Link href="/employee/undertime-form/create">
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                        >
                            <Plus class="mr-2" /> New Undertime
                        </Button>
                    </Link>
                </div>
            </CardHeader>
            <CardContent class="mt-3">
                <div class="relative w-full md:w-1/3 mb-6">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search dates..."
                        class="pl-10 h-12"
                    />
                </div>
                <div class="rounded-md border overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead
                                    class="w-[180px] font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >DATE</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >UNDERTIME DATE</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Reason</TableHead
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
                            <template v-if="filteredData.length > 0">
                                <TableRow
                                    v-for="item in filteredData"
                                    :key="item.id"
                                    class="hover:bg-slate-50"
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
                                    <TableCell>
                                        <div
                                            class="text-sm font-medium text-slate-700"
                                        >
                                            {{ item.undertime_date }}
                                        </div>
                                    </TableCell>
                                    <!-- <TableCell>
                                        <div class="flex items-center gap-2">
                                            <Clock
                                                class="w-4 h-4 text-slate-400"
                                            />
                                            {{ item.total_time }}
                                        </div>
                                    </TableCell> -->
                                    <TableCell
                                        class="truncate max-w-[100px] text-sm font-medium text-slate-700"
                                    >
                                        <p></p>
                                        {{ item.reason }}</TableCell
                                    >

                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    item.leader_status,
                                                )
                                            "
                                        >
                                            {{ item.leader_status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(item.hr_status)
                                            "
                                        >
                                            {{ item.hr_status }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell
                                        class="text-right px-6 space-x-1"
                                    >
                                        <Button
                                            v-if="canEdit(item)"
                                            variant="ghost"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `/employee/undertime-form/edit/${item.id}`,
                                                )
                                            "
                                            class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4" />
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
                                            @click="openView(item)"
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
                                    <p>No Undertime requests found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="undertimes" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue">
                            Undertime Details
                        </DialogTitle>
                        <DialogDescription>
                            Submitted on {{ selectedUndertime?.date_filed }}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-4 py-4 border-y border-slate-100 mt-4"
                >
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Date of Undertime
                        </p>
                        <p class="text-sm font-semibold">
                            {{ selectedUndertime?.undertime_date }}
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
                                    getStatusClass(
                                        selectedUndertime?.leader_status,
                                    )
                                "
                            >
                                {{ selectedUndertime?.leader_status }}
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
                                    getStatusClass(selectedUndertime?.hr_status)
                                "
                            >
                                {{ selectedUndertime?.hr_status }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div
                    class="grid sm:grid-cols-3 grid-cols-1 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-100"
                >
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            From (Start Time)
                        </p>
                        <p class="text-xs font-semibold text-slate-700">
                            {{ selectedUndertime?.from_date }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            From (To Time)
                        </p>
                        <p class="text-xs font-semibold text-slate-700">
                            {{ selectedUndertime?.to_date }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Calculated Duration
                        </p>
                        <p class="text-xs font-semibold text-slate-700">
                            {{ selectedUndertime?.total_time }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase mb-2">
                        Reason
                    </p>
                    <div
                        class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm"
                    >
                        <p
                            class="text-sm text-slate-700 leading-relaxed break-words whitespace-pre-wrap"
                        >
                            {{
                                selectedUndertime?.reason ||
                                "No reason provided."
                            }}
                        </p>
                    </div>
                </div>

                <DialogFooter class="mt-3">
                    <Button variant="secondary" @click="isViewOpen = false"
                        >Close</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
