<script setup lang="ts">
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from 'vue-i18n';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { format } from 'date-fns';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
    stats: {
        totalRestaurants: number;
        totalUsers: number;
        totalOrders: number;
        totalRevenue: number;
    };
    recentOrders: Array<{
        id: number;
        order_number: string;
        status: string;
        total: number;
        created_at: string;
        customer_name: string;
        restaurant_name: string;
    }>;
    ordersByStatus: Record<string, number>;
    monthlyRevenue: Array<{ month: string; revenue: number; orders: number }>;
}>();

const statCards = computed(() => [
    {
        label: t('dashboard.totalRestaurants'),
        value: props.stats.totalRestaurants,
        icon: 'pi pi-building',
        color: 'bg-blue-500',
    },
    {
        label: t('dashboard.totalUsers'),
        value: props.stats.totalUsers,
        icon: 'pi pi-users',
        color: 'bg-green-500',
    },
    {
        label: t('dashboard.totalOrders'),
        value: props.stats.totalOrders,
        icon: 'pi pi-shopping-cart',
        color: 'bg-purple-500',
    },
    {
        label: t('dashboard.totalRevenue'),
        value: `CHF ${props.stats.totalRevenue.toFixed(2)}`,
        icon: 'pi pi-dollar',
        color: 'bg-orange-500',
    },
]);

const revenueChartData = computed(() => ({
    labels: props.monthlyRevenue.map((m) => m.month),
    datasets: [
        {
            label: 'Revenue (CHF)',
            data: props.monthlyRevenue.map((m) => m.revenue),
            backgroundColor: 'rgba(249, 115, 22, 0.7)',
            borderColor: 'rgb(249, 115, 22)',
            borderWidth: 1,
        },
    ],
}));

const revenueChartOptions = {
    responsive: true,
    plugins: { legend: { display: false } },
};

const orderStatusSeverity: Record<string, string> = {
    pending: 'warn',
    confirmed: 'info',
    preparing: 'info',
    picked_up: 'secondary',
    delivered: 'success',
    cancelled: 'danger',
};
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ t('dashboard.title') }}
        </h1>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <Card v-for="card in statCards" :key="card.label" class="shadow-sm">
                <template #content>
                    <div class="flex items-center gap-4">
                        <div :class="['w-12 h-12 rounded-full flex items-center justify-center', card.color]">
                            <i :class="[card.icon, 'text-white text-xl']" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ card.label }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ card.value }}</p>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Monthly Revenue Chart -->
            <Card class="shadow-sm">
                <template #title>{{ t('dashboard.monthlyRevenue') }}</template>
                <template #content>
                    <Bar :data="revenueChartData" :options="revenueChartOptions" />
                </template>
            </Card>

            <!-- Orders by Status -->
            <Card class="shadow-sm">
                <template #title>{{ t('dashboard.ordersByStatus') }}</template>
                <template #content>
                    <div class="space-y-3">
                        <div
                            v-for="(count, status) in props.ordersByStatus"
                            :key="status"
                            class="flex items-center justify-between"
                        >
                            <Tag
                                :value="t(`order.statuses.${status}`)"
                                :severity="orderStatusSeverity[status as string] ?? 'secondary'"
                            />
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ count }}</span>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Recent Orders -->
        <Card class="shadow-sm">
            <template #title>{{ t('dashboard.recentOrders') }}</template>
            <template #content>
                <DataTable :value="recentOrders" striped-rows>
                    <Column field="order_number" :header="t('order.orderNumber')" />
                    <Column field="customer_name" :header="t('order.customer')" />
                    <Column field="restaurant_name" :header="t('order.restaurant')" />
                    <Column :header="t('order.status')">
                        <template #body="{ data }">
                            <Tag
                                :value="t(`order.statuses.${data.status}`)"
                                :severity="orderStatusSeverity[data.status] ?? 'secondary'"
                            />
                        </template>
                    </Column>
                    <Column :header="t('order.total')">
                        <template #body="{ data }">
                            CHF {{ Number(data.total).toFixed(2) }}
                        </template>
                    </Column>
                    <Column :header="t('common.createdAt')">
                        <template #body="{ data }">
                            {{ format(new Date(data.created_at), 'dd.MM.yyyy HH:mm') }}
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </div>
</template>
