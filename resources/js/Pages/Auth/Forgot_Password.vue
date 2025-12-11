<template>
    <div class="bg-light-blue flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
            <h1 class="text-3xl font-bold text-center text-brand-blue mb-2">
                Forgot Password
            </h1>

            <p class="text-center text-gray-600 mb-6 text-sm">
                Enter your email and we will send you instructions to reset your
                password.
            </p>

            <!-- Email input -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Email Address</label>
                <div class="flex items-center border rounded-lg px-3 py-2">
                    <i class="fa-solid fa-envelope text-gray-500 mr-2"></i>
                    <input
                        type="email"
                        class="w-full outline-none"
                        placeholder="Enter your registered email"
                    />
                </div>
            </div>

            <!-- Button -->
            <button
                class="w-full bg-brand-blue text-white py-2 rounded-lg font-semibold hover:bg-green-700"
            >
                Send Reset Link
            </button>

            <!-- Back to login -->
            <p class="text-sm text-center mt-4">
                <a
                    href="/login"
                    class="text-brand-blue hover:underline font-semibold"
                >
                    <i class="fa-solid fa-arrow-left mr-1"></i> Back to Login
                </a>
            </p>
        </div>
    </div>
</template>
