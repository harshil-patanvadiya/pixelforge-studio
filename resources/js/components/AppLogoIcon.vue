<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="className" v-bind="$attrs">
        <rect x="2" y="2" width="9" height="9" rx="1.5" />
        <rect x="13" y="2" width="9" height="9" rx="1.5" opacity="0.6" />
        <rect x="2" y="13" width="9" height="9" rx="1.5" opacity="0.6" />
        <rect x="13" y="13" width="9" height="9" rx="1.5" opacity="0.3" />
    </svg>
</template>
