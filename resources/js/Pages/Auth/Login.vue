<script setup>
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    username: "", // Changed from email
    password: "",
    remember: false,
});

const submit = () => {
    form.post("/", {
        onFinish: () => form.reset("password"),
        onError: () => {
            console.log("Login attempt failed.");
        },
    });
};
</script>

<template>
    <div
        class="bg-[url('@/assets/auth/bg-login.png')] bg-cover bg-center bg-no-repeat flex items-center justify-center min-h-screen"
    >
        <div class="bg-white shadow-xl rounded-xl w-full max-w-md py-8">
            <div class="flex gap-5 justify-center items-center">
                <img src="@/assets/dashboard/logo.png" class="h-20" alt="" />
                <div class="leading-none">
                    <h1 class="flex font-bold text-brand-blue text-3xl italic">
                        NEST
                    </h1>
                    <p class="text-[#59B3DB]">Portal System</p>
                </div>
            </div>
            <h1
                class="text-3xl font-bold text-center mt-6 bg-brand-blue text-white py-1.5 mb-6"
            >
                ACCOUNT LOGIN
            </h1>

            <div class="px-8">
                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block text-brand-blue mb-1"
                            >Username</label
                        >
                        <div
                            class="flex items-center border rounded-lg px-3 py-2 transition-colors"
                            :class="{
                                'border-red-500 bg-red-50':
                                    form.errors.username,
                            }"
                        >
                            <i
                                class="fa-solid fa-user text-brand-blue mr-2"
                            ></i>
                            <input
                                v-model="form.username"
                                @input="form.clearErrors('username')"
                                type="text"
                                class="w-full outline-none bg-transparent"
                                placeholder="Enter your username"
                                required
                            />
                        </div>
                        <span
                            v-if="form.errors.username"
                            class="text-red-600 text-xs mt-1 block"
                        >
                            {{ form.errors.username }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="block text-brand-blue mb-1"
                            >Password</label
                        >
                        <div
                            class="flex items-center border rounded-lg px-3 py-2 transition-colors"
                            :class="{
                                'border-red-500 bg-red-50':
                                    form.errors.password,
                            }"
                        >
                            <i
                                class="fa-solid fa-lock text-brand-blue mr-2"
                            ></i>
                            <input
                                v-model="form.password"
                                @input="form.clearErrors('password')"
                                type="password"
                                class="w-full outline-none bg-transparent"
                                placeholder="Enter your password"
                                required
                            />
                        </div>
                        <span
                            v-if="form.errors.password"
                            class="text-red-600 text-xs mt-1 block"
                        >
                            {{ form.errors.password }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label
                            class="flex items-center gap-2 text-sm cursor-pointer select-none"
                        >
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 text-brand-blue rounded border-gray-300 focus:ring-brand-blue"
                            />
                            Remember me
                        </label>
                        <a
                            href="/forgot-password"
                            class="text-brand-blue hover:underline text-sm font-medium"
                        >
                            Forgot password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-brand-blue hover:bg-blue-700 text-white py-2.5 rounded-lg font-semibold disabled:opacity-50 transition-all shadow-md active:transform active:scale-95"
                    >
                        {{ form.processing ? "Authenticating..." : "Login" }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
