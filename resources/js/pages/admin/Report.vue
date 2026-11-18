<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatUsd } from '@/lib/money';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Money {
    provider_cost: string;
    charges: string;
    profit: string;
}

interface Counts {
    generations: number;
    succeeded: number;
    failed: number;
}

interface DayLine extends Money, Counts {
    day: string;
    pixelforge_cost: string | null;
    has_mismatch: boolean;
}

const props = defineProps<{
    filters: { start_date: string; end_date: string };
    report: {
        totals: Money & Counts & { registered_users: number; pixelforge_cost: string | null };
        days: DayLine[];
        has_pixelforge_usage: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

const form = useForm({ ...props.filters });

function applyFilters(): void {
    form.get(route('dashboard'), { preserveScroll: true });
}

const cellClass = 'px-3 py-2 text-right tabular-nums';

const DAYS_PER_PAGE = 10;

const currentPage = ref(1);

const lastPage = computed(() => Math.max(1, Math.ceil(props.report.days.length / DAYS_PER_PAGE)));

const visibleDays = computed(() => props.report.days.slice((currentPage.value - 1) * DAYS_PER_PAGE, currentPage.value * DAYS_PER_PAGE));

watch(
    () => props.report.days,
    () => (currentPage.value = 1),
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <form class="flex flex-wrap items-end justify-end gap-3" @submit.prevent="applyFilters">
                <div class="grid gap-1">
                    <Label for="start_date">From (UTC)</Label>
                    <Input id="start_date" v-model="form.start_date" type="date" />
                </div>
                <div class="grid gap-1">
                    <Label for="end_date">To (UTC)</Label>
                    <Input id="end_date" v-model="form.end_date" type="date" />
                </div>
                <Button :disabled="form.processing">Apply</Button>
                <InputError class="w-full" :message="form.errors.start_date ?? form.errors.end_date" />
            </form>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border p-4">
                    <p class="text-sm text-muted-foreground">Registered users</p>
                    <p class="text-2xl font-semibold">{{ report.totals.registered_users }}</p>
                </div>
                <div class="rounded-xl border p-4">
                    <p class="text-sm text-muted-foreground">Generations</p>
                    <p class="text-2xl font-semibold">{{ report.totals.generations }}</p>
                    <p class="text-xs text-muted-foreground">{{ report.totals.succeeded }} succeeded · {{ report.totals.failed }} failed</p>
                </div>
                <div class="rounded-xl border p-4">
                    <p class="text-sm text-muted-foreground">Charged to users</p>
                    <p class="text-2xl font-semibold">{{ formatUsd(report.totals.charges) }}</p>
                </div>
                <div class="rounded-xl border p-4">
                    <p class="text-sm text-muted-foreground">Provider cost · profit</p>
                    <p class="text-2xl font-semibold">{{ formatUsd(report.totals.provider_cost) }}</p>
                    <p class="text-xs text-green-700 dark:text-green-400">Profit {{ formatUsd(report.totals.profit) }}</p>
                </div>
            </div>

            <p v-if="!report.has_pixelforge_usage" class="rounded-md bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                Pixelforge usage could not be loaded, so provider costs are not reconciled.
            </p>

            <section class="space-y-2">
                <h2 class="font-medium">Daily</h2>
                <div class="overflow-x-auto rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th class="px-3 py-2 text-left font-normal">Day</th>
                                <th :class="cellClass" class="font-normal">Generations</th>
                                <th :class="cellClass" class="font-normal">Charged</th>
                                <th :class="cellClass" class="font-normal">Provider cost</th>
                                <th :class="cellClass" class="font-normal">Pixelforge reports</th>
                                <th :class="cellClass" class="font-normal">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="day in visibleDays" :key="day.day" :class="['border-t', day.has_mismatch ? 'bg-red-50 dark:bg-red-950/40' : '']">
                                <td class="px-3 py-2">{{ day.day }}</td>
                                <td :class="cellClass">{{ day.succeeded }} / {{ day.generations }}</td>
                                <td :class="cellClass">{{ formatUsd(day.charges) }}</td>
                                <td :class="cellClass">{{ formatUsd(day.provider_cost) }}</td>
                                <td :class="[cellClass, day.has_mismatch ? 'font-semibold text-red-700 dark:text-red-300' : '']">
                                    {{ formatUsd(day.pixelforge_cost) }}
                                    <span v-if="day.has_mismatch"> (mismatch)</span>
                                </td>
                                <td :class="cellClass">{{ formatUsd(day.profit) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs text-muted-foreground">Generations column shows succeeded / total.</p>
                    <div v-if="lastPage > 1" class="flex items-center gap-2">
                        <Button variant="outline" size="sm" :disabled="currentPage === 1" @click="currentPage--">Previous</Button>
                        <span class="text-sm text-muted-foreground">Page {{ currentPage }} of {{ lastPage }}</span>
                        <Button variant="outline" size="sm" :disabled="currentPage === lastPage" @click="currentPage++">Next</Button>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
