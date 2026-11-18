<script setup lang="ts">
import GenerationImage from '@/components/generations/GenerationImage.vue';
import StatusBadge from '@/components/generations/StatusBadge.vue';
import { usePollWhileUnfinished } from '@/composables/usePollWhileUnfinished';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCredits } from '@/lib/money';
import { type BreadcrumbItem } from '@/types';
import type { CostBreakdown, Generation } from '@/types/generation';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    generation: { data: Generation };
    costBreakdown: CostBreakdown | null;
}>();

const generation = computed(() => props.generation.data);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'History', href: '/generations' },
    { title: `Generation #${props.generation.data.id}`, href: `/generations/${props.generation.data.id}` },
];

usePollWhileUnfinished(
    computed(() => !generation.value.is_finished),
    ['generation', 'costBreakdown'],
);
</script>

<template>
    <Head :title="`Generation #${generation.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto grid w-full max-w-5xl gap-6 p-4 lg:grid-cols-2">
            <div class="aspect-square overflow-hidden rounded-xl border">
                <GenerationImage :generation="generation" />
            </div>

            <div class="space-y-6 text-sm">
                <div class="space-y-2">
                    <StatusBadge :status="generation.status" :label="generation.display_status" />
                    <p class="text-base">{{ generation.prompt }}</p>
                    <p class="text-muted-foreground">{{ generation.display_size }} · {{ generation.display_quality }}</p>
                    <p v-if="generation.error_message" class="rounded-md bg-red-50 p-3 text-red-700 dark:bg-red-950/40 dark:text-red-300">
                        {{ generation.error_message }}
                    </p>
                </div>

                <dl class="grid grid-cols-2 gap-2">
                    <dt class="text-muted-foreground">Created</dt>
                    <dd>{{ new Date(generation.created_at).toLocaleString() }}</dd>
                    <dt class="text-muted-foreground">Completed</dt>
                    <dd>{{ generation.completed_at ? new Date(generation.completed_at).toLocaleString() : '—' }}</dd>
                    <dt class="text-muted-foreground">Typical estimate</dt>
                    <dd>{{ formatCredits(generation.estimated_typical_price) }}</dd>
                    <dt class="text-muted-foreground">Maximum estimate</dt>
                    <dd>{{ formatCredits(generation.estimated_maximum_price) }}</dd>
                    <dt class="font-medium">Charged</dt>
                    <dd class="font-medium">{{ generation.actual_price === null ? 'Pending' : formatCredits(generation.actual_price) }}</dd>
                </dl>

                <div v-if="generation.reference_images.length" class="space-y-1">
                    <p class="text-muted-foreground">Reference images</p>
                    <a v-for="url in generation.reference_images" :key="url" :href="url" target="_blank" rel="noopener" class="block truncate underline">
                        {{ url }}
                    </a>
                </div>

                <table v-if="costBreakdown" class="w-full">
                    <thead class="text-left text-muted-foreground">
                        <tr>
                            <th class="pb-1 font-normal">Token type</th>
                            <th class="pb-1 text-right font-normal">Tokens</th>
                            <th class="pb-1 text-right font-normal">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in costBreakdown.rows" :key="row.token_type">
                            <td>{{ row.token_type }}</td>
                            <td class="text-right">{{ row.tokens.toLocaleString() }}</td>
                            <td class="text-right">{{ formatCredits(row.cost) }}</td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">Pixelforge cost</td>
                            <td class="text-right">{{ formatCredits(costBreakdown.provider_cost) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">Service fee (25%)</td>
                            <td class="text-right">{{ formatCredits(costBreakdown.markup) }}</td>
                        </tr>
                        <tr class="font-medium">
                            <td colspan="2">Total charged</td>
                            <td class="text-right">{{ formatCredits(costBreakdown.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
