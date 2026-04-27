<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

const form = useForm({ otp: "" });
const resendForm = useForm({});

const page = usePage();

// ---------------- SERVER SOURCE OF TRUTH ----------------
const resendAt = ref(page.props.resend_available_at || 0);
const now = ref(Math.floor(Date.now() / 1000));

const isResending = ref(false);

// ---------------- COOLDOWN (BASED ON SERVER TIME) ----------------
const cooldown = computed(() => {
    const diff = resendAt.value - now.value;
    return diff > 0 ? diff : 0;
});

const canResend = computed(() => cooldown.value === 0);

// ---------------- VERIFY OTP ----------------
const submit = () => {
    form.post("/otp-verify");
};

// ---------------- RESEND OTP ----------------
const resend = () => {
    if (isResending.value || !canResend.value) return;

    isResending.value = true;

    resendForm.post("/otp-resend", {
        preserveScroll: true,

        onSuccess: (page) => {
            // 🔥 CRITICAL: update from backend response
            resendAt.value =
                page.props.resend_available_at ||
                Math.floor(Date.now() / 1000) + 120;
        },

        onFinish: () => {
            isResending.value = false;
        },
    });
};

// ---------------- LIVE CLOCK ----------------
onMounted(() => {
    setInterval(() => {
        now.value = Math.floor(Date.now() / 1000);
    }, 1000);
});
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
                OTP VERIFICATION
            </h1>

            <div class="px-8">
                <!-- OTP INPUT -->
                <form @submit.prevent="submit">
                    <label class="block text-brand-blue mb-1">OTP</label>
                    <input
                        v-model="form.otp"
                        type="text"
                        placeholder="Enter your OTP"
                        class="w-full border rounded-lg p-3 bg-white text-center tracking-widest"
                    />

                    <p v-if="form.errors.otp" class="text-red-500 text-sm mt-1">
                        {{ form.errors.otp }}
                    </p>

                    <button
                        class="w-full bg-brand-blue mt-4 text-white py-2.5 rounded-lg font-semibold flex justify-center gap-2"
                        :disabled="form.processing"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
                        ></svg>

                        {{ form.processing ? "Verifying..." : "Verify OTP" }}
                    </button>
                </form>

                <!-- TIMER -->
                <p class="text-center text-sm text-gray-600 mt-3">
                    <span v-if="cooldown > 0">
                        Resend OTP in {{ cooldown }}s
                    </span>

                    <span v-else class="text-green-600 font-medium">
                        You can request a new OTP
                    </span>
                </p>

                <!-- RESEND -->
                <button
                    v-if="canResend"
                    @click="resend"
                    class="w-full mt-3 text-brand-blue hover:underline text-sm flex justify-center items-center gap-2"
                    :disabled="isResending"
                >
                    <svg
                        v-if="isResending"
                        class="animate-spin h-4 w-4 border-2 border-brand-blue border-t-transparent rounded-full"
                    ></svg>

                    {{ isResending ? "Sending OTP..." : "Resend OTP" }}
                </button>

                <!-- BACK LOGIN -->
                <div class="text-center mt-4">
                    <a href="/" class="text-brand-blue text-sm hover:underline">
                        ← Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
