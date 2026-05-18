<script setup>
import { ref, onMounted } from "vue"; // watch removed as onMounted is sufficient here
import { useForm, usePage } from "@inertiajs/vue3";
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
} from "lucide-vue-next";

const { props } = usePage();
const head = props.head;

const form = useForm({
    _method: "put",

    // head edit
    status_id: head.status_id,
    first_name: head.first_name ?? "",
    middle_name: head.middle_name ?? "",
    last_name: head.last_name ?? "",
    suffix: head.suffix ?? "",
    gender: head.gender ?? "",
    date_birth: head.date_birth ?? "",
    civil_status: head.civil_status ?? "",
    nationality: head.nationality ?? "",
    personal_email: head.personal_email ?? "",
    mobile_number: head.mobile_number ?? "",
    telephone_number: head.telephone_number ?? "",
    present_address: head.present_address ?? "",
    permanent_address: head.permanent_address ?? "",
    sss_number: head.sss_number ?? "",
    philhealth_number: head.philhealth_number ?? "",
    pagibig_number: head.pagibig_number ?? "",
    tin_number: head.tin_number ?? "",
    contact_person: head.contact_person ?? "",
    relationship: head.relationship ?? "",
    contact_number: head.contact_number ?? "",
    address: head.address ?? "",
    resume: null,
    profile_photo: null,

    // view only
    department_name: head.department?.name || "N/A",
    position_name: head.position?.name || "N/A",
    department_id: head.department_id,
    position_id: head.position_id,

    employee_id: head.employee_id,
    username: head.username,
    company_email: head.company_email,
    date_hired: head.date_hired,
    regularization_date: head.regularization_date,
    immediate_supervisor: head.immediate_supervisor,
    work_location: head.work_location,
    payroll_group: head.payroll_group,
    leave_pay: head.leave_pay,
    employment_status: head.employment_status,
    employment_type: head.employment_type,
});

const preview = ref(null);
const existingPhoto = head.profile_photo
    ? `/storage/app/public/${head.profile_photo}`
    : null;
const existingResume = head.resume
    ? `/storage/app/public/${head.resume}`
    : null;
const loading = ref(false);

const previewImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.profile_photo = file;
    preview.value = URL.createObjectURL(file);
};

// 1. Trigger the reload on success
const submit = () => {
    loading.value = true;
    form.post("/head/profile/update", {
        preserveScroll: true,
        onSuccess: () => {
            head.status_id = 1;
            toastStore.show("Profile updated successfully!", "success");
            loading.value = false;
        },
        onError: () => {
            toastStore.show("Please fix the errors and try again.", "error");
            loading.value = false;
        },
    });
};

onMounted(() => {
    if (usePage().props.flash?.message) {
        toastStore.show(usePage().props.flash.message, "success");
    }
});

const clearError = (field) => {
    if (form.errors[field]) form.errors[field] = null;
};
</script>

<template>
    <div class="p-6 space-y-6">
        <Card class="max-w-7xl mx-auto shadow-sm border-blue-100">
            <div
                v-if="head.status_id == 4"
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
                <CardTitle class="text-2xl font-bold text-brand-blue"
                    >Update Your Profile</CardTitle
                >
                <CardDescription
                    >Edit your details. If files already exist, you don't need
                    to re-upload them.</CardDescription
                >
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
                                    >{{ form.errors.profile_photo }}</span
                                >
                                <p
                                    v-if="existingPhoto"
                                    class="text-xs text-green-600 mt-1 font-medium"
                                >
                                    ✓ Photo is already saved. Upload only if
                                    changing.
                                </p>
                                <p v-else class="text-xs text-red-500 mt-1">
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
                                        label: 'Middle Name',
                                    },
                                    { key: 'last_name', label: 'Last Name' },
                                    {
                                        key: 'suffix',
                                        label: 'Suffix',
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

                                <select
                                    v-if="field.type === 'select'"
                                    v-model="form[field.key]"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="
                                        form.errors[field.key]
                                            ? 'border-red-500'
                                            : ''
                                    "
                                    @change="clearError(field.key)"
                                >
                                    <option
                                        v-for="opt in field.options"
                                        :key="opt"
                                        :value="opt"
                                    >
                                        {{ opt }}
                                    </option>
                                </select>

                                <Input
                                    v-else
                                    v-model="form[field.key]"
                                    :type="field.type || 'text'"
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
                                    @input="clearError(field.key)"
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
                                @change="
                                    (e) => (form.resume = e.target.files[0])
                                "
                            />

                            <span
                                v-if="form.errors.resume"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ form.errors.resume }}
                            </span>

                            <div class="mt-2">
                                <p
                                    v-if="existingResume"
                                    class="text-xs text-green-600 font-medium flex items-center gap-1"
                                >
                                    <CheckCircle class="w-3 h-3" /> Already
                                    uploaded. Upload a new file only to replace
                                    it.
                                </p>
                                <p
                                    v-else
                                    class="text-xs text-red-500 flex items-center gap-1"
                                >
                                    <AlertCircle class="w-3 h-3" /> Resume is
                                    required.
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
                                        label: 'Head ID',
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
        </Card>
    </div>
</template>
