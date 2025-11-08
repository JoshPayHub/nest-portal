<template>
    <aside class="bg-brand-blue p-4 w-[300px] flex flex-col h-full min-h-0">

        <!-- Scrollable sidebar buttons Start -->
        <div class="flex-1 overflow-y-auto min-h-0 scrollbar-custom">
            <div class="grid gap-2">
                <button class="bg-white text-brand-blue text-2xl py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2">
                    <div>
                        <img src="@/assets/dashboard/dashboard.png" class="h-6" alt="">
                    </div>
                    <div>Dashboard</div>
                </button>

                <button class="bg-white text-brand-blue text-2xl py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2">
                    <div class="ps-1">
                        <img src="@/assets/dashboard/sample.png" class="h-6" alt="">
                    </div>
                    <div>Sample Menu</div>
                </button>

                <button class="bg-white text-brand-blue text-2xl py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2">
                    <div class="ps-1">
                        <img src="@/assets/dashboard/sample.png" class="h-6" alt="">
                    </div>
                    <div>Sample Menu</div>
                </button>

                <button class="bg-white text-brand-blue text-2xl py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2">
                    <div class="ps-1">
                        <img src="@/assets/dashboard/sample.png" class="h-6" alt="">
                    </div>
                    <div>Sample Menu</div>
                </button>

                <button class="bg-white text-brand-blue text-2xl py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2">
                    <div class="ps-1">
                        <img src="@/assets/dashboard/sample.png" class="h-6" alt="">
                    </div>
                    <div>Sample Menu</div>
                </button>

                <button class="bg-white text-brand-blue text-2xl py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2">
                    <div class="ps-1">
                        <img src="@/assets/dashboard/sample.png" class="h-6" alt="">
                    </div>
                    <div>Sample Menu</div>
                </button>
            </div>
        </div>
        <!-- Scrollable sidebar buttons End-->

        <!-- Logout pinned at bottom Start -->
        <div class="mt-4">
            <a href="/"
            class="bg-white text-brand-blue text-2xl py-2 px-3 flex gap-3 justify-center items-center rounded-md w-full">
            <div class="ps-1">
                <img src="@/assets/dashboard/logout.png" class="h-6" alt="">
            </div>
            <div>Log Out</div>
            </a>
        </div>
        <!-- Logout pinned at bottom End -->

    </aside>
</template>
