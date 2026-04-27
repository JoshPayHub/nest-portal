<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    email: "",
});

const submit = () => {
    form.post("/forgot-password");
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
                FORGOT PASSWORD
            </h1>

            <div class="px-8">
                <form @submit.prevent="submit">
                    <label class="block text-brand-blue mb-1"
                        >Company Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="Enter your company email"
                        class="w-full border bg-white rounded-lg p-3"
                    />

                    <p
                        v-if="form.errors.email"
                        class="text-red-500 text-sm ps-1"
                    >
                        {{ form.errors.email }}
                    </p>

                    <button
                        class="w-full bg-brand-blue mt-4 hover:bg-green-700 text-white py-2.5 rounded-lg font-semibold disabled:opacity-50 flex justify-center items-center gap-2"
                        :disabled="form.processing"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
                        ></svg>
                        Send OTP
                    </button>

                    <div class="text-center mt-4">
                        <a
                            href="/"
                            class="text-brand-blue text-sm hover:underline font-medium"
                        >
                            ← Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
