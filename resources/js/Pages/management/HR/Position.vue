<script setup>
import { ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import {
    Pencil,
    Building2,
    Plus,
    Search,
    CheckCircle2,
    AlertCircle,
    FileText,
} from "lucide-vue-next";

// Import Toast Store
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
// Alert imports removed as they are no longer used for notifications
import Pagination from "@/Components/Pagination/Index.vue";

const props = defineProps({
    positions: Object,
    statuses: Array,
    filters: Object,
});

// State
const isDialogOpen = ref(false);
const isEditing = ref(false);
const currentEditId = ref(null);
const search = ref(props.filters.search || "");

const form = useForm({
    name: "",
    status_id: 1,
});

// Server-side search watcher
watch(search, (value) => {
    router.get(
        "/hr/position",
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

const openEditModal = (pos) => {
    isEditing.value = true;
    currentEditId.value = pos.id;
    form.name = pos.name;
    form.status_id = pos.status_id;
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

            // Trigger Toast Success
            toastStore.show(
                isEditing.value
                    ? "Position details updated."
                    : "New position added to system.",
                "success",
            );
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];

            // Trigger Toast Error
            toastStore.show(
                firstError || "Please check your inputs.",
                "danger", // or "destructive" depending on your store's accepted types
            );
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
                        >
                            Position Management
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Organize company structures and manage position
                            availability.
                        </CardDescription>
                    </div>
                    <Button
                        @click="openCreateModal"
                        class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                    >
                        <Plus class="w-5 h-5 mr-2" /> Add New Position
                    </Button>
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
                            placeholder="Filter by position name..."
                            class="pl-10 h-12"
                        />
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider w-[100px]"
                                    >ID</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Position Name</TableHead
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
                            <template v-if="positions.data.length > 0">
                                <TableRow
                                    v-for="pos in positions.data"
                                    :key="pos.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
                                >
                                    <TableCell
                                        class="font-mono text-slate-500 text-xs"
                                        >#{{ pos.id }}</TableCell
                                    >
                                    <TableCell
                                        class="font-semibold text-slate-800"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="p-2 bg-blue-50 rounded text-brand-blue"
                                            >
                                                <Building2 class="w-4 h-4" />
                                            </div>
                                            {{ pos.name }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase',
                                                pos.status_id === 1
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-red-100 text-red-700',
                                            ]"
                                        >
                                            {{ pos.status_name }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right px-6">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openEditModal(pos)"
                                            class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    colspan="4"
                                    class="text-center text-slate-500 py-10 italic"
                                >
                                    <FileText
                                        class="w-10 h-10 mx-auto mb-2 opacity-20"
                                    />
                                    No records found matching your criteria.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="positions" />
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
                            placeholder="e.g., Senior Web Developer"
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
                            class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none transition-all cursor-pointer"
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
