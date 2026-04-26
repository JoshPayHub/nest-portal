<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { Search, Eye } from "lucide-vue-next";

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
import Pagination from "@/Components/Pagination/Index.vue";

const props = defineProps({
    cutoffs: Object,
    filters: Object,
});

const search = ref(props.filters?.search || "");

// ✅ debounce
let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            "/head/payroll-cut-off",
            { search: value },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    }, 400);
});

const openView = (item) => {
    if (!item?.id) return;
    router.get(`/head/payroll-cut-off/${item.id}/attendance`);
};

const formatDate = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return isNaN(date)
        ? "-"
        : date.toLocaleDateString("en-US", {
              month: "short",
              day: "2-digit",
              year: "numeric",
          });
};
</script>

<template>
    <div class="p-6 space-y-8">
        <Card class="shadow-sm border-blue-100 max-w-7xl mx-auto">
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
                            View active payroll periods and department
                            attendance records.
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>

            <CardContent>
                <!-- Search -->
                <div
                    class="flex flex-col md:flex-row gap-3 mb-6 items-center pt-3"
                >
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search period from or period to..."
                            class="pl-10 h-12"
                        />
                    </div>
                </div>

                <!-- Table -->
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
                                    >Period From</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Period To</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >Attendances</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <template v-if="cutoffs?.data?.length">
                                <TableRow
                                    v-for="item in cutoffs.data"
                                    :key="item.id"
                                    class="hover:bg-blue-50/30"
                                >
                                    <TableCell
                                        class="font-semibold text-slate-800"
                                    >
                                        <span
                                            v-if="item.name === 'first_cutoff'"
                                            >First Cut Off</span
                                        >
                                        <span
                                            v-else-if="
                                                item.name === 'second_cutoff'
                                            "
                                            >Second Cut Off</span
                                        >
                                        <span v-else>{{ item.name }}</span>
                                    </TableCell>

                                    <TableCell>{{
                                        formatDate(item.from_cutoff_date)
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatDate(item.to_cutoff_date)
                                    }}</TableCell>

                                    <TableCell class="text-center">
                                        {{ item.attendances_count ?? 0 }}
                                    </TableCell>

                                    <TableCell class="text-right px-6">
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
                                    colspan="5"
                                    class="text-center text-slate-500 py-10 italic"
                                >
                                    No active records found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="cutoffs" />
            </CardContent>
        </Card>
    </div>
</template>
