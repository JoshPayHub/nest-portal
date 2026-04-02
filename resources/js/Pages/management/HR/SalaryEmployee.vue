<script setup>
import { ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import {
    Pencil,
    UserCircle,
    Plus,
    Search,
    FileText,
    Banknote,
} from "lucide-vue-next";

import { toastStore } from "@/stores/toast";
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
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import Pagination from "@/Components/Pagination/Index.vue";

const props = defineProps({
    salaryEmployees: Object,
    availableEmployees: Array,
    departments: Array, // Galing sa controller
    statuses: Array,
    filters: Object,
});

const isDialogOpen = ref(false);
const isEditing = ref(false);
const currentEditId = ref(null);
const selectedEmployeeName = ref("");
const search = ref(props.filters.search || "");
const selectedDept = ref(props.filters.department_id || ""); // Reactive variable para sa filter

const form = useForm({
    user_id: "",
    salary_amount: "",
    type: "monthly",
    status_id: 1,
});

// Watcher para sa Search at Department Filter
watch([search, selectedDept], ([searchValue, deptValue]) => {
    router.get(
        "/hr/salary-employee",
        {
            search: searchValue,
            department_id: deptValue,
        },
        { preserveState: true, replace: true },
    );
});

const openCreateModal = () => {
    isEditing.value = false;
    currentEditId.value = null;
    selectedEmployeeName.value = "";
    form.reset();
    isDialogOpen.value = true;
};

const openEditModal = (record) => {
    isEditing.value = true;
    currentEditId.value = record.id;
    selectedEmployeeName.value = `${record.user.first_name} ${record.user.last_name}`;
    form.user_id = record.user_id;
    form.salary_amount = record.salary_amount;
    form.type = record.type;
    form.status_id = record.status_id;
    isDialogOpen.value = true;
};

const submit = () => {
    const url = isEditing.value
        ? `/hr/salary-employee/update/${currentEditId.value}`
        : "/hr/salary-employee/store";

    form.post(url, {
        onSuccess: () => {
            isDialogOpen.value = false;
            form.reset();
            toastStore.show(
                isEditing.value
                    ? "Salary record updated."
                    : "Salary record added.",
                "success",
            );
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            toastStore.show(firstError || "Check your inputs.", "danger");
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
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
                            Salary Employee Management
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Configure employee salaries and payment types.
                        </CardDescription>
                    </div>
                    <Button
                        @click="openCreateModal"
                        class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md"
                    >
                        <Plus class="w-5 h-5 mr-2" /> Add New Salary
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div class="flex justify-between gap-3 mb-6 items-center pt-3">
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search employee name..."
                            class="pl-10 h-12"
                        />
                    </div>

                    <select
                        v-model="selectedDept"
                        class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-brand-blue transition-all cursor-pointer"
                    >
                        <option value="">All Departments</option>
                        <option
                            v-for="d in departments"
                            :key="d.id"
                            :value="d.id"
                        >
                            {{ d.name }}
                        </option>
                    </select>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Employee & Department</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Salary Amount</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Type</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs tracking-wider px-6"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="salaryEmployees.data.length > 0">
                                <TableRow
                                    v-for="record in salaryEmployees.data"
                                    :key="record.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
                                >
                                    <TableCell
                                        class="font-semibold text-slate-800"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="p-2 bg-blue-50 rounded text-brand-blue"
                                            >
                                                <UserCircle class="w-4 h-4" />
                                            </div>
                                            <div>
                                                <p>
                                                    {{ record.user.first_name }}
                                                    {{ record.user.last_name }}
                                                </p>
                                                <p
                                                    class="text-xs text-slate-500 font-normal"
                                                >
                                                    {{
                                                        record.user.department
                                                            ?.name || "N/A"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="text-slate-800 font-medium"
                                    >
                                        {{
                                            formatCurrency(record.salary_amount)
                                        }}
                                    </TableCell>
                                    <TableCell
                                        class="text-slate-800 capitalize"
                                    >
                                        <span
                                            class="px-2 py-1 bg-slate-100 rounded text-xs"
                                            >{{ record.type }}</span
                                        >
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase',
                                                record.status_id === 1
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-red-100 text-red-700',
                                            ]"
                                        >
                                            {{ record.status.name }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right px-6">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openEditModal(record)"
                                            class="h-8 w-8 p-0 text-amber-600 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    colspan="5"
                                    class="text-center text-slate-500 py-10 italic"
                                >
                                    <FileText
                                        class="w-10 h-10 mx-auto mb-2 opacity-20"
                                    />
                                    No records found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination :links="salaryEmployees" />
            </CardContent>
        </Card>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent
                class="sm:max-w-[450px] overflow-hidden border-none shadow-2xl"
            >
                <DialogHeader>
                    <DialogTitle class="text-2xl font-bold text-brand-blue">
                        {{
                            isEditing
                                ? "Update Salary Record"
                                : "New Salary Entry"
                        }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label
                            class="text-sm font-bold text-slate-700 uppercase tracking-tight"
                            >Employee</Label
                        >
                        <Input
                            v-if="isEditing"
                            :value="selectedEmployeeName"
                            disabled
                            class="bg-slate-50 border-slate-200"
                        />
                        <select
                            v-else
                            v-model="form.user_id"
                            required
                            class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                        >
                            <option value="" disabled>
                                Select an employee
                            </option>
                            <option
                                v-for="emp in availableEmployees"
                                :key="emp.id"
                                :value="emp.id"
                            >
                                {{ emp.full_name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <Label
                            for="amount"
                            class="text-sm font-bold text-slate-700 uppercase tracking-tight"
                            >Salary Amount (PHP)</Label
                        >
                        <div class="relative">
                            <Banknote
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                            />
                            <Input
                                id="amount"
                                type="number"
                                step="0.01"
                                v-model="form.salary_amount"
                                placeholder="0.00"
                                class="h-12 pl-10"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label
                                for="type"
                                class="text-sm font-bold text-slate-700 uppercase tracking-tight"
                                >Payment Type</Label
                            >
                            <select
                                id="type"
                                v-model="form.type"
                                class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                            >
                                <option value="monthly">Monthly</option>
                                <option value="daily">Daily</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <Label
                                for="status"
                                class="text-sm font-bold text-slate-700 uppercase tracking-tight"
                                >Status</Label
                            >
                            <select
                                id="status"
                                v-model="form.status_id"
                                class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
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
                    </div>

                    <DialogFooter class="pt-4 gap-2">
                        <Button
                            type="button"
                            variant="secondary"
                            @click="isDialogOpen = false"
                            class="px-8 h-12 font-bold"
                            >Cancel</Button
                        >
                        <Button
                            type="submit"
                            class="bg-brand-blue px-8 h-12 font-bold shadow-lg"
                            :disabled="form.processing"
                        >
                            {{ isEditing ? "Save Changes" : "Confirm Entry" }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
