<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { format } from 'date-fns';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    user: {
        id: number;
        username: string;
        email: string;
        first_name: string | null;
        last_name: string | null;
        phone: string | null;
        avatar: string | null;
        is_active: boolean;
        preferred_language: string;
        email_verified_at: string | null;
        orders_count: number;
        reviews_count: number;
        favorites_count: number;
        orders: Array<{
            id: number;
            order_number: string;
            status: string;
            total: number;
            created_at: string;
            restaurant: { name: string } | null;
        }>;
        reviews: Array<{
            id: number;
            rating: number;
            comment: string | null;
            is_visible: boolean;
            created_at: string;
            restaurant: { name: string } | null;
        }>;
        created_at: string;
    };
}>();

function fullName(): string {
    return [props.user.first_name, props.user.last_name].filter(Boolean).join(' ') || props.user.username;
}

function toggleActive() {
    router.put(route('admin.users.update', props.user.id), {
        is_active: !props.user.is_active,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function confirmDelete() {
    confirm.require({
        message: `Delete user "${props.user.email}"?`,
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.users.destroy', props.user.id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}

function statusSeverity(status: string): string {
    const map: Record<string, string> = {
        pending: 'warn', confirmed: 'info', preparing: 'info',
        picked_up: 'info', delivered: 'success', cancelled: 'danger',
    };
    return map[status] ?? 'secondary';
}
</script>

<template>
    <Head :title="fullName()" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link :href="route('admin.users.index')" class="text-gray-500 hover:text-gray-700">
                    <i class="pi pi-arrow-left text-sm" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ fullName() }}</h1>
                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button
                    :label="user.is_active ? 'Deactivate' : 'Activate'"
                    size="small"
                    :severity="user.is_active ? 'danger' : 'success'"
                    @click="toggleActive"
                />
                <Button label="Delete" icon="pi pi-trash" size="small" severity="danger" outlined @click="confirmDelete" />
            </div>
        </div>

        <!-- Status -->
        <div class="flex gap-2">
            <Tag :severity="user.is_active ? 'success' : 'danger'" :value="user.is_active ? 'Active' : 'Inactive'" />
            <Tag v-if="user.email_verified_at" severity="success" value="Verified" />
            <Tag :value="user.preferred_language.toUpperCase()" severity="info" />
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card>
                <template #content>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ user.orders_count }}</p>
                        <p class="text-sm text-gray-500">Orders</p>
                    </div>
                </template>
            </Card>
            <Card>
                <template #content>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ user.reviews_count }}</p>
                        <p class="text-sm text-gray-500">Reviews</p>
                    </div>
                </template>
            </Card>
            <Card>
                <template #content>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ user.favorites_count }}</p>
                        <p class="text-sm text-gray-500">Favorites</p>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Profile info -->
        <Card>
            <template #title>Profile</template>
            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">Username:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ user.username }}</span>
                    </div>
                    <div v-if="user.phone">
                        <span class="font-medium text-gray-600">Phone:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ user.phone }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Member since:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ format(new Date(user.created_at), 'dd MMM yyyy') }}</span>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Recent orders -->
        <Card>
            <template #title>Recent Orders</template>
            <template #content>
                <DataTable :value="user.orders.slice(0, 20)" striped-rows>
                    <Column field="order_number" header="Order #">
                        <template #body="{ data }">
                            <Link :href="route('admin.orders.show', data.id)" class="text-blue-600 hover:underline font-mono text-xs">
                                {{ data.order_number }}
                            </Link>
                        </template>
                    </Column>
                    <Column header="Restaurant">
                        <template #body="{ data }">{{ data.restaurant?.name ?? '—' }}</template>
                    </Column>
                    <Column field="status" header="Status">
                        <template #body="{ data }">
                            <Tag :severity="statusSeverity(data.status)" :value="data.status" />
                        </template>
                    </Column>
                    <Column field="total" header="Total">
                        <template #body="{ data }">CHF {{ data.total }}</template>
                    </Column>
                    <Column field="created_at" header="Date">
                        <template #body="{ data }">{{ format(new Date(data.created_at), 'dd MMM yyyy') }}</template>
                    </Column>
                </DataTable>
                <p v-if="!user.orders.length" class="text-gray-500 text-sm mt-2">No orders yet.</p>
            </template>
        </Card>
    </div>
</template>
