<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import {
    Pencil,
    Plus,
    Megaphone,
    FileText,
    CheckCircle2,
    AlertCircle,
    Calendar,
    Building2,
} from "lucide-vue-next";

// UI Components
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";

// ShadCN Table & Pagination
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead,
} from "@/Components/ui/table";

import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationPrevious,
    PaginationNext,
    PaginationEllipsis,
} from "@/Components/ui/pagination";

const props = defineProps({
    announcements: Array,
    policies: Array,
    departments: Array,
    statuses: Array,
});

// Modal state
const isDialogOpen = ref(false);
const editMode = ref(false);
const form = useForm({
    id: null,
    type: "announcement",
    title: "",
    description: "",
    department_id: "",
    status_id: "",
});
const alertStatus = ref({
    show: false,
    title: "",
    message: "",
    variant: "default",
});

// Search state
const searchAnnouncement = ref("");
const searchPolicy = ref("");

// Pagination state
const announcementsPage = ref(1);
const policiesPage = ref(1);
const pageSize = 5;

// Computed filtered + paginated data
const filteredAnnouncements = computed(() => {
    const filtered = props.announcements.filter(
        (item) =>
            item.title
                .toLowerCase()
                .includes(searchAnnouncement.value.toLowerCase()) ||
            item.department?.name
                .toLowerCase()
                .includes(searchAnnouncement.value.toLowerCase()),
    );
    const start = (announcementsPage.value - 1) * pageSize;
    return filtered.slice(start, start + pageSize);
});
const filteredPolicies = computed(() => {
    const filtered = props.policies.filter(
        (item) =>
            item.title
                .toLowerCase()
                .includes(searchPolicy.value.toLowerCase()) ||
            item.department?.name
                .toLowerCase()
                .includes(searchPolicy.value.toLowerCase()),
    );
    const start = (policiesPage.value - 1) * pageSize;
    return filtered.slice(start, start + pageSize);
});

// Modal functions
const openModal = (type, item = null) => {
    editMode.value = !!item;
    form.clearErrors();
    if (item) {
        form.id = item.id;
        form.type = type;
        form.title = item.title;
        form.description = item.description;
        form.department_id = item.department_id;
        form.status_id = item.status_id;
    } else {
        form.reset();
        form.type = type;
    }
    isDialogOpen.value = true;
};

// Submit function
const submitForm = () => {
    const url = editMode.value
        ? `/hr/announcement-and-policy/update/${form.id}`
        : `/hr/announcement-and-policy/store`;

    if (editMode.value) {
        form.put(url, {
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
                showAlert(
                    "Updated",
                    `${form.type.charAt(0).toUpperCase() + form.type.slice(1)} has been updated successfully.`,
                    "default",
                );
            },
            onError: (errors) => handleFormErrors(errors),
        });
    } else {
        form.post(url, {
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
                showAlert(
                    "Created",
                    `${form.type.charAt(0).toUpperCase() + form.type.slice(1)} has been created successfully.`,
                    "default",
                );
            },
            onError: (errors) => handleFormErrors(errors),
        });
    }
};

// Handle errors
const handleFormErrors = (errors) => {
    const firstError = Object.values(errors)[0];
    showAlert(
        "Error",
        firstError || "Please check your inputs.",
        "destructive",
    );
};

// Alert
const showAlert = (title, message, variant) => {
    alertStatus.value = { show: true, title, message, variant };
    setTimeout(() => (alertStatus.value.show = false), 5000);
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

// Pagination helpers
const totalAnnouncementPages = computed(() =>
    Math.ceil(
        props.announcements.filter(
            (item) =>
                item.title
                    .toLowerCase()
                    .includes(searchAnnouncement.value.toLowerCase()) ||
                item.department?.name
                    .toLowerCase()
                    .includes(searchAnnouncement.value.toLowerCase()),
        ).length / pageSize,
    ),
);

const totalPolicyPages = computed(() =>
    Math.ceil(
        props.policies.filter(
            (item) =>
                item.title
                    .toLowerCase()
                    .includes(searchPolicy.value.toLowerCase()) ||
                item.department?.name
                    .toLowerCase()
                    .includes(searchPolicy.value.toLowerCase()),
        ).length / pageSize,
    ),
);
</script>

<template>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div
            class="w-full bg-white rounded-2xl p-8 shadow-sm border border-blue-100 flex flex-col md:flex-row justify-between items-center gap-4"
        >
            <div>
                <h1
                    class="text-4xl font-extrabold text-brand-blue tracking-tight"
                >
                    Announcements & Policies
                </h1>
                <p class="text-slate-500 mt-2 text-lg">
                    Manage company updates and official documentation.
                </p>
            </div>
        </div>

        <!-- Alert -->
        <transition name="fade">
            <Alert
                v-if="alertStatus.show"
                :variant="alertStatus.variant"
                class="border-2"
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
                <AlertDescription>{{ alertStatus.message }}</AlertDescription>
            </Alert>
        </transition>

        <!-- Tables -->
        <!-- Announcements Table -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-blue-100">
            <div class="flex justify-between items-center mb-4">
                <h2
                    class="text-xl font-bold text-slate-800 flex items-center gap-2"
                >
                    <Megaphone class="w-5 h-5 text-blue-500" /> Latest
                    Announcements
                </h2>
                <Button
                    @click="openModal('announcement')"
                    variant="outline"
                    class="h-10 px-4"
                >
                    <Plus class="w-4 h-4 mr-2" /> Add Announcement
                </Button>
            </div>
            <Input
                v-model="searchAnnouncement"
                placeholder="Search announcements..."
                class="mb-3"
            />

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Title</TableHead>
                        <TableHead>Department</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="row in filteredAnnouncements"
                        :key="row.id"
                    >
                        <TableCell>{{ row.title }}</TableCell>
                        <TableCell>{{ row.department?.name }}</TableCell>
                        <TableCell>{{ formatDate(row.created_at) }}</TableCell>
                        <TableCell>
                            <span
                                :class="
                                    row.status?.name === 'Published'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700'
                                "
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                            >
                                {{ row.status?.name }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="openModal('announcement', row)"
                                class="text-brand-blue"
                            >
                                <Pencil class="w-4 h-4 mr-1" /> Edit
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Pagination -->
            <Pagination
                v-if="totalAnnouncementPages > 1"
                v-model="announcementsPage"
                class="mt-4 justify-end"
            >
                <PaginationPrevious />
                <PaginationContent>
                    <PaginationItem
                        v-for="p in totalAnnouncementPages"
                        :key="p"
                        :value="p"
                    />
                </PaginationContent>
                <PaginationNext />
            </Pagination>
        </div>

        <!-- Policies Table -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-blue-100">
            <div class="flex justify-between items-center mb-4">
                <h2
                    class="text-xl font-bold text-slate-800 flex items-center gap-2"
                >
                    <FileText class="w-5 h-5 text-blue-500" /> Company Policies
                </h2>
                <Button
                    @click="openModal('policy')"
                    variant="outline"
                    class="h-10 px-4"
                >
                    <Plus class="w-4 h-4 mr-2" /> Add Policy
                </Button>
            </div>
            <Input
                v-model="searchPolicy"
                placeholder="Search policies..."
                class="mb-3"
            />

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Title</TableHead>
                        <TableHead>Department</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in filteredPolicies" :key="row.id">
                        <TableCell>{{ row.title }}</TableCell>
                        <TableCell>{{ row.department?.name }}</TableCell>
                        <TableCell>{{ formatDate(row.created_at) }}</TableCell>
                        <TableCell>
                            <span
                                :class="
                                    row.status?.name === 'Published'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700'
                                "
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                            >
                                {{ row.status?.name }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="openModal('policy', row)"
                                class="text-brand-blue"
                            >
                                <Pencil class="w-4 h-4 mr-1" /> Edit
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Pagination -->
            <Pagination
                v-if="totalPolicyPages > 1"
                v-model="policiesPage"
                class="mt-4 justify-end"
            >
                <PaginationPrevious />
                <PaginationContent>
                    <PaginationItem
                        v-for="p in totalPolicyPages"
                        :key="p"
                        :value="p"
                    />
                </PaginationContent>
                <PaginationNext />
            </Pagination>
        </div>

        <!-- Modal -->
        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent
                class="sm:max-w-[500px] p-0 overflow-hidden border-none shadow-2xl"
            >
                <DialogHeader class="p-8 bg-slate-50 border-b border-slate-100">
                    <DialogTitle
                        class="text-2xl font-black text-brand-blue uppercase tracking-tight"
                    >
                        {{ editMode ? "Edit" : "Create" }} {{ form.type }}
                    </DialogTitle>
                    <DialogDescription class="text-slate-500">
                        Fill in the details below to
                        {{ editMode ? "update" : "publish" }} this
                        {{ form.type }}.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="p-8 space-y-5">
                    <div class="space-y-2">
                        <Label
                            class="text-xs font-bold uppercase text-slate-500 tracking-widest"
                            >Document Title</Label
                        >
                        <Input
                            v-model="form.title"
                            placeholder="e.g. Q1 Operations Update"
                            class="h-12 border-slate-200 focus:ring-brand-blue"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label
                                class="text-xs font-bold uppercase text-slate-500 tracking-widest"
                                >Department</Label
                            >
                            <select
                                v-model="form.department_id"
                                class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                                required
                            >
                                <option value="" disabled>Select Dept</option>
                                <option
                                    v-for="dept in departments"
                                    :key="dept.id"
                                    :value="dept.id"
                                >
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <Label
                                class="text-xs font-bold uppercase text-slate-500 tracking-widest"
                                >Status</Label
                            >
                            <select
                                v-model="form.status_id"
                                class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                                required
                            >
                                <option value="" disabled>Select Status</option>
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
                        <Label
                            class="text-xs font-bold uppercase text-slate-500 tracking-widest"
                            >Content / Description</Label
                        >
                        <Textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Enter the full details here..."
                            class="border-slate-200 focus:ring-brand-blue"
                        />
                    </div>
                    <DialogFooter class="pt-6">
                        <Button
                            type="button"
                            variant="ghost"
                            @click="isDialogOpen = false"
                            class="font-bold text-slate-500"
                            >Cancel</Button
                        >
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-brand-blue px-10 h-12 font-bold shadow-lg shadow-blue-200"
                        >
                            {{
                                form.processing
                                    ? "Saving..."
                                    : editMode
                                      ? "Update Changes"
                                      : "Save & Publish"
                            }}
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
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
