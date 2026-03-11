<script setup>
import { ref, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import {
    Plus,
    Search,
    Calendar,
    Pencil,
    Eye,
    Lock,
    FileText,
    Users,
    Clock,
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
    manpowers: {
        type: Object,
        required: true,
    },
});

const search = ref("");
const isViewOpen = ref(false);
const selectedManpower = ref(null);

const filteredManpowers = computed(() => {
    const data = props.manpowers.data || [];
    if (!search.value) return data;
    const term = search.value.toLowerCase();
    return data.filter(
        (req) =>
            req.date_filed.toLowerCase().includes(term) ||
            req.position_type.toLowerCase().includes(term) ||
            req.date_required.toLowerCase().includes(term),
    );
});

const openView = (req) => {
    selectedManpower.value = req;
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
                            >Manpower Requisition</CardTitle
                        >
                        <CardDescription class="text-base mt-1 text-slate-500"
                            >Manage and track personnel
                            requests.</CardDescription
                        >
                    </div>
                    <Link href="/employee/manpower/create">
                        <Button class="bg-brand-blue h-12 px-8 font-bold">
                            <Plus class="mr-2" /> New Request
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
                        placeholder="Search by date..."
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
                                    >DATE REQUIRED</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >POSITION TYPE</TableHead
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
                            <template v-if="filteredManpowers.length > 0">
                                <TableRow
                                    v-for="item in filteredManpowers"
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
                                            {{ item.date_required }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{
                                            item.position_type
                                        }}</Badge>
                                    </TableCell>
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
                                                    `/employee/manpower/edit/${item.id}`,
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
                                    <p>No Manpower Requisition found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="manpowers" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue">
                            Manpower Details
                        </DialogTitle>
                        <DialogDescription>
                            Submitted on {{ selectedManpower?.date_filed }}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-4 py-4 border-y border-slate-100 mt-4"
                >
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Date Required:
                        </p>
                        <p class="text-sm font-semibold">
                            {{ selectedManpower?.date_required }}
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
                                        selectedManpower?.leader_status,
                                    )
                                "
                            >
                                {{ selectedManpower?.leader_status }}
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
                                    getStatusClass(selectedManpower?.hr_status)
                                "
                            >
                                {{ selectedManpower?.hr_status }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-100"
                >
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Report To
                        </p>
                        <p class="text-xs font-semibold text-slate-700">
                            {{ selectedManpower?.report_to }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Position Type
                        </p>
                        <p class="text-xs font-semibold text-slate-700">
                            {{ selectedManpower?.position_type }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Status
                        </p>
                        <p class="text-xs font-semibold text-slate-700">
                            {{ selectedManpower?.status_type }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Payment
                        </p>
                        <p class="text-xs font-semibold text-slate-700">
                            {{ selectedManpower?.payment_type }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase mb-2">
                        Job Description
                    </p>
                    <div
                        class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm"
                    >
                        <p
                            class="text-sm text-slate-700 leading-relaxed break-words whitespace-pre-wrap"
                        >
                            {{
                                selectedManpower?.job_description ||
                                "No reason provided."
                            }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase mb-2">
                        Justification
                    </p>
                    <div
                        class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm"
                    >
                        <p
                            class="text-sm text-slate-700 leading-relaxed break-words whitespace-pre-wrap"
                        >
                            {{
                                selectedManpower?.justification ||
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
