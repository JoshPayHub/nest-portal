<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import {
    Pencil,
    Building2,
    Plus,
    Search,
    CheckCircle2,
    AlertCircle,
    X,
} from "lucide-vue-next";

// Shadcn UI Components
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
    positions: Array,
    statuses: Array,
});

// State
const isDialogOpen = ref(false);
const isEditing = ref(false);
const currentEditId = ref(null);
const searchQuery = ref("");

const alertStatus = ref({
    show: false,
    title: "",
    message: "",
    variant: "default",
});

const form = useForm({
    name: "",
    status_id: 1,
});

// Filtered Positions
const filteredPositions = computed(() => {
    return props.positions.filter((dept) =>
        dept.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const openCreateModal = () => {
    isEditing.value = false;
    currentEditId.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEditModal = (dept) => {
    isEditing.value = true;
    currentEditId.value = dept.id;
    form.name = dept.name;
    form.status_id = dept.status_id;
    isDialogOpen.value = true;
};

const submit = () => {
    const url = isEditing.value
        ? `/hr/position/update/${currentEditId.value}`
        : "/hr/position/store";

    form.post(url, {
        onSuccess: () => {
            isDialogOpen.value = false;
            form.reset();
            alertStatus.value = {
                show: true,
                title: "Action Successful",
                message: isEditing.value
                    ? "Position details updated."
                    : "New position added to system.",
                variant: "default",
            };
            setTimeout(() => (alertStatus.value.show = false), 5000);
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            alertStatus.value = {
                show: true,
                title: "Error Occurred",
                message: firstError || "Please check your inputs.",
                variant: "destructive",
            };
        },
    });
};
</script>

<template>
    <div class="p-6 space-y-8">
        <Card class="shadow-sm border-blue-100 max-w-6xl mx-auto">
            <CardHeader class="border-b border-slate-50">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-4xl font-extrabold text-brand-blue tracking-tight"
                        >
                            Position Management
                        </CardTitle>
                        <CardDescription class="text-lg mt-2">
                            Organize company structures and manage position
                            availability.
                        </CardDescription>
                    </div>
                    <Button
                        @click="openCreateModal"
                        class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 text-base font-bold shadow-md transition-all active:scale-95"
                    >
                        <Plus class="w-5 h-5 mr-2" /> Add New Position
                    </Button>
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

                <div
                    class="flex items-center space-x-2 bg-slate-50 rounded-lg border border-slate-200 px-4 h-12 mb-6"
                >
                    <Search class="w-5 h-5 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        placeholder="Filter by position name..."
                        class="flex-1 border-none focus:ring-0 text-sm outline-none bg-transparent"
                    />
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-700 w-[100px]"
                                    >ID</TableHead
                                >
                                <TableHead class="font-bold text-slate-700"
                                    >POSITION NAME</TableHead
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
                                v-for="dept in filteredPositions"
                                :key="dept.id"
                                class="hover:bg-blue-50/30 transition-colors group"
                            >
                                <TableCell
                                    class="font-mono text-slate-500 text-xs"
                                    >#{{ dept.id }}</TableCell
                                >
                                <TableCell class="font-semibold text-slate-800">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="p-2 bg-blue-50 rounded text-brand-blue"
                                        >
                                            <Building2 class="w-4 h-4" />
                                        </div>
                                        {{ dept.name }}
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider',
                                            dept.status_id === 1
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        {{ dept.status_name }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right px-6">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="openEditModal(dept)"
                                        class="text-brand-blue hover:bg-blue-100"
                                    >
                                        <Pencil class="w-4 h-4 mr-2" /> Edit
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="filteredPositions.length === 0">
                                <TableCell
                                    colspan="4"
                                    class="h-32 text-center text-slate-400 italic"
                                >
                                    No records found matching your search.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent
                class="sm:max-w-[450px] overflow-hidden border-none shadow-2xl"
            >
                <DialogHeader>
                    <DialogTitle class="text-2xl font-bold text-brand-blue">
                        {{ isEditing ? "Update Position" : "New Position" }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            isEditing
                                ? "Modify the position settings below."
                                : "Enter details to create a new position."
                        }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label
                            for="name"
                            class="text-sm font-bold text-slate-700 uppercase tracking-tight"
                            >Position Name</Label
                        >
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="e.g., Head"
                            class="h-12 border-slate-200 focus:ring-brand-blue"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label
                            for="status"
                            class="text-sm font-bold text-slate-700 uppercase tracking-tight"
                            >Assign Status</Label
                        >
                        <select
                            id="status"
                            v-model="form.status_id"
                            class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none transition-all"
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

                    <DialogFooter class="pt-4 gap-2">
                        <Button
                            type="button"
                            variant="secondary"
                            @click="isDialogOpen = false"
                            class="px-8 h-12 font-bold"
                        >
                            Cancel
                        </Button>
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

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
