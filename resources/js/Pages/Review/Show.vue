<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { format } from 'date-fns';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    review: {
        id: number;
        rating: number;
        title: string | null;
        comment: string | null;
        is_verified: boolean;
        is_visible: boolean;
        created_at: string;
        user: { id: number; first_name: string; last_name: string; email: string } | null;
        restaurant: { id: number; name: string } | null;
        order: { id: number; order_number: string } | null;
    };
}>();

function toggleVisible() {
    router.put(route('admin.reviews.update', props.review.id), {
        is_visible: !props.review.is_visible,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function confirmDelete() {
    confirm.require({
        message: 'Delete this review permanently?',
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.reviews.destroy', props.review.id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}
</script>

<template>
    <Head title="Review Detail" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link :href="route('admin.reviews.index')" class="text-gray-500 hover:text-gray-700">
                    <i class="pi pi-arrow-left text-sm" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Review #{{ review.id }}</h1>
                    <p class="text-sm text-gray-500">{{ format(new Date(review.created_at), 'dd MMM yyyy HH:mm') }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button
                    :label="review.is_visible ? 'Hide' : 'Show'"
                    :icon="review.is_visible ? 'pi pi-eye-slash' : 'pi pi-eye'"
                    size="small"
                    :severity="review.is_visible ? 'warning' : 'success'"
                    @click="toggleVisible"
                />
                <Button label="Delete" icon="pi pi-trash" size="small" severity="danger" outlined @click="confirmDelete" />
            </div>
        </div>

        <!-- Badges -->
        <div class="flex gap-2">
            <Tag :severity="review.is_visible ? 'success' : 'secondary'" :value="review.is_visible ? 'Visible' : 'Hidden'" />
            <Tag v-if="review.is_verified" severity="info" value="Verified purchase" />
        </div>

        <!-- Rating + Content -->
        <Card>
            <template #title>
                <div class="flex items-center gap-2">
                    <span>Rating</span>
                    <div class="flex gap-0.5">
                        <i v-for="i in 5" :key="i"
                           :class="['pi pi-star-fill', i <= review.rating ? 'text-yellow-400' : 'text-gray-300']" />
                    </div>
                    <span class="text-lg font-bold">{{ review.rating }}/5</span>
                </div>
            </template>
            <template #content>
                <div v-if="review.title" class="font-semibold text-gray-900 dark:text-white mb-2">{{ review.title }}</div>
                <p v-if="review.comment" class="text-gray-700 dark:text-gray-300">{{ review.comment }}</p>
                <p v-else class="text-gray-400 italic">No comment provided</p>
            </template>
        </Card>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- User -->
            <Card>
                <template #title>Customer</template>
                <template #content>
                    <div v-if="review.user" class="text-sm">
                        <Link :href="route('admin.users.show', review.user.id)" class="text-blue-600 hover:underline font-medium block">
                            {{ review.user.first_name }} {{ review.user.last_name }}
                        </Link>
                        <span class="text-gray-500">{{ review.user.email }}</span>
                    </div>
                    <p v-else class="text-gray-500 text-sm">Deleted user</p>
                </template>
            </Card>

            <!-- Restaurant -->
            <Card>
                <template #title>Restaurant</template>
                <template #content>
                    <div v-if="review.restaurant" class="text-sm">
                        <Link :href="route('admin.restaurants.show', review.restaurant.id)" class="text-blue-600 hover:underline font-medium">
                            {{ review.restaurant.name }}
                        </Link>
                    </div>
                    <p v-else class="text-gray-500 text-sm">Deleted restaurant</p>
                </template>
            </Card>

            <!-- Order -->
            <Card>
                <template #title>Order</template>
                <template #content>
                    <div v-if="review.order" class="text-sm">
                        <Link :href="route('admin.orders.show', review.order.id)" class="text-blue-600 hover:underline font-mono text-xs">
                            {{ review.order.order_number }}
                        </Link>
                    </div>
                    <p v-else class="text-gray-500 text-sm">No linked order</p>
                </template>
            </Card>
        </div>
    </div>
</template>
