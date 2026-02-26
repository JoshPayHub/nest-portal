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

const props = defineProps({ undertimes: Array });
const search = ref("");

const filteredData = computed(() => {
    if (!search.value) return props.undertimes;
    const term = search.value.toLowerCase();
    return props.undertimes.filter(
        (m) =>
            m.undertime_date.toLowerCase().includes(term) ||
            m.total_time.toLowerCase().includes(term),
    );
});

const getStatusClass = (status) => {
    const s = (status ?? "pending").toLowerCase();
    if (s === "approved") return "bg-green-100 text-green-800";
    if (s === "rejected" || s === "denied") return "bg-red-100 text-red-800";
    return "bg-amber-100 text-amber-800";
};

const canEdit = (item) => {
    return (
        item.hr_status.toLowerCase() !== "approved" &&
        item.leader_status.toLowerCase() !== "approved"
    );
};
</script>

<template>
    <div class="p-6">
        <Card class="max-w-7xl mx-auto shadow-sm border-blue-100">
            <CardHeader class="border-b">
                <div class="flex justify-between items-center">
                    <div>
                        <CardTitle
                            class="text-4xl font-extrabold text-brand-blue"
                            >Undertime Requests</CardTitle
                        >
                        <CardDescription class="text-lg"
                            >Track and manage your undertime
                            applications.</CardDescription
                        >
                    </div>
                    <Link href="/employee/undertime-form/create">
                        <Button class="bg-brand-blue h-12 px-8 font-bold">
                            <Plus class="mr-2" /> New Undertime
                        </Button>
                    </Link>
                </div>
            </CardHeader>
            <CardContent class="mt-6">
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
                                <TableHead>DATE FILED</TableHead>
                                <TableHead>UNDERTIME DATE</TableHead>
                                <TableHead>DURATION</TableHead>
                                <TableHead class="text-center"
                                    >DEPT. HEAD</TableHead
                                >
                                <TableHead class="text-center"
                                    >HR STATUS</TableHead
                                >
                                <TableHead class="text-right"
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
                                    <TableCell class="font-bold">{{
                                        item.date_filed
                                    }}</TableCell>
                                    <TableCell>
                                        <div
                                            class="flex items-center gap-2 text-brand-blue"
                                        >
                                            <Calendar class="w-4 h-4" />
                                            {{ item.undertime_date }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <Clock
                                                class="w-4 h-4 text-slate-400"
                                            />
                                            {{ item.total_time }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            :class="
                                                getStatusClass(
                                                    item.leader_status,
                                                )
                                            "
                                            >{{ item.leader_status }}</Badge
                                        >
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            :class="
                                                getStatusClass(item.hr_status)
                                            "
                                            >{{ item.hr_status }}</Badge
                                        >
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button
                                            v-if="canEdit(item)"
                                            variant="outline"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `/employee/undertime-form/edit/${item.id}`,
                                                )
                                            "
                                        >
                                            <Pencil class="w-4 h-4 mr-1" /> Edit
                                        </Button>
                                        <span
                                            v-else
                                            class="text-xs text-slate-400 italic"
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
                                    <p>No Undertime requests found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
