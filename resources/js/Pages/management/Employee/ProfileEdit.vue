<script setup>
import { ref, onMounted, computed } from "vue"; // ✅ added computed
import { useForm, usePage, router } from "@inertiajs/vue3";
import { toastStore } from "@/stores/toast";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from "@/Components/ui/card";
import {
    User,
    Save,
    FileText,
    AlertCircle,
    Info,
    CheckCircle,
    ExternalLink,
    Lock,
    Eye,
    EyeOff,
} from "lucide-vue-next";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";

const page = usePage();
const employee = computed(() => page.props.employee);

const auth_user_type_id = page.props.auth_user_type_id;

const routeMap = {
    2: "employee",
    3: "head",
};

const baseRoute = routeMap[auth_user_type_id];

const form = useForm({
    _method: "put",

    // employee edit
    status_id: employee.value.status_id,
    first_name: employee.value.first_name ?? "",
    middle_name: employee.value.middle_name ?? "",
    last_name: employee.value.last_name ?? "",
    suffix: employee.value.suffix ?? "",
    gender: employee.value.gender ?? "",
    date_birth: employee.value.date_birth ?? "",
    civil_status: employee.value.civil_status ?? "",
    nationality: employee.value.nationality ?? "",
    personal_email: employee.value.personal_email ?? "",
    mobile_number: employee.value.mobile_number ?? "",
    telephone_number: employee.value.telephone_number ?? "",
    present_address: employee.value.present_address ?? "",
    permanent_address: employee.value.permanent_address ?? "",
    sss_number: employee.value.sss_number ?? "",
    philhealth_number: employee.value.philhealth_number ?? "",
    pagibig_number: employee.value.pagibig_number ?? "",
    tin_number: employee.value.tin_number ?? "",
    contact_person: employee.value.contact_person ?? "",
    relationship: employee.value.relationship ?? "",
    contact_number: employee.value.contact_number ?? "",
    address: employee.value.address ?? "",
    resume: null,
    profile_photo: null,

    // view only
    department_name: employee.value.department?.name || "N/A",
    position_name: employee.value.position?.name || "N/A",
    department_id: employee.value.department_id,
    position_id: employee.value.position_id,

    employee_id: employee.value.employee_id,
    username: employee.value.username,
    company_email: employee.value.company_email,
    date_hired: employee.value.date_hired,
    regularization_date: employee.value.regularization_date,
    immediate_supervisor: employee.value.immediate_supervisor,
    work_location: employee.value.work_location,
    payroll_group: employee.value.payroll_group,
    leave_pay: employee.value.leave_pay,
    employment_status: employee.value.employment_status,
    employment_type: employee.value.employment_type,
});

const preview = ref(null);

// ✅ FIX: now reactive (NO MORE refresh issue)
const existingPhoto = computed(() =>
    employee.value?.profile_photo
        ? `/storage/app/public/${employee.value.profile_photo}`
        : null,
);

const existingResume = computed(() =>
    employee.value?.resume
        ? `/storage/app/public/${employee.value.resume}`
        : null,
);

const loading = ref(false);

const previewImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.profile_photo = file;
    preview.value = URL.createObjectURL(file);

    clearError("profile_photo");
};

const handleResume = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.resume = file;
    clearError("resume");
};

// 1. Trigger the reload on success
const submit = () => {
    loading.value = true;

    form.transform((data) => {
        const fd = new FormData();

        Object.keys(data).forEach((key) => {
            if (data[key] !== null && data[key] !== undefined) {
                fd.append(key, data[key]);
            }
        });

        return fd;
    }).post(`/${baseRoute}/profile/update`, {
        forceFormData: true, // 🔥 IMPORTANT FIX
        preserveScroll: true,

        onSuccess: () => {
            toastStore.show("Profile updated successfully!", "success");

            router.reload({
                only: ["employee"],
                preserveScroll: true,
            });

            loading.value = false;
        },

        onError: (errors) => {
            console.log("VALIDATION ERRORS:", errors); // 🔥 DEBUG
            toastStore.show("Please fix the errors and try again.", "error");
            loading.value = false;
        },

        onFinish: () => {
            loading.value = false;
        },
    });
};

onMounted(() => {
    if (page.props.flash?.message) {
        toastStore.show(page.props.flash.message, "success");
    }
});

const clearError = (field) => {
    if (form.errors[field]) form.errors[field] = null;
};

// password updates
const passwordRules = computed(() => {
    const v = passwordForm.new_password || "";

    return {
        length: v.length >= 8,
        upper: /[A-Z]/.test(v),
        lower: /[a-z]/.test(v),
        number: /[0-9]/.test(v),
    };
});

const isPasswordValid = computed(() =>
    Object.values(passwordRules.value).every(Boolean),
);

const isPasswordModalOpen = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const showCurrentPassword = ref(false);

const passwordForm = useForm({
    current_password: "",
    new_password: "",
    new_password_confirmation: "",
});

const passwordLoading = ref(false);
const submitPassword = () => {
    passwordLoading.value = true;

    passwordForm.clearErrors();

    passwordForm.put(`/${baseRoute}/profile/change-password`, {
        preserveScroll: true,

        onSuccess: () => {
            toastStore.show("Password updated successfully!", "success");
            isPasswordModalOpen.value = false;
            passwordForm.reset();
        },

        onError: () => {
            toastStore.show("Please check your password fields.", "error");
        },

        onFinish: () => {
            passwordLoading.value = false;
        },
    });
};
</script>

<template>
    <div class="p-6 space-y-6">
        <Card class="shadow-sm border-blue-100">
            <div
                v-if="employee.status_id == 4"
                class="m-6 flex items-start gap-4 p-4 bg-amber-50 border border-amber-200 rounded-xl"
            >
                <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                    <Info class="w-5 h-5" />
                </div>
                <div>
                    <h5 class="text-sm font-bold text-amber-900">
                        Action Required: Complete Your Profile
                    </h5>
                    <p class="text-sm text-amber-700 leading-relaxed">
                        Your account is currently <strong>Pending</strong>.
                        Please fill out all required fields, including your
                        profile photo and resume. Once you click "Update
                        Profile," your information will be saved and your
                        account will be
                        <strong>Activated</strong> automatically.
                    </p>
                </div>
            </div>

            <CardHeader>
                <div class="flex justify-between">
                    <div>
                        <CardTitle class="text-2xl font-bold text-brand-blue"
                            >Update Your Profile {{ baseRoute }}</CardTitle
                        >
                        <CardDescription
                            >Edit your details. If files already exist, you
                            don't need to re-upload them.</CardDescription
                        >
                    </div>
                    <div>
                        <Button
                            class="bg-gray-900 text-white flex items-center gap-2"
                            @click="isPasswordModalOpen = true"
                        >
                            <Lock class="w-4 h-4" />
                            Change Password
                        </Button>
                    </div>
                </div>
            </CardHeader>

            <CardContent>
                <form @submit.prevent="submit" class="space-y-10">
                    <section class="space-y-4">
                        <div
                            class="flex items-center gap-3 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                        >
                            <User class="w-5 h-5" />
                            <h3>Profile Photo</h3>
                        </div>

                        <div class="flex items-center gap-4">
                            <div
                                class="w-28 h-28 rounded-full bg-gray-100 overflow-hidden border-2 grid place-items-center"
                                :class="
                                    form.errors.profile_photo
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                "
                            >
                                <img
                                    v-if="preview || existingPhoto"
                                    :src="preview ? preview : existingPhoto"
                                    class="w-full h-full object-cover"
                                />
                                <User v-else class="text-gray-400 w-12 h-12" />
                            </div>

                            <div class="flex flex-col flex-1">
                                <Input
                                    type="file"
                                    accept="image/png, image/jpeg, image/jpg"
                                    @change="previewImage"
                                />

                                <span
                                    v-if="form.errors.profile_photo"
                                    class="text-red-500 text-sm"
                                >
                                    {{ form.errors.profile_photo }}
                                </span>
                                <p
                                    v-if="existingPhoto"
                                    class="text-xs text-green-600 mt-1 font-medium"
                                >
                                    ✓ Photo is already saved. Upload only if
                                    changing.
                                </p>

                                <p
                                    v-else-if="!form.profile_photo"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    Photo is required.
                                </p>
                            </div>
                        </div>
                    </section>

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
                                    { key: 'first_name', label: 'First Name' },
                                    {
                                        key: 'middle_name',
                                        label: 'Middle Name (Optional)',
                                    },
                                    { key: 'last_name', label: 'Last Name' },
                                    {
                                        key: 'suffix',
                                        label: 'Suffix (Optional)',
                                        type: 'select',
                                        options: [
                                            'N/A',
                                            'Jr.',
                                            'Sr.',
                                            'II',
                                            'III',
                                            'IV',
                                            'V',
                                        ],
                                    },
                                    {
                                        key: 'gender',
                                        label: 'Gender',
                                        type: 'select',
                                        options: [
                                            'Male',
                                            'Female',
                                            'Other',
                                            'Prefer not to say',
                                        ],
                                    },
                                    {
                                        key: 'date_birth',
                                        label: 'Date of Birth',
                                        type: 'date',
                                    },
                                    {
                                        key: 'civil_status',
                                        label: 'Civil Status',
                                        type: 'select',
                                        options: ['Single', 'Married', 'Other'],
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
                                >
                                    {{ field.label }}
                                </label>

                                <!-- Select Fields -->
                                <select
                                    v-if="field.type === 'select'"
                                    v-model="form[field.key]"
                                    class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="
                                        form.errors[field.key]
                                            ? 'border-red-500 text-red-500 focus-visible:ring-red-500'
                                            : 'border-slate-200 text-slate-900'
                                    "
                                    @change="clearError(field.key)"
                                >
                                    <!-- Disabled Placeholder Option -->
                                    <option value="" disabled selected hidden>
                                        Select {{ field.label }}
                                    </option>
                                    <option
                                        v-for="opt in field.options"
                                        :key="opt"
                                        :value="opt"
                                    >
                                        {{ opt }}
                                    </option>
                                </select>

                                <!-- Input/Date Fields -->
                                <Input
                                    v-else
                                    v-model="form[field.key]"
                                    :type="field.type || 'text'"
                                    :placeholder="field.label"
                                    class="bg-white"
                                    :class="
                                        form.errors[field.key]
                                            ? 'border-red-500 focus-visible:ring-red-500'
                                            : 'border-slate-200'
                                    "
                                    @input="clearError(field.key)"
                                />

                                <!-- Error Message text -->
                                <span
                                    v-if="form.errors[field.key]"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors[field.key] }}
                                </span>
                            </div>
                        </div>
                    </section>

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
                                        label: 'Telephone (Optional)',
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
                                    class="bg-white"
                                    :class="
                                        form.errors[field.key]
                                            ? 'border-red-500'
                                            : ''
                                    "
                                    @input="clearError(field.key)"
                                />
                                <span
                                    v-if="form.errors[field.key]"
                                    class="text-red-500 text-sm"
                                    >{{ form.errors[field.key] }}</span
                                >
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
                                    class="bg-white"
                                    :placeholder="field.label"
                                    :class="
                                        form.errors[field.key]
                                            ? 'border-red-500'
                                            : ''
                                    "
                                    @input="clearError(field.key)"
                                />
                                <span
                                    v-if="form.errors[field.key]"
                                    class="text-red-500 text-sm mt-1"
                                    >{{ form.errors[field.key] }}</span
                                >
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
                                    class="bg-white"
                                    :placeholder="field.label"
                                    :class="
                                        form.errors[field.key]
                                            ? 'border-red-500'
                                            : ''
                                    "
                                    @input="clearError(field.key)"
                                />
                                <span
                                    v-if="form.errors[field.key]"
                                    class="text-red-500 text-sm"
                                    >{{ form.errors[field.key] }}</span
                                >
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div
                            class="flex items-center justify-between border-l-4 border-brand-blue pl-3"
                        >
                            <div
                                class="flex items-center gap-3 text-brand-blue font-bold"
                            >
                                <FileText class="w-5 h-5" />
                                <h3>Resume (PDF/DOC)</h3>
                            </div>

                            <a
                                v-if="existingResume"
                                :href="existingResume"
                                target="_blank"
                                class="text-xs flex items-center gap-1 text-brand-blue hover:underline font-medium"
                            >
                                View Current Resume
                                <ExternalLink class="w-3 h-3" />
                            </a>
                        </div>

                        <div
                            class="flex flex-col p-4 bg-slate-50 rounded-xl border border-dashed"
                            :class="
                                form.errors.resume
                                    ? 'border-red-500'
                                    : 'border-slate-200'
                            "
                        >
                            <Input
                                type="file"
                                accept=".pdf,.doc,.docx"
                                class="bg-white"
                                @change="handleResume"
                            />

                            <span
                                v-if="form.errors.resume"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ form.errors.resume }}
                            </span>

                            <div class="mt-2">
                                <!-- ✅ FIXED LOGIC -->
                                <p
                                    v-if="existingResume"
                                    class="text-xs text-green-600 font-medium flex items-center gap-1"
                                >
                                    <CheckCircle class="w-3 h-3" />
                                    Already uploaded. Upload a new file only to
                                    replace it.
                                </p>

                                <p
                                    v-else-if="!form.resume"
                                    class="text-xs text-red-500 flex items-center gap-1"
                                >
                                    <AlertCircle class="w-3 h-3" />
                                    Resume is required.
                                </p>
                            </div>
                        </div>
                    </section>
                </form>
            </CardContent>

            <CardFooter class="flex justify-end gap-3 py-6 border-t border-b">
                <Button
                    type="button"
                    class="bg-brand-blue text-white flex items-center gap-2"
                    :disabled="loading"
                    @click="submit"
                >
                    <template v-if="loading">Updating...</template>
                    <template v-else>
                        <Save class="w-4 h-4" /> Update Profile
                    </template>
                </Button>
            </CardFooter>

            <div class="px-6">
                <div
                    class="flex items-center gap-3 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                >
                    <User class="w-5 h-5" />
                    <h3>Your Personal Record (Only HR that Edit)</h3>
                </div>

                <div class="space-y-4">
                    <section class="space-y-4 pt-6">
                        <h4
                            class="text-xs font-semibold text-slate-500 uppercase"
                        >
                            Basic Identification
                        </h4>
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div
                                v-for="field in [
                                    {
                                        key: 'employee_id',
                                        label: 'Employee ID',
                                    },
                                    {
                                        key: 'username',
                                        label: 'Username',
                                    },
                                    {
                                        key: 'company_email',
                                        label: 'Company Email',
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
                                    readonly
                                    disabled
                                />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4 pt-6">
                        <h4
                            class="text-xs font-semibold text-slate-500 uppercase"
                        >
                            Employment Details
                        </h4>
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div
                                v-for="field in [
                                    {
                                        key: 'employee_id',
                                        label: 'User Type (Role)',
                                    },
                                    {
                                        key: 'department_name',
                                        label: 'Department',
                                    },
                                    {
                                        key: 'position_name',
                                        label: 'Position',
                                    },

                                    {
                                        key: 'employment_status',
                                        label: 'Employment Status',
                                    },

                                    {
                                        key: 'employment_type',
                                        label: 'Employment Type',
                                    },

                                    {
                                        key: 'date_hired',
                                        label: 'Date Hired',
                                        type: 'date',
                                    },

                                    {
                                        key: 'regularization_date',
                                        label: 'Regularization Date',
                                        type: 'date',
                                    },

                                    {
                                        key: 'immediate_supervisor',
                                        label: 'Immediate Supervisor',
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
                                    readonly
                                    disabled
                                />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4 pt-6">
                        <h4
                            class="text-xs font-semibold text-slate-500 uppercase"
                        >
                            Work Settings & Payroll
                        </h4>
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div
                                v-for="field in [
                                    {
                                        key: 'work_location',
                                        label: 'Work Location',
                                    },
                                    {
                                        key: 'payroll_group',
                                        label: 'Payroll Group',
                                    },
                                    {
                                        key: 'leave_pay',
                                        label: 'Leave Pay Credits',
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
                                    readonly
                                    disabled
                                />
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <Dialog v-model:open="isPasswordModalOpen">
                <DialogContent class="w-[420px]">
                    <DialogHeader>
                        <DialogTitle>Change Password</DialogTitle>
                    </DialogHeader>

                    <!-- CURRENT PASSWORD -->
                    <div class="pt-3">
                        <label class="p-1">Current Password</label>
                        <div class="relative">
                            <Input
                                :type="
                                    showCurrentPassword ? 'text' : 'password'
                                "
                                v-model="passwordForm.current_password"
                                placeholder="Current Password"
                                :class="
                                    passwordForm.errors.current_password
                                        ? 'border-red-500'
                                        : ''
                                "
                                @input="
                                    passwordForm.clearErrors('current_password')
                                "
                            />

                            <button
                                type="button"
                                class="absolute right-3 top-2"
                                @click="
                                    showCurrentPassword = !showCurrentPassword
                                "
                            >
                                <Eye v-if="!showCurrentPassword" />
                                <EyeOff v-else />
                            </button>

                            <!-- ERROR -->
                            <p
                                v-if="passwordForm.errors.current_password"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </div>
                    </div>

                    <!-- NEW PASSWORD -->
                    <div>
                        <label class="p-1">New Password</label>
                        <div class="relative">
                            <Input
                                :type="showNewPassword ? 'text' : 'password'"
                                v-model="passwordForm.new_password"
                                placeholder="New Password"
                                :class="
                                    passwordForm.errors.new_password
                                        ? 'border-red-500'
                                        : ''
                                "
                                @input="
                                    passwordForm.clearErrors('new_password')
                                "
                            />

                            <button
                                type="button"
                                class="absolute right-3 top-2"
                                @click="showNewPassword = !showNewPassword"
                            >
                                <Eye v-if="!showNewPassword" />
                                <EyeOff v-else />
                            </button>

                            <!-- SERVER ERROR -->
                            <p
                                v-if="passwordForm.errors.new_password"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ passwordForm.errors.new_password }}
                            </p>
                        </div>
                    </div>

                    <!-- PASSWORD RULES (ONLY WHEN INVALID AND USER TYPING NEW PASSWORD) -->
                    <div
                        v-if="
                            passwordForm.new_password.length > 0 &&
                            !isPasswordValid
                        "
                        class="text-xs space-y-1"
                    >
                        <p
                            :class="
                                passwordRules.length
                                    ? 'text-green-600'
                                    : 'text-red-500'
                            "
                        >
                            • At least 8 characters
                        </p>
                        <p
                            :class="
                                passwordRules.upper
                                    ? 'text-green-600'
                                    : 'text-red-500'
                            "
                        >
                            • One uppercase letter
                        </p>
                        <p
                            :class="
                                passwordRules.lower
                                    ? 'text-green-600'
                                    : 'text-red-500'
                            "
                        >
                            • One lowercase letter
                        </p>
                        <p
                            :class="
                                passwordRules.number
                                    ? 'text-green-600'
                                    : 'text-red-500'
                            "
                        >
                            • One number
                        </p>
                    </div>

                    <!-- CONFIRM PASSWORD -->
                    <div>
                        <label class="p-1">Confirm Password</label>
                        <div class="relative">
                            <Input
                                :type="
                                    showConfirmPassword ? 'text' : 'password'
                                "
                                v-model="passwordForm.new_password_confirmation"
                                placeholder="Confirm Password"
                                :class="
                                    passwordForm.errors
                                        .new_password_confirmation
                                        ? 'border-red-500'
                                        : ''
                                "
                                @input="
                                    passwordForm.clearErrors(
                                        'new_password_confirmation',
                                    )
                                "
                            />

                            <button
                                type="button"
                                class="absolute right-3 top-2"
                                @click="
                                    showConfirmPassword = !showConfirmPassword
                                "
                            >
                                <Eye v-if="!showConfirmPassword" />
                                <EyeOff v-else />
                            </button>

                            <p
                                v-if="
                                    passwordForm.errors
                                        .new_password_confirmation
                                "
                                class="text-red-500 text-xs mt-1"
                            >
                                {{
                                    passwordForm.errors
                                        .new_password_confirmation
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <DialogFooter class="flex justify-end gap-2 mt-3">
                        <Button
                            variant="secondary"
                            @click="isPasswordModalOpen = false"
                        >
                            Cancel
                        </Button>

                        <Button
                            class="bg-brand-blue text-white flex items-center gap-2"
                            :disabled="!isPasswordValid || passwordLoading"
                            @click="submitPassword"
                        >
                            <span v-if="passwordLoading">Updating...</span>
                            <span v-else>Update Password</span>
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </Card>
    </div>
</template>
