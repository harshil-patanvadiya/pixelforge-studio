<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCredits, formatUsd } from '@/lib/money';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Plus, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Option {
    value: string;
    label: string;
}

interface Estimate {
    typical_price: string;
    maximum_price: string;
    can_afford: boolean;
}

defineProps<{
    sizes: Option[];
    qualities: Option[];
}>();

const page = usePage<SharedData>();

const maximumReferenceImages = 4;

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Generate', href: '/generations/create' }];

const form = useForm({
    prompt: '',
    size: '1024x1024',
    quality: 'medium',
    reference_images: [] as string[],
});

const estimate = ref<Estimate | null>(null);
const hasEstimateError = ref(false);

const fieldClass =
    'flex w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 md:text-sm';

function xsrfToken(): string {
    const cookie = document.cookie.split('; ').find((entry) => entry.startsWith('XSRF-TOKEN='));

    return cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
}

async function refreshEstimate(): Promise<void> {
    const response = await fetch(route('generations.estimate'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
        body: JSON.stringify({
            prompt: form.prompt,
            size: form.size,
            quality: form.quality,
            reference_images: form.reference_images,
        }),
    });

    hasEstimateError.value = !response.ok;
    estimate.value = response.ok ? await response.json() : null;
}

watchDebounced(() => [form.prompt, form.size, form.quality, form.reference_images.length], refreshEstimate, {
    debounce: 300,
    immediate: true,
});

function addReferenceImage(): void {
    form.reference_images.push('');
}

function removeReferenceImage(index: number): void {
    form.reference_images.splice(index, 1);
}

function submit(): void {
    form.post(route('generations.store'), {
        onSuccess: () => form.reset('prompt', 'reference_images'),
    });
}
</script>

<template>
    <Head title="Generate" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto grid w-full max-w-5xl gap-6 p-4 lg:grid-cols-[1fr_18rem]">
            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="prompt">Prompt</Label>
                    <textarea
                        id="prompt"
                        v-model="form.prompt"
                        rows="5"
                        maxlength="4000"
                        :class="fieldClass"
                        placeholder="A lighthouse on a cliff at dawn, oil painting"
                    />
                    <p class="text-xs text-muted-foreground">{{ form.prompt.length }} / 4000 characters</p>
                    <InputError :message="form.errors.prompt" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="size">Size</Label>
                        <select id="size" v-model="form.size" :class="fieldClass">
                            <option v-for="size in sizes" :key="size.value" :value="size.value">{{ size.label }}</option>
                        </select>
                        <InputError :message="form.errors.size" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="quality">Quality</Label>
                        <select id="quality" v-model="form.quality" :class="fieldClass">
                            <option v-for="quality in qualities" :key="quality.value" :value="quality.value">{{ quality.label }}</option>
                        </select>
                        <InputError :message="form.errors.quality" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Reference image URLs (optional, up to {{ maximumReferenceImages }})</Label>
                    <div v-for="(_, index) in form.reference_images" :key="index" class="space-y-1">
                        <div class="flex gap-2">
                            <Input v-model="form.reference_images[index]" type="url" placeholder="https://example.com/style.png" />
                            <Button type="button" variant="ghost" size="icon" @click="removeReferenceImage(index)">
                                <X />
                            </Button>
                        </div>
                        <InputError :message="form.errors[`reference_images.${index}` as keyof typeof form.errors]" />
                    </div>
                    <InputError :message="form.errors.reference_images" />
                    <Button
                        v-if="form.reference_images.length < maximumReferenceImages"
                        type="button"
                        variant="outline"
                        class="w-fit"
                        @click="addReferenceImage"
                    >
                        <Plus /> Add reference image
                    </Button>
                </div>

                <InputError :message="form.errors.balance" />
                <InputError :message="form.errors.message" />
                <Button :disabled="form.processing || estimate?.can_afford === false">Generate image</Button>
            </form>

            <aside class="h-fit space-y-3 rounded-xl border p-4">
                <h2 class="font-medium">Estimated cost</h2>
                <p v-if="hasEstimateError" class="text-sm text-red-600">The estimate is unavailable right now.</p>
                <template v-else>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Typical</span>
                        <span>{{ formatUsd(estimate?.typical_price ?? null) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-medium">
                        <span>At most</span>
                        <span>{{ formatUsd(estimate?.maximum_price ?? null) }}</span>
                    </div>
                </template>
                <div class="flex justify-between border-t pt-3 text-sm">
                    <span class="text-muted-foreground">Your balance</span>
                    <span>{{ formatCredits(page.props.auth.balance) }}</span>
                </div>
                <p v-if="estimate?.can_afford === false" class="rounded-md bg-amber-50 p-2 text-sm text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                    Not enough credits for the maximum price.
                    <Link :href="route('wallet.index')" class="underline">Buy credits</Link>
                </p>
                <p class="text-xs text-muted-foreground">
                    The maximum is held from your balance until the image is ready, then the unused part comes back.
                </p>
                <p class="text-xs text-muted-foreground">
                    The final price depends on how many tokens the model uses and is known once the image is ready. It is never more than the
                    maximum shown. Failed generations are free.
                </p>
            </aside>
        </div>
    </AppLayout>
</template>
