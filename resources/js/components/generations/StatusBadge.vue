<script setup lang="ts">
import type { GenerationStatus } from '@/types/generation';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status: GenerationStatus;
    label: string;
}>();

const colours: Record<GenerationStatus, string> = {
    pending: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    queued: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    succeeded: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    failed: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
};
</script>

<template>
    <span :class="['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium', colours[status]]">
        <LoaderCircle v-if="status !== 'succeeded' && status !== 'failed'" class="size-3 animate-spin" />
        {{ label }}
    </span>
</template>
