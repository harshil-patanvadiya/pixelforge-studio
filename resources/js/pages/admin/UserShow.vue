<script setup lang="ts">
import GenerationImage from '@/components/generations/GenerationImage.vue';
import StatusBadge from '@/components/generations/StatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCredits } from '@/lib/money';
import { type BreadcrumbItem } from '@/types';
import type { Generation, Paginated } from '@/types/generation';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    user: { id: number; name: string; email: string | null };
    generations: Paginated<Generation>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: '/admin/users' },
    { title: props.user.name, href: `/admin/users/${props.user.id}` },
];
</script>

<template>
    <Head :title="user.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div>
                <h1 class="text-lg font-semibold">{{ user.name }}</h1>
                <p v-if="user.email" class="text-sm text-muted-foreground">{{ user.email }}</p>
            </div>

            <p v-if="generations.data.length === 0" class="rounded-xl border p-8 text-center text-muted-foreground">
                This user hasn't generated any images yet.
            </p>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="generation in generations.data" :key="generation.id" class="overflow-hidden rounded-xl border">
                    <div class="aspect-square">
                        <GenerationImage :generation="generation" />
                    </div>
                    <div class="space-y-2 p-3 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <StatusBadge :status="generation.status" :label="generation.display_status" />
                            <span class="text-xs text-muted-foreground">{{ new Date(generation.created_at).toLocaleString() }}</span>
                        </div>
                        <p class="line-clamp-3">{{ generation.prompt }}</p>
                        <p class="text-xs text-muted-foreground">{{ generation.display_size }} · {{ generation.display_quality }}</p>
                        <p v-if="generation.error_message" class="text-xs text-red-600">{{ generation.error_message }}</p>
                        <div class="flex justify-between text-xs">
                            <span class="text-muted-foreground">Charged</span>
                            <span class="font-medium">{{ generation.actual_price === null ? 'Pending' : formatCredits(generation.actual_price) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <nav v-if="generations.meta.last_page > 1" class="flex flex-wrap gap-1">
                <template v-for="link in generations.meta.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        :class="['rounded-md border px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : '']"
                    >
                        <span v-html="link.label" />
                    </Link>
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
