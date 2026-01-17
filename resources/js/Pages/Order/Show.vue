<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import { format } from 'date-fns';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();

const props = defineProps<{
    order: {
        id: number;
        order_number: string;
        status: string;
        order_type: string;
        items: Array<{
            menu_item_id: number | string;
            name: string;
            unit_price: number;
            total_price: number;
            quantity: number;
        }>;
        subtotal: number;
        delivery_fee: number;
        tax: number;
        total: number;
        delivery_address: Record<string, string> | null;
        special_instructions: string | null;
        estimated_delivery_time: string | null;
        payment_method: string;
        payment_status: string;
        user: { id: number; first_name: string; last_name: string; email: string } | null;
        restaurant: { id: number; name: string } | null;
        review: { id: number; rating: number; comment: string | null } | null;
        created_at: string;
        updated_at: string;
    };
}>();

const statusOptions = [
    { label: 'Pending', value: 'pending' },
    { label: 'Confirmed', value: 'confirmed' },
    { label: 'Preparing', value: 'preparing' },
    { label: 'Picked Up', value: 'picked_up' },
    { label: 'Delivered', value: 'delivered' },
    { label: 'Cancelled', value: 'cancelled' },
];

const selectedStatus = ref(props.order.status);

function updateStatus() {
    router.put(route('admin.orders.update', props.order.id), {
        status: selectedStatus.value,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Status updated', life: 2000 }),
    });
}

function statusSeverity(status: string): string {
    const map: Record<string, string> = {
        pending: 'warn', confirmed: 'info', preparing: 'info',
        picked_up: 'info', delivered: 'success', cancelled: 'danger',
    };
    return map[status] ?? 'secondary';
}

function paymentSeverity(status: string): string {
    return status === 'paid' ? 'success' : status === 'failed' ? 'danger' : 'warn';
}
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link :href="route('admin.orders.index')" class="text-gray-500 hover:text-gray-700">
                    <i class="pi pi-arrow-left text-sm" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ order.order_number }}</h1>
                    <p class="text-sm text-gray-500">{{ format(new Date(order.created_at), 'dd MMM yyyy HH:mm') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Select v-model="selectedStatus" :options="statusOptions" option-label="label" option-value="value" class="w-40" />
                <Button label="Update Status" icon="pi pi-check" size="small" @click="updateStatus" />
            </div>
        </div>

        <!-- Status badges -->
        <div class="flex gap-2">
            <Tag :severity="statusSeverity(order.status)" :value="order.status" />
            <Tag :value="order.order_type" severity="info" />
            <Tag :severity="paymentSeverity(order.payment_status)" :value="order.payment_status" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Order Summary -->
            <Card>
                <template #title>Order Summary</template>
                <template #content>
                    <div class="space-y-2">
                        <div v-for="item in order.items" :key="item.menu_item_id"
                             class="flex justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-900 dark:text-white">
                                {{ item.quantity }}× {{ item.name }}
                            </span>
                            <span class="font-medium">CHF {{ (item.total_price ?? item.unit_price * item.quantity).toFixed(2) }}</span>
                        </div>
                        <div class="pt-3 space-y-1 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span><span>CHF {{ order.subtotal }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Delivery fee</span><span>CHF {{ order.delivery_fee }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span><span>CHF {{ order.tax }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 dark:text-white border-t pt-2">
                                <span>Total</span><span>CHF {{ order.total }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="order.special_instructions" class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-medium">Note:</span> {{ order.special_instructions }}
                    </div>
                </template>
            </Card>

            <!-- Details -->
            <div class="space-y-4">
                <Card>
                    <template #title>Customer</template>
                    <template #content>
                        <div v-if="order.user" class="text-sm space-y-1">
                            <Link :href="route('admin.users.show', order.user.id)" class="text-blue-600 hover:underline font-medium">
                                {{ order.user.first_name }} {{ order.user.last_name }}
                            </Link>
                            <p class="text-gray-500">{{ order.user.email }}</p>
                        </div>
                        <p v-else class="text-gray-500 text-sm">Guest order</p>
                    </template>
                </Card>

                <Card>
                    <template #title>Restaurant</template>
                    <template #content>
                        <div v-if="order.restaurant" class="text-sm">
                            <Link :href="route('admin.restaurants.show', order.restaurant.id)" class="text-blue-600 hover:underline font-medium">
                                {{ order.restaurant.name }}
                            </Link>
                        </div>
                    </template>
                </Card>

                <Card v-if="order.delivery_address">
                    <template #title>Delivery Address</template>
                    <template #content>
                        <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ JSON.stringify(order.delivery_address, null, 2) }}</pre>
                    </template>
                </Card>

                <Card v-if="order.review">
                    <template #title>Customer Review</template>
                    <template #content>
                        <div class="flex items-center gap-1 mb-2">
                            <i v-for="i in 5" :key="i"
                               :class="['pi pi-star-fill text-sm', i <= order.review.rating ? 'text-yellow-400' : 'text-gray-300']" />
                        </div>
                        <p v-if="order.review.comment" class="text-sm text-gray-700 dark:text-gray-300">{{ order.review.comment }}</p>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>
