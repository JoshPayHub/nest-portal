<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { CheckCircle2, AlertCircle, Lock, Ban } from "lucide-vue-next";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from "@/Components/ui/card";

const props = defineProps({
    departments: Array,
    positions: Array,
    statuses: Array,
});

const alertStatus = ref({
    show: false,
    title: "",
    message: "",
    variant: "default",
});

const form = useForm({
    name: "",
    email: "",
    phone: "",
    gender: "",
    address: "",
    department_id: "",
    position_id: "",
    status_id: "",
});

// Watch status_id: If system status is Inactive (2), clear selections
watch(
    () => form.status_id,
    (newStatus) => {
        if (newStatus == 2) {
            form.department_id = "";
            form.position_id = "";
        }
    },
);

const submit = () => {
    alertStatus.value.show = false;
    form.post("/hr/add-employees/store", {
        onSuccess: () => {
            form.reset();
            alertStatus.value = {
                show: true,
                title: "Action Successful",
                message: "Employee has been created and saved to the database.",
                variant: "default",
            };
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            alertStatus.value = {
                show: true,
                title: "Registration Failed",
                message: firstError || "Please check your inputs.",
                variant: "destructive",
            };
        },
    });
};
</script>

<template>
    <div class="p-6">
        <Card class="shadow-sm border-blue-100 max-w-5xl mx-auto">
            <CardHeader class="pb-5">
                <CardTitle
                    class="text-4xl font-extrabold text-brand-blue tracking-tight"
                >
                    Employee Onboarding
                </CardTitle>
                <CardDescription class="text-lg mt-2">
                    Complete the details below to register a new staff member.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <transition name="fade">
                    <Alert
                        v-if="alertStatus.show"
                        :variant="alertStatus.variant"
                        class="mb-8"
                    >
                        <component
                            :is="
                                alertStatus.variant === 'destructive'
                                    ? AlertCircle
                                    : CheckCircle2
                            "
                            class="h-5 w-5"
                        />
                        <AlertTitle class="font-bold">{{
                            alertStatus.title
                        }}</AlertTitle>
                        <AlertDescription>{{
                            alertStatus.message
                        }}</AlertDescription>
                    </Alert>
                </transition>

                <form
                    @submit.prevent="submit"
                    id="employeeForm"
                    class="space-y-8"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <Label for="name" class="text-base font-semibold"
                                >Full Name</Label
                            >
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Juan Dela Cruz"
                                class="h-12 border-slate-200"
                                required
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="space-y-3">
                            <Label for="email" class="text-base font-semibold"
                                >Work Email</Label
                            >
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="juan@company.com"
                                class="h-12 border-slate-200"
                                required
                            />
                            <p
                                v-if="form.errors.email"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-3">
                            <Label class="text-base font-semibold"
                                >System Status</Label
                            >
                            <select
                                v-model="form.status_id"
                                class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                                required
                            >
                                <option value="">Select Status</option>
                                <option
                                    v-for="st in statuses"
                                    :key="st.id"
                                    :value="st.id"
                                >
                                    {{ st.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-3 relative">
                            <Label
                                class="text-base font-semibold flex items-center gap-2"
                            >
                                Department
                                <Ban
                                    v-if="form.status_id == 2"
                                    class="h-4 w-4 text-red-500"
                                />
                            </Label>
                            <div class="relative">
                                <select
                                    v-model="form.department_id"
                                    :disabled="form.status_id == 2"
                                    class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none disabled:bg-slate-100 disabled:cursor-not-allowed"
                                    :required="form.status_id != 2"
                                >
                                    <option value="">
                                        {{
                                            form.status_id == 2
                                                ? "N/A (Inactive)"
                                                : "Select Department"
                                        }}
                                    </option>
                                    <option
                                        v-for="dept in departments"
                                        :key="dept.id"
                                        :value="dept.id"
                                        :disabled="dept.status_id == 2"
                                        :class="{
                                            'text-slate-400 italic':
                                                dept.status_id == 2,
                                        }"
                                    >
                                        {{ dept.name }}
                                        {{
                                            dept.status_id == 2
                                                ? "(Currently Inactive)"
                                                : ""
                                        }}
                                    </option>
                                </select>
                                <Lock
                                    v-if="form.status_id == 2"
                                    class="absolute right-8 top-3.5 h-5 w-5 text-slate-400"
                                />
                            </div>
                            <p
                                v-if="form.errors.department_id"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.department_id }}
                            </p>
                        </div>

                        <div class="space-y-3 relative">
                            <Label
                                class="text-base font-semibold flex items-center gap-2"
                            >
                                Position
                                <Ban
                                    v-if="form.status_id == 2"
                                    class="h-4 w-4 text-red-500"
                                />
                            </Label>
                            <div class="relative">
                                <select
                                    v-model="form.position_id"
                                    :disabled="form.status_id == 2"
                                    class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none disabled:bg-slate-100 disabled:cursor-not-allowed"
                                    :required="form.status_id != 2"
                                >
                                    <option value="">
                                        {{
                                            form.status_id == 2
                                                ? "N/A (Inactive)"
                                                : "Select Position"
                                        }}
                                    </option>
                                    <option
                                        v-for="pos in positions"
                                        :key="pos.id"
                                        :value="pos.id"
                                        :disabled="pos.status_id == 2"
                                        :class="{
                                            'text-slate-400 italic':
                                                pos.status_id == 2,
                                        }"
                                    >
                                        {{ pos.name }}
                                        {{
                                            pos.status_id == 2
                                                ? "(Currently Inactive)"
                                                : ""
                                        }}
                                    </option>
                                </select>
                                <Lock
                                    v-if="form.status_id == 2"
                                    class="absolute right-8 top-3.5 h-5 w-5 text-slate-400"
                                />
                            </div>
                            <p
                                v-if="form.errors.position_id"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.position_id }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <Label for="phone" class="text-base font-semibold"
                                >Phone Number</Label
                            >
                            <Input
                                id="phone"
                                v-model="form.phone"
                                placeholder="09123456789"
                                class="h-12 border-slate-200"
                            />
                        </div>
                        <div class="space-y-3">
                            <Label for="gender" class="text-base font-semibold"
                                >Gender</Label
                            >
                            <select
                                id="gender"
                                v-model="form.gender"
                                class="flex h-12 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                            >
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                                <option value="Prefer not to say">
                                    Prefer not to say
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <Label for="address" class="text-base font-semibold"
                            >Residential Address</Label
                        >
                        <Input
                            id="address"
                            v-model="form.address"
                            placeholder="Full address details..."
                            class="h-12 border-slate-200"
                        />
                    </div>
                </form>
            </CardContent>

            <CardFooter
                class="flex items-center gap-6 pt-6 border-t border-slate-100"
            >
                <Button
                    form="employeeForm"
                    type="submit"
                    class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-10 font-bold shadow-md transition-all active:scale-95"
                    :disabled="form.processing"
                >
                    {{ form.processing ? "Processing..." : "Add Employee" }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    @click="
                        form.reset();
                        alertStatus.show = false;
                    "
                    class="h-12 px-10 border-2"
                >
                    Clear Fields
                </Button>
            </CardFooter>
        </Card>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
