<script setup lang="ts">
import type { Generation } from '@/types/generation';
import { computed } from 'vue';

const props = defineProps<{
    generation: Generation;
}>();

const placeholderText = computed(() => {
    if (props.generation.has_expired_image) {
        return 'Image link expired';
    }

    if (props.generation.status === 'failed') {
        return 'No image';
    }

    return 'Generating…';
});
</script>

<template>
    <img v-if="generation.image_url" :src="generation.image_url" :alt="generation.prompt" class="h-full w-full object-cover" loading="lazy" />
    <div v-else class="flex h-full w-full items-center justify-center bg-muted text-sm text-muted-foreground">
        {{ placeholderText }}
    </div>
</template>
