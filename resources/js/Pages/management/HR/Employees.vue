<script setup>
import { ref, watch } from "vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import {
    Pencil,
    UserCircle,
    Plus,
    Search,
    CheckCircle2,
    AlertCircle,
    Users,
    Briefcase,
    Ban,
    Lock,
} from "lucide-vue-next";

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
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";

const props = defineProps({
    employees: Object,
    departments: Array,
    positions: Array,
    statuses: Array,
    filters: Object,
});

const search = ref(props.filters?.search || "");
const selectedDept = ref(props.filters?.department || "");
const isDialogOpen = ref(false);

const alertStatus = ref({
    show: false,
    title: "",
    message: "",
    variant: "default",
});

const form = useForm({
    id: null,
    name: "",
    status_id: "",
    department_id: "",
    position_id: "", // Added Position
});

// Watch status_id: If system status is Inactive (2), clear selections
watch(
    () => form.status_id,
    (newStatus) => {
        if (newStatus == 2) {
            form.department_id = "";
            form.position_id = "";
        }
    },
);

const updateFilters = () => {
    router.get(
        window.location.pathname,
        {
            search: search.value,
            department: selectedDept.value,
        },
        { preserveState: true, replace: true },
    );
};

watch([search, selectedDept], () => updateFilters());

const openEditModal = (emp) => {
    form.id = emp.id;
    form.name = emp.name;
    form.status_id = emp.status_id;
    form.department_id = emp.department_id;
    form.position_id = emp.position_id;
    isDialogOpen.value = true;
};

const submitUpdate = () => {
    form.post(`/hr/list-employee/update/${form.id}`, {
        onSuccess: () => {
            isDialogOpen.value = false;
            alertStatus.value = {
                show: true,
                title: "Updated",
                message: "Employee records have been updated successfully.",
                variant: "default",
            };
            setTimeout(() => (alertStatus.value.show = false), 5000);
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            alertStatus.value = {
                show: true,
                title: "Error",
                message: firstError || "Failed to update employee.",
                variant: "destructive",
            };
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
                            class="text-4xl font-extrabold text-brand-blue tracking-tight"
                            >Employee Directory</CardTitle
                        >
                        <CardDescription class="text-lg mt-2"
                            >Manage workforce assignments and
                            status.</CardDescription
                        >
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
                <transition name="fade">
                    <Alert
                        v-if="alertStatus.show"
                        :variant="alertStatus.variant"
                        class="mb-6 border-2"
                    >
                        <component
                            :is="
                                alertStatus.variant === 'destructive'
                                    ? AlertCircle
                                    : CheckCircle2
                            "
                            class="h-5 w-5"
                        />
                        <AlertTitle class="font-bold">{{
                            alertStatus.title
                        }}</AlertTitle>
                        <AlertDescription>{{
                            alertStatus.message
                        }}</AlertDescription>
                    </Alert>
                </transition>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead class="font-bold text-slate-700"
                                    >EMPLOYEE</TableHead
                                >
                                <TableHead class="font-bold text-slate-700"
                                    >DEPARTMENT & POSITION</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-700 text-center"
                                    >STATUS</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-700 px-6"
                                    >ACTIONS</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="emp in employees.data"
                                :key="emp.id"
                                class="hover:bg-blue-50/30 transition-colors group"
                            >
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="p-2 bg-blue-50 rounded text-brand-blue"
                                        >
                                            <UserCircle class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <div
                                                class="font-bold text-slate-800"
                                            >
                                                {{ emp.name }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ emp.email }}
                                            </div>
                                        </div>
                                    </div>
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
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="openEditModal(emp)"
                                        class="text-brand-blue hover:bg-blue-100"
                                    >
                                        <Pencil class="w-4 h-4 mr-2" /> Edit
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-[480px]">
                <DialogHeader>
                    <DialogTitle class="text-2xl font-bold text-brand-blue"
                        >Edit Employee</DialogTitle
                    >
                    <DialogDescription
                        >Update assignment details for
                        {{ form.name }}.</DialogDescription
                    >
                </DialogHeader>

                <form @submit.prevent="submitUpdate" class="space-y-5">
                    <div class="space-y-2">
                        <Label
                            class="text-xs font-bold uppercase text-slate-500"
                            >Full Name</Label
                        >
                        <Input
                            v-model="form.name"
                            class="h-12 border-slate-200"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label
                            class="text-xs font-bold uppercase text-slate-500"
                            >System Status</Label
                        >
                        <select
                            v-model="form.status_id"
                            class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                        >
                            <option
                                v-for="s in statuses"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2 relative">
                        <Label
                            class="text-xs font-bold uppercase text-slate-500 flex items-center gap-2"
                        >
                            Department
                            <Ban
                                v-if="form.status_id == 2"
                                class="h-3 w-3 text-red-500"
                            />
                        </Label>
                        <div class="relative">
                            <select
                                v-model="form.department_id"
                                :disabled="form.status_id == 2"
                                class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed outline-none"
                                :required="form.status_id != 2"
                            >
                                <option value="">Select Department</option>
                                <option
                                    v-for="d in departments"
                                    :key="d.id"
                                    :value="d.id"
                                    :disabled="d.status_id == 2"
                                >
                                    {{ d.name }}
                                    {{ d.status_id == 2 ? "(Inactive)" : "" }}
                                </option>
                            </select>
                            <Lock
                                v-if="form.status_id == 2"
                                class="absolute right-8 top-3.5 h-5 w-5 text-slate-400"
                            />
                        </div>
                    </div>

                    <div class="space-y-2 relative">
                        <Label
                            class="text-xs font-bold uppercase text-slate-500 flex items-center gap-2"
                        >
                            Position
                            <Ban
                                v-if="form.status_id == 2"
                                class="h-3 w-3 text-red-500"
                            />
                        </Label>
                        <div class="relative">
                            <select
                                v-model="form.position_id"
                                :disabled="form.status_id == 2"
                                class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed outline-none"
                                :required="form.status_id != 2"
                            >
                                <option value="">Select Position</option>
                                <option
                                    v-for="p in positions"
                                    :key="p.id"
                                    :value="p.id"
                                    :disabled="p.status_id == 2"
                                >
                                    {{ p.name }}
                                    {{ p.status_id == 2 ? "(Inactive)" : "" }}
                                </option>
                            </select>
                            <Lock
                                v-if="form.status_id == 2"
                                class="absolute right-8 top-3.5 h-5 w-5 text-slate-400"
                            />
                        </div>
                    </div>

                    <DialogFooter class="pt-4 gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isDialogOpen = false"
                            class="px-8 h-12 font-bold"
                            >Cancel</Button
                        >
                        <Button
                            type="submit"
                            class="bg-brand-blue px-8 h-12 font-bold shadow-lg"
                            :disabled="form.processing"
                            >Save Updates</Button
                        >
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
