<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const showPassword = ref(false);

const form = useForm({
    username: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post("/", {
        onFinish: () => form.reset("password"),
        onError: () => console.log("Login failed"),
    });
};
</script>

<template>
    <div
        class="relative min-h-screen flex items-center justify-center bg-[url('@/assets/auth/happyLogo.png')] bg-cover bg-center bg-no-repeat"
    >
        <div class="absolute inset-0 bg-black/50"></div>

        <div
            class="relative z-10 bg-white/90 shadow-xl rounded-xl w-full max-w-md py-8"
        >
            <h1
                class="text-3xl font-bold text-center mt-4 bg-brand-blue text-white py-1.5 mb-6"
            >
                ACCOUNT LOGIN
            </h1>

            <div class="px-8">
                <form @submit.prevent="submit">
                    <!-- USERNAME -->
                    <div class="mb-4">
                        <label class="block text-brand-blue mb-1"
                            >Username</label
                        >

                        <div
                            class="flex items-center border bg-white rounded-lg px-3 py-2"
                            :class="{ 'border-red-500': form.errors.username }"
                        >
                            <i
                                class="fa-solid fa-user text-brand-blue mr-2"
                            ></i>

                            <input
                                v-model="form.username"
                                @input="form.clearErrors('username')"
                                type="text"
                                class="w-full outline-none"
                                placeholder="Enter your username"
                                required
                            />
                        </div>

                        <span
                            v-if="form.errors.username"
                            class="text-red-600 text-xs"
                        >
                            {{ form.errors.username }}
                        </span>
                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-3">
                        <label class="block text-brand-blue mb-1"
                            >Password</label
                        >

                        <div
                            class="flex items-center border bg-white rounded-lg px-3 py-2"
                            :class="{ 'border-red-500': form.errors.password }"
                        >
                            <i
                                class="fa-solid fa-lock text-brand-blue mr-2"
                            ></i>

                            <input
                                v-model="form.password"
                                @input="form.clearErrors('password')"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full outline-none bg-transparent"
                                placeholder="Enter your password"
                                required
                            />

                            <!-- EYE -->
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                            >
                                <i
                                    :class="
                                        showPassword
                                            ? 'fa-solid fa-eye-slash'
                                            : 'fa-solid fa-eye'
                                    "
                                    class="text-gray-500 ml-2"
                                ></i>
                            </button>
                        </div>

                        <span
                            v-if="form.errors.password"
                            class="text-red-600 text-xs"
                        >
                            {{ form.errors.password }}
                        </span>
                    </div>

                    <!-- REMEMBER + FORGOT -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 text-sm">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 text-brand-blue"
                            />
                            Remember me
                        </label>

                        <a
                            href="/forgot-password"
                            class="text-brand-blue text-sm hover:underline"
                        >
                            Forgot password?
                        </a>
                    </div>

                    <!-- BUTTON -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-brand-blue hover:bg-green-700 text-white py-2.5 rounded-lg font-semibold disabled:opacity-50 flex justify-center items-center gap-2"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
                        ></svg>
                        <span>{{
                            form.processing ? "Authenticating..." : "Login"
                        }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
