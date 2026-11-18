<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCredits } from '@/lib/money';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps<{
    stats: {
        images_generated: number;
        failed: number;
        in_progress: number;
        spent: string;
        added: string;
    };
}>();

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="space-y-2">
                <h2 class="font-medium">Images</h2>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border p-4">
                        <p class="text-sm text-muted-foreground">Images generated</p>
                        <p class="text-2xl font-semibold">{{ stats.images_generated }}</p>
                    </div>
                    <div class="rounded-xl border p-4">
                        <p class="text-sm text-muted-foreground">In progress</p>
                        <p class="text-2xl font-semibold">{{ stats.in_progress }}</p>
                    </div>
                    <div class="rounded-xl border p-4">
                        <p class="text-sm text-muted-foreground">Failed</p>
                        <p class="text-2xl font-semibold">{{ stats.failed }}</p>
                        <p class="text-xs text-muted-foreground">Refunded in full</p>
                    </div>
                </div>
            </section>

            <section class="space-y-2">
                <h2 class="font-medium">Credits</h2>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border p-4">
                        <p class="text-sm text-muted-foreground">Available balance</p>
                        <p class="text-2xl font-semibold">{{ formatCredits(page.props.auth.balance) }}</p>
                    </div>
                    <div class="rounded-xl border p-4">
                        <p class="text-sm text-muted-foreground">Total spent</p>
                        <p class="text-2xl font-semibold">{{ formatCredits(stats.spent) }}</p>
                    </div>
                    <div class="rounded-xl border p-4">
                        <p class="text-sm text-muted-foreground">Total added</p>
                        <p class="text-2xl font-semibold">{{ formatCredits(stats.added) }}</p>
                        <p class="text-xs text-muted-foreground">Starting credits and purchases</p>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap gap-2">
                <Button as-child>
                    <Link :href="route('generations.create')">Generate image</Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="route('wallet.index')">Buy credits</Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
