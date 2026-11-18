<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCredits } from '@/lib/money';
import { type BreadcrumbItem, type SharedData } from '@/types';
import type { PaginationLink } from '@/types/generation';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

interface WalletTransaction {
    id: number;
    type: string;
    display_type: string;
    amount: string;
    generation_id: number | null;
    created_at: string;
}

defineProps<{
    transactions: { data: WalletTransaction[]; meta: { last_page: number; links: PaginationLink[] } };
    packages: string[];
}>();

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Wallet', href: '/wallet' }];

const purchasingAmount = ref<string | null>(null);

function buy(amount: string): void {
    purchasingAmount.value = amount;

    router.post(
        route('wallet.purchases.store'),
        { amount },
        { preserveScroll: true, onFinish: () => (purchasingAmount.value = null) },
    );
}
</script>

<template>
    <Head title="Wallet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-3xl space-y-6 p-4">
            <div class="space-y-4 rounded-xl border p-4">
                <div>
                    <p class="text-sm text-muted-foreground">Available credits</p>
                    <p class="text-3xl font-semibold">{{ formatCredits(page.props.auth.balance) }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button v-for="amount in packages" :key="amount" :disabled="purchasingAmount !== null" @click="buy(amount)">
                        Buy {{ formatCredits(amount) }}
                    </Button>
                </div>
                <p class="text-xs text-muted-foreground">Demo purchase, no payment is taken.</p>
            </div>

            <section class="space-y-2">
                <h2 class="font-medium">Transactions</h2>
                <div class="overflow-x-auto rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th class="px-3 py-2 text-left font-normal">Date</th>
                                <th class="px-3 py-2 text-left font-normal">Description</th>
                                <th class="px-3 py-2 text-right font-normal">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="transactions.data.length === 0" class="border-t">
                                <td colspan="3" class="px-3 py-4 text-center text-muted-foreground">No transactions yet.</td>
                            </tr>
                            <tr v-for="transaction in transactions.data" :key="transaction.id" class="border-t">
                                <td class="px-3 py-2">{{ new Date(transaction.created_at).toLocaleString() }}</td>
                                <td class="px-3 py-2">
                                    {{ transaction.display_type }}
                                    <Link v-if="transaction.generation_id" :href="route('generations.show', transaction.generation_id)" class="underline">
                                        #{{ transaction.generation_id }}
                                    </Link>
                                </td>
                                <td :class="['px-3 py-2 text-right tabular-nums', transaction.amount.startsWith('-') ? '' : 'text-green-700 dark:text-green-400']">
                                    {{ formatCredits(transaction.amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav v-if="transactions.meta.last_page > 1" class="flex flex-wrap gap-1">
                    <template v-for="link in transactions.meta.links" :key="link.label">
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
            </section>
        </div>
    </AppLayout>
</template>
