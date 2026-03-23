<script setup>
import { ref, watch, computed } from "vue";
import { useForm, router, Link } from "@inertiajs/vue3";
import { toastStore } from "@/stores/toast"; // Integrated Toast
import {
    Pencil,
    Plus,
    Search,
    FileText,
    Check,
    ChevronsUpDown,
    Users,
    Megaphone,
    ShieldCheck,
} from "lucide-vue-next";

// UI Components
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { Checkbox } from "@/Components/ui/checkbox";
import { Badge } from "@/Components/ui/badge";
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
    DialogDescription,
} from "@/Components/ui/dialog";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/Components/ui/command";
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
    departments: Array,
    statuses: Array,
    filters: Object,
});

const isDialogOpen = ref(false);
const isPopoverOpen = ref(false);
const editMode = ref(false);
const searchQuery = ref(props.filters.search || "");
const activeTab = ref(props.filters.tab || "all");
const selectedStatus = ref(props.filters.status || "");

const form = useForm({
    id: null,
    types: "announcements",
    title: "",
    description: "",
    status_id: "",
    selected_departments: [],
});

// Manual trigger for filtering instead of auto-watch to prevent unwanted reloads
const handleFilter = () => {
    router.get(
        "/hr/announcements-policies",
        {
            search: searchQuery.value,
            tab: activeTab.value,
            status: selectedStatus.value,
        },
        { preserveState: true, replace: true },
    );
};

const openModal = (type, item = null) => {
    editMode.value = !!item;
    form.clearErrors();
    if (item) {
        form.id = item.id;
        form.types = item.types;
        form.title = item.title;
        form.description = item.description;
        form.status_id = item.status_id;

        // This ensures "All Departments" (null) results in an empty array []
        form.selected_departments = (item.filters || [])
            .map((f) => f.department_id)
            .filter((id) => id !== null); // Remove nulls so length is 0
    } else {
        form.reset();
        form.types = type === "all" ? "announcements" : type;
        form.status_id = props.statuses[0]?.id || "";
    }
    isDialogOpen.value = true;
};

const submitForm = () => {
    const url = editMode.value
        ? `/hr/announcements-policies/${form.id}`
        : "/hr/announcements-policies";

    form.post(url, {
        onSuccess: () => {
            isDialogOpen.value = false;
            form.reset();

            // Trigger Toast Success
            toastStore.show(
                editMode.value
                    ? "Record updated successfully"
                    : "Record created successfully.",
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

const toggleDepartment = (id) => {
    // Ensure we aren't adding null to the array
    if (id === null) return;

    const index = form.selected_departments.indexOf(id);
    if (index > -1) {
        form.selected_departments.splice(index, 1);
    } else {
        form.selected_departments.push(id);
    }
};

const getTableFilterLabel = (item) => {
    if (!item.filters || item.filters.length === 0) return "All Departments";
    const depts = [
        ...new Set(item.filters.map((f) => f.department?.name).filter(Boolean)),
    ];
    return depts.length === 0 ? "All Departments" : depts.join(", ");
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
        <Card class="shadow-sm border-blue-100 max-w-7xl mx-auto">
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
                            Manage internal communications and workforce
                            documents.
                        </CardDescription>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            @click="openModal('announcements')"
                            class="bg-brand-blue hover:bg-brand-blue/90 text-white h-12 px-8 font-bold shadow-sm"
                        >
                            <Plus class="w-5 h-5 mr-2" /> New Announcement
                        </Button>
                        <Button
                            @click="openModal('policies')"
                            variant="outline"
                            class="border-2 border-brand-blue text-brand-blue hover:bg-brand-blue hover:text-white h-12 px-8 font-bold shadow-sm"
                        >
                            <Plus class="w-5 h-5 mr-2" /> New Policy
                        </Button>
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
                        <select
                            v-model="selectedStatus"
                            @change="handleFilter"
                            class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium"
                        >
                            <option value="">All Statuses</option>
                            <option
                                v-for="s in statuses"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs w-[120px]"
                                    >Type</TableHead
                                >

                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Date</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Title</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Target Departments</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                    >Action</TableHead
                                >
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
                                                >{{ item.types }}</span
                                            >
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="font-bold text-slate-800"
                                        >{{ item.created_at }}</TableCell
                                    >
                                    <TableCell
                                        class="font-bold text-slate-800"
                                        >{{ item.title }}</TableCell
                                    >

                                    <TableCell>
                                        <div
                                            class="flex items-center gap-1.5 text-slate-500 text-sm italic"
                                        >
                                            <Users class="w-3.5 h-3.5" />
                                            {{ getTableFilterLabel(item) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase',
                                                item.status_name?.toLowerCase() ===
                                                'active'
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-red-100 text-red-700',
                                            ]"
                                        >
                                            {{ item.status_name }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right px-6">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openModal(item.types, item)"
                                            class="text-amber-600 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    colspan="6"
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
                        {{ editMode ? "Update" : "Create New" }}
                        {{
                            form.types === "announcements"
                                ? "Announcement"
                                : "Policy"
                        }}
                    </DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="space-y-5 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="font-bold text-xs uppercase"
                                >Category</Label
                            >
                            <select
                                v-model="form.types"
                                class="w-full border rounded-md h-11 px-3 text-sm"
                            >
                                <option value="announcements">
                                    Announcement
                                </option>
                                <option value="policies">Policy</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <Label class="font-bold text-xs uppercase"
                                >Status</Label
                            >
                            <select
                                v-model="form.status_id"
                                class="w-full border rounded-md h-11 px-3 text-sm"
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
                    </div>
                    <div class="space-y-2">
                        <Label class="font-bold text-xs uppercase"
                            >Document Title</Label
                        >
                        <Input
                            v-model="form.title"
                            placeholder="e.g. Annual Year-End Party"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label class="font-bold text-xs uppercase"
                            >Description</Label
                        >
                        <Textarea
                            v-model="form.description"
                            class="min-h-[100px]"
                            placeholder="Enter details..."
                        />
                    </div>
                    <div class="space-y-2">
                        <Label class="font-bold text-xs uppercase"
                            >Target Departments</Label
                        >
                        <Popover v-model:open="isPopoverOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    class="w-full justify-between min-h-[44px] h-auto py-2 font-normal"
                                >
                                    <div
                                        v-if="
                                            form.selected_departments.length > 0
                                        "
                                        class="flex flex-wrap gap-1"
                                    >
                                        <Badge
                                            v-for="id in form.selected_departments"
                                            :key="id"
                                            variant="secondary"
                                            class="text-[10px]"
                                        >
                                            {{
                                                departments.find(
                                                    (d) => d.id === id,
                                                )?.name
                                            }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-slate-500"
                                        >All Departments</span
                                    >
                                    <ChevronsUpDown
                                        class="ml-2 h-4 w-4 shrink-0 opacity-50"
                                    />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-[300px] p-0">
                                <Command>
                                    <CommandInput
                                        placeholder="Search departments..."
                                    />
                                    <CommandList>
                                        <CommandEmpty
                                            >No department found.</CommandEmpty
                                        >
                                        <CommandGroup>
                                            <CommandItem
                                                value="all-departments"
                                                @select="
                                                    form.selected_departments =
                                                        []
                                                "
                                                class="font-boldborder-b mb-1"
                                            >
                                                <div
                                                    :class="[
                                                        'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                                                        form
                                                            .selected_departments
                                                            .length === 0
                                                            ? 'bg-primary text-primary-foreground'
                                                            : 'opacity-50',
                                                    ]"
                                                >
                                                    <Check
                                                        v-if="
                                                            form
                                                                .selected_departments
                                                                .length === 0
                                                        "
                                                        class="h-3 w-3"
                                                    />
                                                </div>
                                                All Departments
                                            </CommandItem>

                                            <CommandItem
                                                v-for="dept in departments"
                                                :key="dept.id"
                                                :value="dept.name"
                                                @select="
                                                    toggleDepartment(dept.id)
                                                "
                                            >
                                                <div
                                                    :class="[
                                                        'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                                                        form.selected_departments.includes(
                                                            dept.id,
                                                        )
                                                            ? 'bg-primary text-primary-foreground'
                                                            : 'opacity-50',
                                                    ]"
                                                >
                                                    <Check
                                                        v-if="
                                                            form.selected_departments.includes(
                                                                dept.id,
                                                            )
                                                        "
                                                        class="h-3 w-3"
                                                    />
                                                </div>
                                                <span>{{ dept.name }}</span>
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="ghost"
                            @click="isDialogOpen = false"
                            >Cancel</Button
                        >
                        <Button
                            type="submit"
                            class="bg-brand-blue"
                            :disabled="form.processing"
                        >
                            {{ editMode ? "Save Changes" : "Create Record" }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
