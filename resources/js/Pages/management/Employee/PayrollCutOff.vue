<script setup>
import { ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import {
    Pencil,
    Building2,
    Plus,
    Search,
    FileText,
    Calendar,
    Eye,
} from "lucide-vue-next";
import { toastStore } from "@/stores/toast";

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

const props = defineProps({
    cutoffs: Object,
    statuses: Array,
    filters: Object,
});

const isDialogOpen = ref(false);
const isEditing = ref(false);
const currentEditId = ref(null);
const search = ref(props.filters.search || "");

const form = useForm({
    name: "first_cutoff",
    from_cutoff_date: "",
    to_cutoff_date: "",
    status_id: 1,
});

watch(search, (value) => {
    router.get(
        "/hr/payroll-cut-off",
        { search: value },
        { preserveState: true, replace: true },
    );
});

const openCreateModal = () => {
    isEditing.value = false;
    currentEditId.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    currentEditId.value = item.id;
    form.name = item.name;
    form.from_cutoff_date = item.from_cutoff_date;
    form.to_cutoff_date = item.to_cutoff_date;
    form.status_id = item.status_id;
    isDialogOpen.value = true;
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "2-digit",
        year: "numeric",
    });
};

const submit = () => {
    const url = isEditing.value
        ? `/hr/payroll-cut-off/update/${currentEditId.value}`
        : "/hr/payroll-cut-off/store";

    form.post(url, {
        onSuccess: () => {
            isDialogOpen.value = false;
            form.reset();
            toastStore.show(
                isEditing.value ? "Cutoff updated." : "Cutoff added.",
                "success",
            );
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            toastStore.show(firstError || "Check your inputs.", "danger");
        },
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
                            >Payroll Cut Off</CardTitle
                        >
                        <CardDescription class="text-base mt-1 text-slate-500"
                            >Manage payroll period ranges and
                            statuses.</CardDescription
                        >
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
                                    >Period From</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Period To</TableHead
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
                                        <span
                                            v-if="item.name === 'first_cutoff'"
                                        >
                                            First Cut Off
                                        </span>
                                        <span v-else> Second Cut Off </span>
                                    </TableCell>
                                    <TableCell>{{
                                        formatDate(item.from_cutoff_date)
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatDate(item.to_cutoff_date)
                                    }}</TableCell>
                                    <TableCell class="text-right px-6">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openEditModal(item)"
                                            class="text-brand-blue"
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
                                    >No records found.</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination :links="cutoffs" />
            </CardContent>
        </Card>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-[450px]">
                <DialogHeader>
                    <DialogTitle>{{
                        isEditing ? "Update Cut Off" : "New Cut Off"
                    }}</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label
                            for="name"
                            class="text-sm font-bold text-slate-700 uppercase tracking-tight"
                            >Cut Off Name</Label
                        >
                        <select
                            id="name"
                            v-model="form.name"
                            class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                        >
                            <option value="first_cutoff">First Cut Off</option>
                            <option value="second_cutoff">
                                Second Cut Off
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label
                                for="from"
                                class="text-xs font-bold uppercase"
                                >From Date</Label
                            >
                            <Input
                                id="from"
                                type="date"
                                v-model="form.from_cutoff_date"
                                required
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="to" class="text-xs font-bold uppercase"
                                >To Date</Label
                            >
                            <Input
                                id="to"
                                type="date"
                                v-model="form.to_cutoff_date"
                                required
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="status" class="text-xs font-bold uppercase"
                            >Status</Label
                        >
                        <select
                            id="status"
                            v-model="form.status_id"
                            class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm"
                        >
                            <option
                                v-for="status in statuses"
                                :key="status.id"
                                :value="status.id"
                            >
                                {{ status.name.toUpperCase() }}
                            </option>
                        </select>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="secondary"
                            @click="isDialogOpen = false"
                            >Cancel</Button
                        >
                        <Button
                            type="submit"
                            class="bg-brand-blue"
                            :disabled="form.processing"
                            >Save</Button
                        >
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
