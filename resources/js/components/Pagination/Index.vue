<script setup>
import { computed } from "vue";
import { router } from "@inertiajs/vue3"; // Missing import
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationPrevious,
    PaginationNext,
} from "@/Components/ui/pagination";

// Define the props to receive the data from the parent
const props = defineProps({
    links: {
        type: Object,
        required: true,
    },
});

/* =========================
   BACKEND PAGINATION
========================= */
const paginator = computed(() => props.links);

const perPage = computed(() => paginator.value.per_page);
const totalRows = computed(() => paginator.value.total);
const currentPage = computed(() => paginator.value.current_page);
const lastPage = computed(() => paginator.value.last_page);

/* =========================
   SLIDING PAGE NUMBERS
========================= */
const visiblePages = computed(() => {
    const pages = [];
    const start = Math.max(1, currentPage.value);
    const end = Math.min(start + 1, lastPage.value);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

const showingFrom = computed(() => (currentPage.value - 1) * perPage.value + 1);
const showingTo = computed(() =>
    Math.min(currentPage.value * perPage.value, totalRows.value),
);

/* =========================
   NAVIGATION
========================= */
const goToPage = (url) => {
    if (!url) return;
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <div
        v-if="paginator.last_page > 1"
        class="flex justify-between gap-4 pt-6 px-6 items-center"
    >
        <div class="text-[12px] text-slate-500 font-medium">
            Page
            <span class="font-bold text-slate-700">{{ currentPage }}</span> of
            <span class="font-bold text-slate-700">{{ lastPage }}</span> |
            Showing
            <span class="font-bold text-slate-700"
                >{{ showingFrom }}–{{ showingTo }}</span
            >
            of
            <span class="font-bold text-slate-700">{{ totalRows }}</span> total
            rows
        </div>

        <div>
            <Pagination>
                <PaginationContent class="flex items-center gap-2">
                    <PaginationPrevious
                        :disabled="!paginator.prev_page_url"
                        @click="goToPage(paginator.prev_page_url)"
                    />

                    <PaginationItem v-for="page in visiblePages" :key="page">
                        <button
                            @click="
                                goToPage(
                                    paginator.links.find((l) => l.label == page)
                                        ?.url,
                                )
                            "
                            class="px-4 py-2 text-sm rounded-lg font-semibold transition-all"
                            :class="
                                page === currentPage
                                    ? 'bg-brand-blue text-white shadow'
                                    : 'bg-white border text-slate-600 hover:bg-slate-100'
                            "
                        >
                            {{ page }}
                        </button>
                    </PaginationItem>

                    <PaginationNext
                        :disabled="!paginator.next_page_url"
                        @click="goToPage(paginator.next_page_url)"
                    />
                </PaginationContent>
            </Pagination>
        </div>
    </div>
</template>
