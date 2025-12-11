<template>
    <div class="bg-light-blue flex items-center justify-center min-h-screen">
        <div class="bg-white shadow-xl rounded-xl p-8 w-full max-w-md">
            <h1 class="text-3xl font-bold text-center text-brand-blue mb-6">
                Login
            </h1>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Email Address</label>
                <div class="flex items-center border rounded-lg px-3 py-2">
                    <i class="fa-solid fa-envelope text-gray-500 mr-2"></i>
                    <input
                        type="email"
                        class="w-full outline-none"
                        placeholder="Enter your email"
                    />
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Password</label>
                <div class="flex items-center border rounded-lg px-3 py-2">
                    <i class="fa-solid fa-lock text-gray-500 mr-2"></i>
                    <input
                        type="password"
                        class="w-full outline-none"
                        placeholder="Enter your password"
                    />
                </div>
            </div>

            <!-- Options -->
            <div class="flex items-center justify-between mb-4">
                <label class="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        class="w-4 h-4 text-brand-blue rounded"
                    />
                    Remember me
                </label>

                <a
                    href="/forgot-password"
                    class="text-brand-blue hover:underline text-sm"
                >
                    Forgot password?
                </a>
            </div>

            <!-- Button -->
            <button
                class="w-full bg-brand-blue hover:bg-green-700 text-white py-2 rounded-lg font-semibold"
            >
                Login
            </button>

            <!-- Link -->
            <p class="text-sm text-center mt-4">
                Don’t have an account?
                <a
                    href="/register"
                    class="text-brand-blue hover:underline font-semibold"
                    >Register</a
                >
            </p>

            <p class="text-sm text-center mt-2">
                <a
                    href="/"
                    class="text-brand-blue hover:underline font-semibold"
                >
                    <i class="fa-solid fa-house mr-1"></i> Back to Home
                </a>
            </p>
        </div>
    </div>
</template>
