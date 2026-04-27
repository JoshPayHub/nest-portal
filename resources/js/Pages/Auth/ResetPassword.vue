<script setup>
import { ref, computed } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";

const show = ref(false);
const showConfirm = ref(false);
const successModal = ref(false);

const form = useForm({
    password: "",
    password_confirmation: "",
});

// ---------------- RULES ----------------
const hasUpper = computed(() => /[A-Z]/.test(form.password));
const hasLower = computed(() => /[a-z]/.test(form.password));
const hasNumber = computed(() => /[0-9]/.test(form.password));
const hasLength = computed(() => form.password.length >= 8);

const isPasswordValid = computed(
    () =>
        hasUpper.value && hasLower.value && hasNumber.value && hasLength.value,
);

// ---------------- FLASH FIX (IMPORTANT) ----------------
const page = usePage();

const submit = () => {
    form.post("/reset-password", {
        preserveScroll: true,

        onSuccess: () => {
            // THIS is the only thing needed for success modal
            successModal.value = true;

            setTimeout(() => {
                router.visit("/");
            }, 3000);
        },
    });
};

// ---------------- LOGIN ----------------
const goLogin = () => {
    router.visit("/");
};
</script>

<template>
    <div
        class="relative min-h-screen flex items-center justify-center bg-[url('@/assets/auth/happyLogo.png')] bg-cover bg-center"
    >
        <div class="absolute inset-0 bg-black/50"></div>

        <div
            class="relative z-10 bg-white/90 shadow-xl rounded-xl w-full max-w-md py-8"
        >
            <h1
                class="text-3xl font-bold text-center mt-4 bg-brand-blue text-white py-1.5 mb-6"
            >
                RESET PASSWORD
            </h1>

            <div class="px-8">
                <form @submit.prevent="submit">
                    <!-- PASSWORD -->
                    <label class="block text-brand-blue mb-1">
                        New Password
                    </label>

                    <div class="mb-2 relative">
                        <input
                            v-model="form.password"
                            :type="show ? 'text' : 'password'"
                            placeholder="Enter your new password"
                            class="w-full border bg-white rounded-lg p-3 pr-14"
                        />

                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-brand-blue font-medium"
                        >
                            {{ show ? "Hide" : "Show" }}
                        </button>
                    </div>

                    <p
                        v-if="form.errors.password"
                        class="text-red-500 text-xs mb-2"
                    >
                        {{ form.errors.password }}
                    </p>

                    <!-- RULES -->
                    <div
                        v-if="form.password && !isPasswordValid"
                        class="text-xs mt-2 space-y-1 text-gray-600"
                    >
                        <p
                            :class="
                                hasUpper ? 'text-green-600' : 'text-red-500'
                            "
                        >
                            • At least one uppercase letter
                        </p>
                        <p
                            :class="
                                hasLower ? 'text-green-600' : 'text-red-500'
                            "
                        >
                            • At least one lowercase letter
                        </p>
                        <p
                            :class="
                                hasNumber ? 'text-green-600' : 'text-red-500'
                            "
                        >
                            • At least one number
                        </p>
                        <p
                            :class="
                                hasLength ? 'text-green-600' : 'text-red-500'
                            "
                        >
                            • At least 8 characters long
                        </p>
                    </div>

                    <!-- CONFIRM PASSWORD -->
                    <label class="block text-brand-blue mt-3 mb-1">
                        Confirm Password
                    </label>

                    <div class="mb-4 relative">
                        <input
                            v-model="form.password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            placeholder="Enter your confirm password"
                            class="w-full border bg-white rounded-lg p-3 pr-14"
                        />

                        <button
                            type="button"
                            @click="showConfirm = !showConfirm"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-brand-blue font-medium"
                        >
                            {{ showConfirm ? "Hide" : "Show" }}
                        </button>
                    </div>

                    <!-- SUBMIT -->
                    <button
                        class="w-full bg-brand-blue mt-4 hover:bg-green-700 text-white py-2.5 rounded-lg font-semibold disabled:opacity-50 flex justify-center items-center gap-2"
                        :disabled="form.processing || !isPasswordValid"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
                        ></svg>

                        {{ form.processing ? "Updating..." : "Reset Password" }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL (UNCHANGED DESIGN) -->
    <div
        v-if="successModal"
        class="fixed inset-0 z-[9999] bg-black/50 flex items-center justify-center px-4"
    >
        <div
            class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden"
        >
            <!-- HEADER -->
            <div class="bg-brand-blue text-white py-4 text-center">
                <h2 class="text-lg font-semibold tracking-wide">
                    Password Updated
                </h2>
            </div>

            <!-- BODY -->
            <div class="p-6 text-center">
                <div
                    class="mx-auto w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mb-4"
                >
                    <svg
                        class="w-7 h-7 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>

                <p class="text-gray-700 font-medium">
                    Your password has been successfully updated.
                </p>

                <p class="text-xs text-gray-500 mt-2">
                    You will be redirected to the login page shortly.
                </p>
            </div>
        </div>
    </div>
</template>
