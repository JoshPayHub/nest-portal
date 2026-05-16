<script setup>
import { ref, computed, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
    Pencil,
    Plus,
    Users,
    Briefcase,
    Search,
    FileText,
    Settings2,
} from "lucide-vue-next";

import { toastStore } from "@/stores/toast";

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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/Components/ui/dialog";

import Pagination from "@/Components/Pagination/Index.vue";

const props = defineProps({
    employees: Object,
    departments: Array,
    statuses: Array,
    filters: Object,
});

const search = ref("");
const selectedDept = ref("");
const selectedEmplo = ref("");
const selectedStatus = ref("");

// Modal state
const showStatusModal = ref(false);
const selectedEmployee = ref(null);
const newStatus = ref("");

const employeeOptions = computed(() => {
    const allEmployees = props.employees.data || [];

    if (!selectedDept.value) return allEmployees;

    return allEmployees.filter(
        (emp) => emp.department_id == selectedDept.value,
    );
});

watch(selectedDept, () => {
    selectedEmplo.value = "";
});

const filteredEmployees = computed(() => {
    let data = props.employees.data || [];
    const term = search.value.toLowerCase();

    return data.filter((emp) => {
        const matchesSearch =
            !term ||
            emp.employee_id?.toLowerCase().includes(term) ||
            emp.username?.toLowerCase().includes(term) ||
            emp.company_email?.toLowerCase().includes(term);

        const matchesDept =
            !selectedDept.value || emp.department_id == selectedDept.value;

        const matchesSpecificEmp =
            !selectedEmplo.value || emp.id == selectedEmplo.value;

        const matchesStatus =
            !selectedStatus.value || emp.status_id == selectedStatus.value;

        return (
            matchesSearch && matchesDept && matchesStatus && matchesSpecificEmp
        );
    });
});

const openStatusModal = (employee) => {
    if (employee.status_id === 4) {
        toastStore.show("Pending employee status cannot be updated.", "danger");
        return;
    }

    selectedEmployee.value = employee;
    newStatus.value = employee.status_id;
    showStatusModal.value = true;
};

const updateEmployeeStatus = () => {
    if (!selectedEmployee.value || !newStatus.value) {
        toastStore.show("Please select a valid employee status.", "danger");
        return;
    }

    // Prevent same status update
    if (Number(newStatus.value) === Number(selectedEmployee.value.status_id)) {
        toastStore.show("Please choose a different status.", "danger");
        return;
    }

    const url = `/hr/list-employee/update/${selectedEmployee.value.id}`;

    router.put(
        url,
        {
            status_id: newStatus.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showStatusModal.value = false;

                selectedEmployee.value = null;
                newStatus.value = "";

                toastStore.show(
                    "Employee status updated successfully.",
                    "success",
                );
            },

            onError: (errors) => {
                const firstError = Object.values(errors)[0];

                toastStore.show(
                    firstError || "Failed to update employee status.",
                    "danger",
                );
            },
        },
    );
};
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
                            Employee Directory
                        </CardTitle>

                        <CardDescription class="text-base mt-1 text-slate-500">
                            View and manage workforce assignments.
                        </CardDescription>
                    </div>

                    <Link href="/hr/add-employees">
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md"
                        >
                            <Plus class="w-5 h-5 mr-2" />
                            Register Employee
                        </Button>
                    </Link>
                </div>
            </CardHeader>

            <CardContent>
                <!-- Filters -->
                <div
                    class="flex flex-col md:flex-row gap-3 mb-6 items-center pt-3"
                >
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search Name, ID, or Username..."
                            class="pl-10 h-12"
                        />
                    </div>

                    <select
                        v-model="selectedDept"
                        class="h-12 w-full md:w-1/4 rounded-md border px-3"
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

                    <select
                        v-model="selectedEmplo"
                        class="h-12 w-full md:w-1/4 rounded-md border px-3"
                    >
                        <option value="">All Employees</option>

                        <option
                            v-for="emp in employeeOptions"
                            :key="emp.id"
                            :value="emp.id"
                        >
                            {{ emp.username }}
                        </option>
                    </select>

                    <select
                        v-model="selectedStatus"
                        class="h-12 w-full md:w-1/4 rounded-md border px-3"
                    >
                        <option value="">All Statuses</option>

                        <option v-for="s in statuses" :key="s.id" :value="s.id">
                            {{ s.name }}
                        </option>
                    </select>
                </div>

                <!-- Table -->
                <div class="rounded-md border overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead>ID & USERNAME</TableHead>
                                <TableHead>COMPANY EMAIL</TableHead>
                                <TableHead>DEPARTMENT & POSITION</TableHead>
                                <TableHead class="text-center">
                                    STATUS
                                </TableHead>
                                <TableHead class="text-right">
                                    ACTIONS
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <template v-if="filteredEmployees.length > 0">
                                <TableRow
                                    v-for="emp in filteredEmployees"
                                    :key="emp.id"
                                >
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-bold">
                                                {{ emp.employee_id || "N/A" }}
                                            </span>

                                            <span
                                                class="text-xs text-brand-blue"
                                            >
                                                @{{ emp.username }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        {{ emp.company_email || emp.email }}
                                    </TableCell>

                                    <TableCell>
                                        <div class="flex flex-col gap-1">
                                            <span
                                                class="inline-flex items-center gap-1"
                                            >
                                                <Users class="w-4 h-4" />
                                                {{
                                                    emp.department?.name ??
                                                    "Unassigned"
                                                }}
                                            </span>

                                            <span
                                                class="inline-flex items-center gap-1 text-xs text-slate-500"
                                            >
                                                <Briefcase class="w-4 h-4" />
                                                {{
                                                    emp.position?.name ??
                                                    "No Position"
                                                }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase',

                                                emp.status_id === 1
                                                    ? 'bg-green-100 text-green-700'
                                                    : emp.status_id === 2
                                                      ? 'bg-gray-100 text-gray-700'
                                                      : emp.status_id === 3
                                                        ? 'bg-orange-100 text-orange-700'
                                                        : emp.status_id === 4
                                                          ? 'bg-yellow-100 text-yellow-700'
                                                          : emp.status_id === 9
                                                            ? 'bg-red-100 text-red-700'
                                                            : 'bg-slate-100 text-slate-700',
                                            ]"
                                        >
                                            {{ emp.status?.name }}
                                        </span>
                                    </TableCell>

                                    <TableCell
                                        class="text-right flex justify-end gap-2"
                                    >
                                        <!-- Update Status -->
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            :disabled="emp.status_id === 4"
                                            @click="openStatusModal(emp)"
                                        >
                                            <Settings2
                                                class="w-4 h-4 text-blue-600"
                                            />
                                        </Button>

                                        <!-- Edit -->
                                        <Link
                                            :href="`/hr/employees/edit/${emp.id}`"
                                        >
                                            <Button variant="ghost" size="sm">
                                                <Pencil
                                                    class="w-4 h-4 text-amber-600"
                                                />
                                            </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </template>

                            <TableRow v-else>
                                <TableCell
                                    colspan="5"
                                    class="text-center py-10"
                                >
                                    <FileText
                                        class="w-10 h-10 mx-auto opacity-20"
                                    />
                                    No employees found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="employees" />
            </CardContent>
        </Card>

        <!-- STATUS MODAL -->
        <Dialog v-model:open="showStatusModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle> Update Employee Status </DialogTitle>

                    <DialogDescription>
                        Change the status of
                        <strong> @{{ selectedEmployee?.username }} </strong>
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-2 py-4">
                    <label class="text-sm font-medium"> Select Status </label>

                    <select
                        v-model="newStatus"
                        class="w-full border rounded-md px-3 py-2"
                    >
                        <!-- 1. Loop through statuses using a invisible <template> tag -->
                        <template v-for="status in statuses" :key="status.id">
                            <!-- 2. Only render the <option> if it's NOT 'pending' -->
                            <option
                                v-if="status.name.toLowerCase() !== 'pending'"
                                :value="status.id"
                            >
                                {{ status.name }}
                            </option>
                        </template>
                    </select>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showStatusModal = false">
                        Cancel
                    </Button>

                    <Button class="bg-brand-blue" @click="updateEmployeeStatus">
                        Update Status
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
