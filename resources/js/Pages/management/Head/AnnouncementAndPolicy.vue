<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { Search, FileText, Megaphone, ShieldCheck, Eye } from "lucide-vue-next";

// UI Components
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
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
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead,
} from "@/Components/ui/table";
import Pagination from "@/Components/Pagination/Index.vue";

const props = defineProps({
    data: Object,
    statuses: Array,
    filters: Object,
});

const isDialogOpen = ref(false);
const searchQuery = ref(props.filters.search || "");
const activeTab = ref(props.filters.tab || "all");

const viewData = ref({
    types: "",
    title: "",
    description: "",
});

const handleFilter = () => {
    router.get(
        "/employee/announcements-policies",
        {
            search: searchQuery.value,
            tab: activeTab.value,
        },
        { preserveState: true, replace: true },
    );
};

const openViewModal = (item) => {
    viewData.value = {
        types: item.types,
        title: item.title,
        description: item.description || "No description provided.",
    };
    isDialogOpen.value = true;
};

watch(searchQuery, (value) => {
    clearTimeout(window._searchTimeout);
    window._searchTimeout = setTimeout(() => {
        handleFilter();
    }, 300);
});
</script>

<template>
    <div class="p-6 space-y-8">
        <Card class="max-w-7xl mx-auto shadow-sm border-blue-100">
            <CardHeader class="border-b border-slate-50">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                        >
                            Announcements & Policies
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            View company bulletins and official policy
                            documents.
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>

            <CardContent>
                <div
                    class="flex flex-col md:flex-row gap-3 mb-6 items-center pt-3"
                >
                    <div class="relative w-full md:w-1/2">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="searchQuery"
                            @keyup.enter="handleFilter"
                            placeholder="Search by title..."
                            class="pl-10 h-12 border-slate-200"
                        />
                    </div>
                    <div
                        class="flex flex-col md:flex-row gap-3 w-full md:w-2/3 justify-end"
                    >
                        <select
                            v-model="activeTab"
                            @change="handleFilter"
                            class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium"
                        >
                            <option value="all">All Types</option>
                            <option value="announcements">Announcements</option>
                            <option value="policies">Policies</option>
                        </select>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs w-[120px]"
                                >
                                    Type
                                </TableHead>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                >
                                    Date
                                </TableHead>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                >
                                    Title
                                </TableHead>
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                >
                                    Action
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="data.data.length > 0">
                                <TableRow
                                    v-for="item in data.data"
                                    :key="item.id"
                                    class="hover:bg-blue-50/30 group"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <component
                                                :is="
                                                    item.types ===
                                                    'announcements'
                                                        ? Megaphone
                                                        : ShieldCheck
                                                "
                                                class="w-4 h-4 text-slate-400"
                                            />
                                            <span
                                                class="text-xs font-bold uppercase text-slate-700"
                                            >
                                                {{ item.types }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-bold text-slate-800">
                                        {{ item.created_at }}
                                    </TableCell>
                                    <TableCell class="font-bold text-slate-800">
                                        {{ item.title }}
                                    </TableCell>
                                    <TableCell class="text-right px-6">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openViewModal(item)"
                                            class="text-brand-blue hover:bg-blue-50"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    colspan="4"
                                    class="text-center text-slate-500 py-20 italic"
                                >
                                    <FileText
                                        class="w-12 h-12 mx-auto mb-3 opacity-20"
                                    />
                                    <p class="text-lg font-medium">
                                        No records found
                                    </p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="data" />
            </CardContent>
        </Card>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle
                        class="text-2xl text-brand-blue font-extrabold"
                    >
                        View
                        {{
                            viewData.types === "announcements"
                                ? "Announcement"
                                : "Policy"
                        }}
                    </DialogTitle>
                </DialogHeader>
                <div class="space-y-5 py-4">
                    <div class="space-y-2">
                        <Label
                            class="font-bold text-xs uppercase text-slate-500"
                            >Category</Label
                        >
                        <div
                            class="p-3 bg-slate-50 rounded-md border text-sm font-medium capitalize"
                        >
                            {{ viewData.types }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label
                            class="font-bold text-xs uppercase text-slate-500"
                            >Document Title</Label
                        >
                        <div
                            class="p-3 bg-slate-50 rounded-md border text-sm font-medium"
                        >
                            {{ viewData.title }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label
                            class="font-bold text-xs uppercase text-slate-500"
                            >Description</Label
                        >
                        <div
                            class="p-3 bg-slate-50 rounded-md border text-sm whitespace-pre-wrap min-h-[150px]"
                        >
                            {{ viewData.description }}
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="ghost"
                        @click="isDialogOpen = false"
                    >
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
