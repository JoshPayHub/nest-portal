<script setup>
import { ref, onMounted, computed } from "vue";
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

import { User, Save, Lock, Eye, EyeOff } from "lucide-vue-next";

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
    1: "hr",
};

const baseRoute = routeMap[auth_user_type_id];

// PROFILE FORM
const form = useForm({
    // editable
    first_name: employee.value.first_name ?? "",
    middle_name: employee.value.middle_name ?? "",
    last_name: employee.value.last_name ?? "",

    gender: employee.value.gender ?? "",
    date_birth: employee.value.date_birth ?? "",

    company_email: employee.value.company_email ?? "",

    profile_photo: null,

    // readonly (ADMIN ONLY EDIT)
    employee_id: employee.value.employee_id ?? "",
    username: employee.value.username ?? "",
});

const preview = ref(null);

const existingPhoto = computed(() =>
    employee.value?.profile_photo
        ? `/storage/${employee.value.profile_photo}`
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
        forceFormData: true,
        preserveScroll: true,

        onSuccess: () => {
            toastStore.show("Profile updated successfully!", "success");

            form.clearErrors();

            router.reload({
                only: ["employee"],
                preserveScroll: true,
            });
        },

        onError: () => {
            toastStore.show("Please fix the errors and try again.", "error");
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
    if (form.errors[field]) {
        form.errors[field] = null;
    }
};

// PASSWORD

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

    passwordForm.post(`/${baseRoute}/profile/change-password`, {
        preserveScroll: true,

        onSuccess: () => {
            toastStore.show("Password updated successfully!", "success");

            isPasswordModalOpen.value = false;

            passwordForm.reset();

            passwordForm.clearErrors();
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
            <CardHeader>
                <div class="flex justify-between">
                    <div>
                        <CardTitle class="text-2xl font-bold text-brand-blue">
                            Update Your HR Profile
                        </CardTitle>

                        <CardDescription>
                            Edit your personal HR profile information.
                        </CardDescription>
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
                    <!-- PROFILE PHOTO -->
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

                    <!-- BASIC PROFILE -->
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
                                        label: 'Middle Name (Optional)',
                                    },

                                    {
                                        key: 'last_name',
                                        label: 'Last Name',
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
                                    class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm"
                                    :class="
                                        form.errors[field.key]
                                            ? 'border-red-500'
                                            : 'border-slate-200'
                                    "
                                    @change="clearError(field.key)"
                                >
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

                                <Input
                                    v-else
                                    v-model="form[field.key]"
                                    :type="field.type || 'text'"
                                    :placeholder="field.label"
                                    class="bg-white"
                                    :class="
                                        form.errors[field.key]
                                            ? 'border-red-500'
                                            : 'border-slate-200'
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
                </form>
            </CardContent>

            <CardFooter class="flex justify-end gap-3 py-6 border-t border-b">
                <Button
                    type="button"
                    class="bg-brand-blue text-white flex items-center gap-2"
                    :disabled="loading"
                    @click="submit"
                >
                    <template v-if="loading"> Updating... </template>

                    <template v-else>
                        <Save class="w-4 h-4" />
                        Update Profile
                    </template>
                </Button>
            </CardFooter>

            <div class="px-6 pb-6">
                <div
                    class="flex items-center gap-3 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                >
                    <User class="w-5 h-5" />
                    <h3>Your Personal Record (Only ADMIN that Edit)</h3>
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
                                >
                                    {{ field.label }}
                                </label>

                                <Input
                                    v-model="form[field.key]"
                                    readonly
                                    disabled
                                    class="bg-slate-100 cursor-not-allowed"
                                />
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!-- PASSWORD MODAL -->
            <Dialog v-model:open="isPasswordModalOpen">
                <DialogContent class="w-[420px]">
                    <DialogHeader>
                        <DialogTitle> Change Password </DialogTitle>
                    </DialogHeader>

                    <!-- CURRENT PASSWORD -->
                    <div class="pt-3">
                        <label class="p-1"> Current Password </label>

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
                        <label class="p-1"> New Password </label>

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

                            <p
                                v-if="passwordForm.errors.new_password"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ passwordForm.errors.new_password }}
                            </p>
                        </div>
                    </div>

                    <!-- RULES -->
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

                    <!-- CONFIRM -->
                    <div>
                        <label class="p-1"> Confirm Password </label>

                        <div class="relative">
                            <Input
                                :type="
                                    showConfirmPassword ? 'text' : 'password'
                                "
                                v-model="passwordForm.new_password_confirmation"
                                placeholder="Confirm Password"
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
                        </div>
                    </div>

                    <DialogFooter class="flex justify-end gap-2 mt-3">
                        <Button
                            variant="secondary"
                            @click="isPasswordModalOpen = false"
                        >
                            Cancel
                        </Button>

                        <Button
                            class="bg-brand-blue text-white"
                            :disabled="!isPasswordValid || passwordLoading"
                            @click="submitPassword"
                        >
                            <span v-if="passwordLoading"> Updating... </span>

                            <span v-else> Update Password </span>
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </Card>
    </div>
</template>
