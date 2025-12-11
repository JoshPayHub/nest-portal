<template>
    <div class="p-6">
        <!-- Page Title -->
        <h1 class="text-3xl font-bold text-brand-blue mb-6">
            Add Employee Record
        </h1>

        <!-- Card Container -->
        <div class="bg-white shadow-lg rounded-xl p-6 border">
            <!-- Employee Basic Info -->
            <h2 class="text-xl font-semibold text-gray-700 mb-4">
                Personal Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-gray-700">First Name</label>
                    <input
                        type="text"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                        placeholder="First Name"
                    />
                </div>

                <div>
                    <label class="text-gray-700">Middle Name</label>
                    <input
                        type="text"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                        placeholder="Middle Name"
                    />
                </div>

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
                    placeholder="Enter email"
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

            <!-- Birthdate + Gender -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
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

            <!-- Employee Job Information -->
            <h2 class="text-xl font-semibold text-gray-700 mt-8 mb-4">
                Employee Details
            </h2>

            <!-- Position / Department -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-gray-700">Position</label>
                    <input
                        type="text"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                        placeholder="Employee Position"
                    />
                </div>

                <div>
                    <label class="text-gray-700">Department</label>
                    <input
                        type="text"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                        placeholder="Department Name"
                    />
                </div>
            </div>

            <!-- Hire Date / Employment Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="text-gray-700">Date Hired</label>
                    <input
                        type="date"
                        class="w-full border rounded-lg px-3 py-2 mt-1"
                    />
                </div>

                <div>
                    <label class="text-gray-700">Employment Type</label>
                    <select class="w-full border rounded-lg px-3 py-2 mt-1">
                        <option>Full-time</option>
                        <option>Part-time</option>
                        <option>Contract</option>
                        <option>Intern</option>
                    </select>
                </div>
            </div>

            <!-- Salary -->
            <div class="mt-4">
                <label class="text-gray-700">Salary</label>
                <input
                    type="number"
                    class="w-full border rounded-lg px-3 py-2 mt-1"
                    placeholder="Monthly Salary"
                />
            </div>

            <!-- Button -->
            <button
                class="w-full bg-brand-blue hover:bg-green-700 text-white py-3 rounded-lg font-semibold mt-8"
            >
                Save Employee Record
            </button>
        </div>
    </div>
</template>
