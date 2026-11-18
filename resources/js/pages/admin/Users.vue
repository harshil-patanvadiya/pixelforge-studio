<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatUsd } from '@/lib/money';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface UserLine {
    id: number;
    name: string;
    email: string | null;
    balance: string | null;
    generations: number;
    succeeded: number;
    failed: number;
    provider_cost: string;
    charges: string;
    profit: string;
}

const props = defineProps<{
    filters: { start_date: string; end_date: string };
    users: UserLine[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Users', href: '/admin/users' }];

const form = useForm({ ...props.filters });

function applyFilters(): void {
    form.get(route('admin.users.index'), { preserveScroll: true });
}

const cellClass = 'px-3 py-2 text-right tabular-nums';
</script>

<template>
    <Head title="Users" />

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

            <section class="space-y-2">
                <h2 class="font-medium">Per user</h2>
                <div class="overflow-x-auto rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th class="px-3 py-2 text-left font-normal">User</th>
                                <th :class="cellClass" class="font-normal">Generations</th>
                                <th :class="cellClass" class="font-normal">Charged</th>
                                <th :class="cellClass" class="font-normal">Provider cost</th>
                                <th :class="cellClass" class="font-normal">Profit</th>
                                <th :class="cellClass" class="font-normal">Credits</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="users.length === 0" class="border-t">
                                <td colspan="6" class="px-3 py-4 text-center text-muted-foreground">No users yet.</td>
                            </tr>
                            <tr v-for="user in users" :key="user.id" class="border-t">
                                <td class="px-3 py-2">
                                    <Link :href="route('admin.users.show', user.id)" class="font-medium hover:underline">{{ user.name }}</Link>
                                    <span v-if="user.email" class="block text-xs text-muted-foreground">{{ user.email }}</span>
                                </td>
                                <td :class="cellClass">{{ user.succeeded }} / {{ user.generations }}</td>
                                <td :class="cellClass">{{ formatUsd(user.charges) }}</td>
                                <td :class="cellClass">{{ formatUsd(user.provider_cost) }}</td>
                                <td :class="cellClass">{{ formatUsd(user.profit) }}</td>
                                <td :class="cellClass">{{ formatUsd(user.balance) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-muted-foreground">Generations column shows succeeded / total.</p>
            </section>
        </div>
    </AppLayout>
</template>
