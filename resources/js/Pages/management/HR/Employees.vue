<script setup>
import { ref, computed, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import {
    Pencil,
    Plus,
    Users,
    Briefcase,
    Search,
    FileText,
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

import Pagination from "@/Components/Pagination/Index.vue";

const props = defineProps({
    employees: Object, // Paginated object containing .data
    departments: Array,
    statuses: Array,
    filters: Object,
});

// State for filtering
const search = ref("");
const selectedDept = ref("");
const selectedEmplo = ref("");
const selectedStatus = ref("");

// 1. Filter the list of employees shown in the "All Employees" dropdown
// This ensures that if a department is picked, only those users appear in the list.
const employeeOptions = computed(() => {
    const allEmployees = props.employees.data || [];
    if (!selectedDept.value) return allEmployees;

    return allEmployees.filter(
        (emp) => emp.department_id == selectedDept.value,
    );
});

// 2. Reset the specific employee selection if the department changes
watch(selectedDept, () => {
    selectedEmplo.value = "";
});

// 3. Table Filter Logic
const filteredEmployees = computed(() => {
    let data = props.employees.data || [];
    const term = search.value.toLowerCase();

    return data.filter((emp) => {
        // Search by Name, ID, or Username
        const matchesSearch =
            !term ||
            emp.employee_id?.toLowerCase().includes(term) ||
            emp.username?.toLowerCase().includes(term) ||
            emp.company_email?.toLowerCase().includes(term);

        // Filter by Department
        const matchesDept =
            !selectedDept.value || emp.department_id == selectedDept.value;

        // Filter by Specific Employee (The Username Select)
        const matchesSpecificEmp =
            !selectedEmplo.value || emp.id == selectedEmplo.value;

        // Filter by Status
        const matchesStatus =
            !selectedStatus.value || emp.status_id == selectedStatus.value;

        return (
            matchesSearch && matchesDept && matchesStatus && matchesSpecificEmp
        );
    });
});
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
                            Employee Directory
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            View and manage workforce assignments.
                        </CardDescription>
                    </div>
                    <Link href="/hr/add-employees">
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                        >
                            <Plus class="w-5 h-5 mr-2" /> Register Employee
                        </Button>
                    </Link>
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
                            placeholder="Search Name, ID, or Username..."
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

                    <select
                        v-model="selectedEmplo"
                        class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-brand-blue transition-all cursor-pointer"
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
                        class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-brand-blue transition-all cursor-pointer"
                    >
                        <option value="">All Statuses</option>
                        <option v-for="s in statuses" :key="s.id" :value="s.id">
                            {{ s.name }}
                        </option>
                    </select>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >ID & USERNAME</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >COMPANY EMAIL</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >DEPARTMENT & POSITION</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >STATUS</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs tracking-wider px-6"
                                    >ACTIONS</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredEmployees.length > 0">
                                <TableRow
                                    v-for="emp in filteredEmployees"
                                    :key="emp.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
                                >
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span
                                                class="font-bold text-slate-800"
                                            >
                                                {{ emp.employee_id || "N/A" }}
                                            </span>
                                            <span
                                                class="text-xs text-brand-blue font-medium"
                                            >
                                                @{{ emp.username }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            class="text-slate-600 font-medium"
                                        >
                                            {{ emp.company_email || emp.email }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col gap-1">
                                            <span
                                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700"
                                            >
                                                <Users
                                                    class="w-4 h-4 text-slate-400"
                                                />
                                                {{
                                                    emp.department?.name ||
                                                    "Unassigned"
                                                }}
                                            </span>
                                            <span
                                                class="inline-flex items-center gap-1.5 text-xs text-slate-500"
                                            >
                                                <Briefcase
                                                    class="w-3.5 h-3.5 text-slate-400"
                                                />
                                                {{
                                                    emp.position?.name ||
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
                                                    : 'bg-red-100 text-red-700',
                                            ]"
                                        >
                                            {{ emp.status?.name }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right px-6">
                                        <Link
                                            :href="`/hr/employees/edit/${emp.id}`"
                                        >
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                            >
                                                <Pencil class="w-4 h-4" />
                                            </Button>
                                        </Link>
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
                                    No employees found matching your criteria.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="employees" />
            </CardContent>
        </Card>
    </div>
</template>
