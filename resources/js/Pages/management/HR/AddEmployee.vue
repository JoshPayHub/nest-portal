<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { onMounted, watch, ref, computed } from "vue";
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
    CardFooter,
} from "@/Components/ui/card";
import {
    Save,
    ArrowLeft,
    Briefcase,
    MapPin,
    BadgeCheck,
    Send,
    User,
    Info,
} from "lucide-vue-next";

const props = defineProps({
    employee: Object,
    isEditing: Boolean,
    departments: Array,
    positions: Array,
    userTypes: Array,
    pendingStatus: Object,
});

// Storage key - unique if editing, generic for new onboarding
const STORAGE_KEY = props.isEditing
    ? `edit_employee_${props.employee?.id}`
    : "onboarding_form_draft";

const form = useForm({
    // --- Work Information (Editable/Store fields) ---
    employee_id: props.employee?.employee_id || "",
    username: props.employee?.username || "",
    company_email: props.employee?.company_email || "",
    user_type_id: props.employee?.user_type_id || "",
    department_id: props.employee?.department_id || "",
    position_id: props.employee?.position_id || "",
    status_id: props.employee?.status_id || props.pendingStatus?.id || "",
    employment_status: props.employee?.employment_status || "",
    employment_type: props.employee?.employment_type || "",
    date_hired: props.employee?.date_hired ?? "",
    regularization_date: props.employee?.regularization_date ?? "",
    immediate_supervisor: props.employee?.immediate_supervisor ?? "",
    work_location: props.employee?.work_location || "",
    payroll_group: props.employee?.payroll_group || "",
    leave_pay: props.employee?.leave_pay || 0,

    // --- Personal Profile (Read-Only fields) ---
    profile_photo: props.employee?.profile_photo || null,
    first_name: props.employee?.first_name || "",
    middle_name: props.employee?.middle_name || "",
    last_name: props.employee?.last_name || "",
    suffix: props.employee?.suffix || "",
    gender: props.employee?.gender || "",
    date_birth: props.employee?.date_birth || "",
    civil_status: props.employee?.civil_status || "",
    nationality: props.employee?.nationality || "",

    // --- Contact Information ---
    personal_email: props.employee?.personal_email || "",
    mobile_number: props.employee?.mobile_number || "",
    telephone_number: props.employee?.telephone_number || "",
    present_address: props.employee?.present_address || "",
    permanent_address: props.employee?.permanent_address || "",

    // --- Government Information ---
    sss_number: props.employee?.sss_number || "",
    philhealth_number: props.employee?.philhealth_number || "",
    pagibig_number: props.employee?.pagibig_number || "",
    tin_number: props.employee?.tin_number || "",

    // --- Emergency Information ---
    contact_person: props.employee?.contact_person || "",
    relationship: props.employee?.relationship || "",
    contact_number: props.employee?.contact_number || "",
    address: props.employee?.address || "",

    // --- Attachments ---
    resume: props.employee?.resume || null,
});

const existingPhoto = computed(() => {
    return props.employee?.profile_photo
        ? `/storage/${props.employee.profile_photo}`
        : null;
});

const existingResume = computed(() => {
    return props.employee?.resume ? `/storage/${props.employee.resume}` : null;
});

const preview = ref(null);

// Load draft from localStorage on refresh
onMounted(() => {
    const savedData = localStorage.getItem(STORAGE_KEY);
    if (savedData) {
        try {
            const parsedData = JSON.parse(savedData);
            // Sync saved data into the form object
            Object.assign(form, parsedData);
        } catch (e) {
            console.error("Could not parse saved form data", e);
        }
    }
});

// Watch for changes and save to localStorage
watch(
    () => form.data(),
    (newData) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newData));
    },
    { deep: true },
);

const submit = () => {
    const url = props.isEditing
        ? `/hr/employees/update/${props.employee.id}`
        : "/hr/add-employees/store";

    const method = props.isEditing ? "put" : "post";

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            // Remove from storage only on success
            localStorage.removeItem(STORAGE_KEY);

            if (!props.isEditing) {
                form.reset();
                form.clearErrors();
            }
            toastStore.show(
                `Employee ${props.isEditing ? "updated" : "onboarded"} successfully!`,
                "success",
            );
        },
        onError: () => {
            toastStore.show("Please check the errors in the form.", "error");
        },
    });
};
</script>

<template>
    <div class="p-6 space-y-6">
        <Card class="shadow-sm border-blue-100 max-w-6xl mx-auto">
            <CardHeader class="space-y-4 border-b border-blue-50/50 pb-6">
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer flex items-center gap-1"
                        @click="router.get('/hr/list-employee')"
                    >
                        Employee List
                    </span>
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">
                        {{ isEditing ? "Edit Profile" : "New Onboarding" }}
                    </span>
                </nav>

                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold text-brand-blue flex items-center gap-2"
                    >
                        {{
                            isEditing
                                ? "Update Employee Profile"
                                : "Employee Onboarding"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500">
                        {{
                            isEditing
                                ? "Modify existing employment records and system access."
                                : "Initialize a new employee record and set up their work profile."
                        }}
                    </CardDescription>
                </div>
            </CardHeader>

            <CardContent class="mt-4">
                <form
                    @submit.prevent="submit"
                    id="employeeForm"
                    class="space-y-10"
                >
                    <section class="space-y-5">
                        <div
                            class="flex items-center gap-3 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                        >
                            <BadgeCheck class="w-5 h-5" />
                            <h3>Basic Identification</h3>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    :class="{
                                        'text-red-500': form.errors.employee_id,
                                    }"
                                    >Employee ID</label
                                >
                                <Input
                                    v-model="form.employee_id"
                                    placeholder="EMP-2026-001"
                                    class="bg-white"
                                    :class="{
                                        'border-red-500':
                                            form.errors.employee_id,
                                    }"
                                />
                                <p
                                    v-if="form.errors.employee_id"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.employee_id }}
                                </p>
                            </div>
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    :class="{
                                        'text-red-500': form.errors.username,
                                    }"
                                    >Username</label
                                >
                                <Input
                                    v-model="form.username"
                                    class="bg-white"
                                    placeholder="j.delacruz"
                                    :class="{
                                        'border-red-500': form.errors.username,
                                    }"
                                />
                                <p
                                    v-if="form.errors.username"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.username }}
                                </p>
                            </div>
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    :class="{
                                        'text-red-500':
                                            form.errors.company_email,
                                    }"
                                    >Company Email</label
                                >
                                <Input
                                    v-model="form.company_email"
                                    class="bg-white"
                                    type="email"
                                    placeholder="work@company.com"
                                    :class="{
                                        'border-red-500':
                                            form.errors.company_email,
                                    }"
                                />
                                <p
                                    v-if="form.errors.company_email"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.company_email }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div
                            class="flex items-center gap-2 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                        >
                            <Briefcase class="w-5 h-5" />
                            <h3>Employment Details</h3>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    :class="{
                                        'text-red-500':
                                            form.errors.user_type_id,
                                    }"
                                    >User Type (Role)</label
                                >
                                <select
                                    v-model="form.user_type_id"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none transition-colors"
                                >
                                    <option value="">Select Role</option>
                                    <option
                                        v-for="type in userTypes"
                                        :key="type.id"
                                        :value="type.id"
                                    >
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    :class="{
                                        'text-red-500':
                                            form.errors.department_id,
                                    }"
                                    >Department</label
                                >
                                <select
                                    v-model="form.department_id"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none"
                                >
                                    <option value="">Select Dept</option>
                                    <option
                                        v-for="dept in departments"
                                        :key="dept.id"
                                        :value="dept.id"
                                    >
                                        {{ dept.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    :class="{
                                        'text-red-500': form.errors.position_id,
                                    }"
                                    >Position</label
                                >
                                <select
                                    v-model="form.position_id"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none"
                                >
                                    <option value="">Select Position</option>
                                    <option
                                        v-for="pos in positions"
                                        :key="pos.id"
                                        :value="pos.id"
                                    >
                                        {{ pos.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >Employment Status</label
                                >
                                <select
                                    v-model="form.employment_status"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none"
                                >
                                    <option value="">Select Status</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Probationary">
                                        Probationary
                                    </option>
                                    <option value="Contractual">
                                        Contractual
                                    </option>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >Employment Type</label
                                >
                                <select
                                    v-model="form.employment_type"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none"
                                >
                                    <option value="">Select Type</option>
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                </select>
                            </div>
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >Date Hired</label
                                >
                                <Input
                                    v-model="form.date_hired"
                                    class="bg-white"
                                    type="date"
                                />
                            </div>
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >Regularization Date</label
                                >
                                <Input
                                    v-model="form.regularization_date"
                                    class="bg-white"
                                    type="date"
                                />
                            </div>
                            <div class="flex flex-col">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >Immediate Supervisor</label
                                >
                                <Input
                                    v-model="form.immediate_supervisor"
                                    class="bg-white"
                                    placeholder="Reports to..."
                                />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div
                            class="flex items-center gap-2 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                        >
                            <MapPin class="w-5 h-5" />
                            <h3>Work Settings & Payroll</h3>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div class="space-y-2">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >Work Location</label
                                >
                                <Input
                                    v-model="form.work_location"
                                    placeholder="Office Location"
                                    class="bg-white"
                                />
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >Payroll Group</label
                                >
                                <Input
                                    v-model="form.payroll_group"
                                    placeholder="e.g. Weekly"
                                    class="bg-white"
                                />
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >Leave Pay Credits</label
                                >
                                <Input
                                    v-model="form.leave_pay"
                                    type="number"
                                    step="0.5"
                                    class="bg-white"
                                />
                            </div>
                        </div>
                    </section>
                </form>
            </CardContent>

            <CardFooter
                class="flex items-center justify-between py-6 border-t border-b border-slate-100 bg-slate-50/20"
            >
                <Button
                    v-if="!isEditing"
                    type="button"
                    variant="secondary"
                    @click="form.reset()"
                    class="text-slate-500"
                >
                    Reset Changes
                </Button>
                <div v-else></div>

                <div class="flex gap-3">
                    <Button
                        form="employeeForm"
                        type="submit"
                        class="bg-brand-blue text-white shadow-md"
                        :disabled="form.processing"
                    >
                        <Save v-if="isEditing" class="mr-2 w-4 h-4" />
                        <Send v-else class="mr-2 w-4 h-4" />

                        {{
                            form.processing
                                ? "Saving..."
                                : isEditing
                                  ? "Update Profile"
                                  : "Onboard Employee"
                        }}
                    </Button>
                </div>
            </CardFooter>

            <div class="px-6 pb-8">
                <section class="space-y-8">
                    <div
                        v-if="form.status_id == 4"
                        class="mb-6 flex items-start gap-4 p-4 bg-amber-50 border border-amber-200 rounded-xl"
                    >
                        <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                            <Info class="w-5 h-5" />
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-amber-900">
                                Personal Information
                            </h5>
                            <p class="text-sm text-amber-700 leading-relaxed">
                                These fields are currently empty. Once the
                                employee completes their onboarding profile,
                                their personal details, government IDs, and
                                contact information will automatically appear
                                here.
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-2 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                    >
                        <User class="w-5 h-5" />
                        <h3>Employee Personal Record (Read-Only)</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="w-full flex items-center gap-4 mb-10">
                            <div
                                class="w-28 h-28 rounded-full bg-gray-100 overflow-hidden border-2 grid place-items-center"
                            >
                                <img
                                    v-if="preview || existingPhoto"
                                    :src="preview || existingPhoto"
                                    class="w-full h-full object-cover"
                                    alt="Employee Profile"
                                />

                                <User v-else class="text-gray-400 w-12 h-12" />
                            </div>
                            <p class="text-xs text-slate-400 italic">
                                User Profile Image
                            </p>
                        </div>

                        <section class="space-y-5">
                            <h4
                                class="text-xs font-semibold text-slate-500 uppercase"
                            >
                                Basic Profile
                            </h4>
                            <div
                                class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                            >
                                <div
                                    v-for="field in [
                                        {
                                            key: 'first_name',
                                            label: 'First Name',
                                        },
                                        {
                                            key: 'middle_name',
                                            label: 'Middle Name',
                                        },
                                        {
                                            key: 'last_name',
                                            label: 'Last Name',
                                        },
                                        { key: 'suffix', label: 'Suffix' },
                                        { key: 'gender', label: 'Gender' },
                                        {
                                            key: 'date_birth',
                                            label: 'Date of Birth',
                                            type: 'date',
                                        },
                                        {
                                            key: 'civil_status',
                                            label: 'Civil Status',
                                        },
                                        {
                                            key: 'nationality',
                                            label: 'Nationality',
                                        },
                                    ]"
                                    :key="field.key"
                                    class="flex flex-col"
                                >
                                    <label
                                        class="text-xs font-semibold mb-1 text-gray-500"
                                        >{{ field.label }}</label
                                    >
                                    <Input
                                        v-model="form[field.key]"
                                        :type="field.type || 'text'"
                                        :placeholder="field.label"
                                        readonly
                                        disabled
                                    />
                                </div>
                            </div>
                        </section>
                    </div>

                    <section class="space-y-5">
                        <h4
                            class="text-xs font-semibold text-slate-500 uppercase"
                        >
                            Contact Details
                        </h4>
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div
                                v-for="field in [
                                    {
                                        key: 'personal_email',
                                        label: 'Personal Email',
                                    },
                                    {
                                        key: 'mobile_number',
                                        label: 'Mobile Number',
                                    },
                                    {
                                        key: 'telephone_number',
                                        label: 'Telephone',
                                    },
                                    {
                                        key: 'present_address',
                                        label: 'Present Address',
                                        span: 'md:col-span-3',
                                    },
                                    {
                                        key: 'permanent_address',
                                        label: 'Permanent Address',
                                        span: 'md:col-span-3',
                                    },
                                ]"
                                :key="field.key"
                                :class="['flex flex-col', field.span]"
                            >
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >{{ field.label }}</label
                                >
                                <Input
                                    v-model="form[field.key]"
                                    :placeholder="field.label"
                                    readonly
                                    disabled
                                />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <h4
                            class="text-xs font-semibold text-slate-500 uppercase"
                        >
                            Government IDs
                        </h4>
                        <div
                            class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div
                                v-for="field in [
                                    { key: 'sss_number', label: 'SSS Number' },
                                    {
                                        key: 'philhealth_number',
                                        label: 'PhilHealth Number',
                                    },
                                    {
                                        key: 'pagibig_number',
                                        label: 'Pag-IBIG Number',
                                    },
                                    { key: 'tin_number', label: 'TIN Number' },
                                ]"
                                :key="field.key"
                                class="flex flex-col"
                            >
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >{{ field.label }}</label
                                >
                                <Input
                                    v-model="form[field.key]"
                                    :placeholder="field.label"
                                    readonly
                                    disabled
                                />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <h4
                            class="text-xs font-semibold text-slate-500 uppercase"
                        >
                            Emergency Information
                        </h4>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div
                                v-for="field in [
                                    {
                                        key: 'contact_person',
                                        label: 'Contact Person',
                                    },
                                    {
                                        key: 'relationship',
                                        label: 'Relationship',
                                    },
                                    {
                                        key: 'contact_number',
                                        label: 'Contact Number',
                                    },
                                    { key: 'address', label: 'Address' },
                                ]"
                                :key="field.key"
                                class="flex flex-col"
                            >
                                <label
                                    class="text-xs font-semibold mb-1 text-gray-500"
                                    >{{ field.label }}</label
                                >
                                <Input
                                    v-model="form[field.key]"
                                    :placeholder="field.label"
                                    readonly
                                    disabled
                                />
                            </div>
                        </div>
                    </section>

                    <div class="space-y-4">
                        <h4
                            class="text-[12px] font-semibold text-slate-500 uppercase tracking-wider"
                        >
                            Attachments
                        </h4>
                        <div
                            class="bg-slate-50/50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600 font-medium"
                                    >Employee Resume / CV</span
                                >

                                <a
                                    v-if="existingResume"
                                    :href="existingResume"
                                    target="_blank"
                                    class="text-brand-blue text-sm font-bold hover:underline"
                                >
                                    View Current Resume
                                    <ExternalLink class="w-3 h-3" />
                                </a>

                                <span
                                    v-else
                                    class="text-xs text-slate-400 italic"
                                    >No resume uploaded</span
                                >
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </Card>
    </div>
</template>
