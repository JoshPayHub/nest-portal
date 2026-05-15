<script setup>
import { ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { Pencil } from "lucide-vue-next";
import { toastStore } from "@/stores/toast";

import { Button } from "@/Components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";

const props = defineProps({
    sss: Array,
    tax: Array,
    settings: Array,
    activeTab: String,
});

const isOpen = ref(false);
const type = ref("");
const url = ref("");

const form = useForm({
    id: null,
    min_salary: null,
    max_salary: null,
    msc: null,
    ee_share: null,
    er_share: null,
    wisp_ee: null,
    wisp_er: null,
    ec_er: null,
    base_tax: null,
    excess_rate: null,
    over_amount: null,
    key: null,
    value: null,
});

// Tab Switching via URL
const switchTab = (tab) => {
    router.get(
        "/hr/salary-deductions",
        { tab: tab },
        { preserveState: true, preserveScroll: true },
    );
};

// Formatters
const formatNumber = (val) => {
    return Number(val)
        .toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
        })
        .replace(/\.0+$/, "");
};

const peso = (val) => `₱${formatNumber(val)}`;
const percent = (val) => `${formatNumber(val * 100)}%`;

const isPercentField = (key) => {
    return key?.toLowerCase().includes("rate");
};

// Modal Logic
const openEdit = (row, t, u) => {
    type.value = t;
    url.value = u;
    form.clearErrors();
    form.defaults({ ...row });
    form.reset();

    // UI Conversions
    if (t === "tax") form.excess_rate = row.excess_rate * 100;
    if (t === "settings" && isPercentField(row.key))
        form.value = row.value * 100;

    isOpen.value = true;
};

const normalizeForm = () => {
    if (type.value === "tax") form.excess_rate = Number(form.excess_rate) / 100;
    if (type.value === "settings" && isPercentField(form.key)) {
        form.value = Number(form.value) / 100;
    }
};

const submit = () => {
    normalizeForm();
    form.put(url.value, {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            toastStore.show("Settings updated successfully.", "success");
        },
        onError: (e) => toastStore.show(Object.values(e)[0], "danger"),
    });
};
</script>

<template>
    <div class="p-6 space-y-6">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle class="text-3xl font-bold text-brand-blue">
                    Salary Deductions
                </CardTitle>
            </CardHeader>

            <CardContent>
                <div class="flex gap-3 mb-6">
                    <Button
                        v-for="t in ['sss', 'philhealth', 'pagibig', 'tax']"
                        :key="t"
                        @click="switchTab(t)"
                        :variant="activeTab === t ? 'default' : 'outline'"
                        :class="[
                            'capitalize px-6',
                            activeTab === t
                                ? 'bg-brand-blue hover:bg-green-700'
                                : 'border-2',
                        ]"
                    >
                        {{ t }}
                    </Button>
                </div>

                <div v-if="activeTab === 'sss'">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Range</TableHead>
                                <TableHead>MSC</TableHead>
                                <TableHead>EE</TableHead>
                                <TableHead>ER</TableHead>
                                <TableHead>WISP EE</TableHead>
                                <TableHead>WISP ER</TableHead>
                                <TableHead>EC</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="row in sss" :key="row.id">
                                <TableCell
                                    >{{ peso(row.min_salary) }} -
                                    {{ peso(row.max_salary) }}</TableCell
                                >
                                <TableCell>{{ peso(row.msc) }}</TableCell>
                                <TableCell>{{ peso(row.ee_share) }}</TableCell>
                                <TableCell>{{ peso(row.er_share) }}</TableCell>
                                <TableCell>{{ peso(row.wisp_ee) }}</TableCell>
                                <TableCell>{{ peso(row.wisp_er) }}</TableCell>
                                <TableCell>{{ peso(row.ec_er) }}</TableCell>

                                <TableCell class="text-right px-6">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="
                                            openEdit(
                                                row,
                                                'sss',
                                                `/hr/salary-deductions/sss/${row.id}`,
                                            )
                                        "
                                        class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div v-if="activeTab === 'tax'">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Range</TableHead>
                                <TableHead>Base Tax</TableHead>
                                <TableHead>Rate</TableHead>
                                <TableHead>Over</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="row in tax" :key="row.id">
                                <TableCell
                                    >{{ peso(row.min_salary) }} -
                                    {{ peso(row.max_salary) }}</TableCell
                                >
                                <TableCell>{{ peso(row.base_tax) }}</TableCell>
                                <TableCell>{{
                                    percent(row.excess_rate)
                                }}</TableCell>
                                <TableCell>{{
                                    peso(row.over_amount)
                                }}</TableCell>

                                <TableCell class="text-right px-6">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="
                                            openEdit(
                                                row,
                                                'tax',
                                                `/hr/salary-deductions/tax/${row.id}`,
                                            )
                                        "
                                        class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div
                    v-if="activeTab === 'philhealth' || activeTab === 'pagibig'"
                >
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Key</TableHead>
                                <TableHead>Value</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="row in settings.filter((s) =>
                                    s.key.includes(activeTab),
                                )"
                                :key="row.id"
                            >
                                <TableCell class="capitalize">{{
                                    row.key.replace(/_/g, " ")
                                }}</TableCell>
                                <TableCell>
                                    {{
                                        isPercentField(row.key)
                                            ? percent(row.value)
                                            : peso(row.value)
                                    }}
                                </TableCell>

                                <TableCell class="text-right px-6">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="
                                            openEdit(
                                                row,
                                                'settings',
                                                `/hr/salary-deductions/settings/${row.id}`,
                                            )
                                        "
                                        class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <Dialog :open="isOpen" @update:open="isOpen = $event">
            <DialogContent
                class="max-w-lg overflow-hidden flex flex-col max-h-[90vh]"
            >
                <DialogHeader>
                    <DialogTitle
                        >Edit {{ type.toUpperCase() }} Entry</DialogTitle
                    >
                </DialogHeader>

                <div class="flex-1 overflow-y-auto p-1 space-y-4">
                    <template v-if="type === 'sss'">
                        <div
                            v-for="field in [
                                'min_salary',
                                'max_salary',
                                'msc',
                                'ee_share',
                                'er_share',
                                'wisp_ee',
                                'wisp_er',
                                'ec_er',
                            ]"
                            :key="field"
                        >
                            <label
                                class="text-xs font-semibold uppercase text-gray-500 ms-1"
                                >{{ field.replace(/_/g, " ") }}</label
                            >
                            <input
                                v-model="form[field]"
                                type="number"
                                step="any"
                                class="input"
                            />
                        </div>
                    </template>

                    <template v-if="type === 'tax'">
                        <div
                            v-for="field in [
                                'min_salary',
                                'max_salary',
                                'base_tax',
                                'excess_rate',
                                'over_amount',
                            ]"
                            :key="field"
                        >
                            <label
                                class="text-xs font-semibold uppercase text-gray-500 ms-1"
                            >
                                {{ field.replace(/_/g, " ") }}
                                {{ field === "excess_rate" ? "(%)" : "" }}
                            </label>
                            <input
                                v-model="form[field]"
                                type="number"
                                step="any"
                                class="input"
                            />
                        </div>
                    </template>

                    <template v-if="type === 'settings'">
                        <label
                            class="text-xs font-semibold uppercase text-gray-500 ms-1"
                        >
                            Value {{ isPercentField(form.key) ? "(%)" : "(₱)" }}
                        </label>
                        <input
                            v-model="form.value"
                            type="number"
                            step="any"
                            class="input"
                        />
                    </template>
                </div>

                <DialogFooter class="pt-4 border-t">
                    <Button variant="secondary" @click="isOpen = false"
                        >Cancel</Button
                    >
                    <Button @click="submit" :disabled="form.processing"
                        >Save Changes</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    padding: 10px;
    border-radius: 6px;
    outline-color: #2563eb;
}
</style>
