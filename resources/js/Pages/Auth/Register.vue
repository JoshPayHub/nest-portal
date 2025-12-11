<template>
    <div
        class="bg-light-blue flex items-center justify-center py-10 px-3 min-h-screen"
    >
        <div
            class="bg-white shadow-xl rounded-xl py-8 sm:px-8 px-3 w-full max-w-xl"
        >
            <h1 class="text-3xl font-bold text-center text-brand-blue mb-6">
                Create Account
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- First Name -->
                <div>
                    <label class="text-gray-700">First Name</label>
                    <input
                        type="text"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                        placeholder="First Name"
                    />
                </div>

                <!-- Middle Name -->
                <div>
                    <label class="text-gray-700">Middle Name</label>
                    <input
                        type="text"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                        placeholder="Middle Name"
                    />
                </div>

                <!-- Last Name -->
                <div>
                    <label class="text-gray-700">Last Name</label>
                    <input
                        type="text"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                        placeholder="Last Name"
                    />
                </div>
            </div>

            <!-- Email -->
            <div class="mt-4">
                <label class="text-gray-700">Email Address</label>
                <input
                    type="email"
                    class="w-full border rounded-lg px-3 py-2 mt-1"
                    placeholder="Enter your email"
                />
            </div>

            <!-- Contact -->
            <div class="mt-4">
                <label class="text-gray-700">Contact Number</label>
                <input
                    type="text"
                    class="w-full border rounded-lg px-3 py-2 mt-1"
                    placeholder="09XXXXXXXXX"
                />
            </div>

            <!-- Address -->
            <div class="mt-4">
                <label class="text-gray-700">Address</label>
                <input
                    type="text"
                    class="w-full border rounded-lg px-3 py-2 mt-1"
                    placeholder="Complete Address"
                />
            </div>

            <!-- Birthdate / Gender -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                <div>
                    <label class="text-gray-700">Birthdate</label>
                    <input
                        type="date"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                    />
                </div>

                <div>
                    <label class="text-gray-700">Gender</label>
                    <select class="w-full border rounded-lg px-3 py-2 mt-1">
                        <option>Male</option>
                        <option>Female</option>
                        <option>Prefer not to say</option>
                    </select>
                </div>
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label class="text-gray-700">Password</label>
                <input
                    type="password"
                    class="w-full border rounded-lg px-3 py-2 mt-1"
                    placeholder="Password"
                />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <label class="text-gray-700">Confirm Password</label>
                <input
                    type="password"
                    class="w-full border rounded-lg px-3 py-2 mt-1"
                    placeholder="Confirm Password"
                />
            </div>

            <!-- Button -->
            <button
                class="w-full bg-brand-blue hover:bg-green-700 text-white py-2 rounded-lg font-semibold mt-6"
            >
                Create Account
            </button>

            <p class="text-sm text-center mt-4">
                Already have an account?
                <a
                    href="/login"
                    class="text-brand-blue font-semibold hover:underline"
                    >Login</a
                >
            </p>
        </div>
    </div>
</template>
