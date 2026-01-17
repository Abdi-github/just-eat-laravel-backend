<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import Select from 'primevue/select';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement,
    BarElement, Title, Tooltip, Legend, ArcElement, Filler } from 'chart.js';
import { Line, Bar, Doughnut } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement,
    BarElement, Title, Tooltip, Legend, ArcElement, Filler);

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
    stats: {
        totalRevenue: number;
        totalOrders: number;
        avgOrderValue: number;
        newUsers: number;
    };
    revenueTimeSeries: Array<{ date: string; revenue: number; orders: number }>;
    ordersByStatus: Record<string, number>;
    topRestaurants: Array<{ id: number; name: string; total_orders: number; total_revenue: number }>;
    filters: { preset?: string; period?: string };
}>();

const PRESETS = [
    { label: 'Today',         value: 'today' },
    { label: 'Last 7 days',   value: 'last_7_days' },
    { label: 'Last 30 days',  value: 'last_30_days' },
    { label: 'Last 90 days',  value: 'last_90_days' },
    { label: 'This year',     value: 'this_year' },
];

const PERIODS = [
    { label: 'Daily',   value: 'daily' },
    { label: 'Monthly', value: 'monthly' },
];

const selectedPreset = ref(props.filters.preset ?? 'last_30_days');
const selectedPeriod = ref(props.filters.period ?? 'daily');

function applyFilters() {
    router.get(route('admin.analytics.index'), {
        preset: selectedPreset.value,
        period: selectedPeriod.value,
    }, { preserveState: true, replace: true });
}

// Charts
const revenueChartData = computed(() => ({
    labels: props.revenueTimeSeries.map(r => r.date),
    datasets: [
        {
            label: 'Revenue (CHF)',
            data: props.revenueTimeSeries.map(r => r.revenue),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true,
            tension: 0.3,
        },
    ],
}));

const ordersChartData = computed(() => ({
    labels: props.revenueTimeSeries.map(r => r.date),
    datasets: [
        {
            label: 'Orders',
            data: props.revenueTimeSeries.map(r => r.orders),
            backgroundColor: '#10b981',
        },
    ],
}));

const statusChartData = computed(() => ({
    labels: Object.keys(props.ordersByStatus),
    datasets: [
        {
            data: Object.values(props.ordersByStatus),
            backgroundColor: ['#f59e0b','#3b82f6','#8b5cf6','#10b981','#ef4444','#6b7280'],
        },
    ],
}));

const chartOptions = { responsive: true, plugins: { legend: { position: 'top' as const } } };
</script>

<template>
    <Head :title="t('analytics.title')" />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('analytics.title') }}</h1>
            <div class="flex gap-3">
                <Select v-model="selectedPreset" :options="PRESETS" option-label="label" option-value="value"
                    :placeholder="'Period'" />
                <Select v-model="selectedPeriod" :options="PERIODS" option-label="label" option-value="value"
                    :placeholder="'Granularity'" />
                <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                <p class="text-sm text-gray-500">{{ t('analytics.totalRevenue') }}</p>
                <p class="text-2xl font-bold text-blue-600">CHF {{ stats.totalRevenue.toFixed(2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                <p class="text-sm text-gray-500">{{ t('analytics.totalOrders') }}</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.totalOrders }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                <p class="text-sm text-gray-500">{{ t('analytics.avgOrder') }}</p>
                <p class="text-2xl font-bold text-purple-600">CHF {{ stats.avgOrderValue.toFixed(2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                <p class="text-sm text-gray-500">{{ t('analytics.newUsers') }}</p>
                <p class="text-2xl font-bold text-orange-600">{{ stats.newUsers }}</p>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-4">{{ t('analytics.revenueChart') }}</h2>
                <Line :data="revenueChartData" :options="chartOptions" />
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-4">{{ t('analytics.ordersChart') }}</h2>
                <Bar :data="ordersChartData" :options="chartOptions" />
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-4">{{ t('analytics.ordersByStatus') }}</h2>
                <Doughnut :data="statusChartData" :options="chartOptions" />
            </div>

            <!-- Top Restaurants -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-4">{{ t('analytics.topRestaurants') }}</h2>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b dark:border-gray-700">
                            <th class="pb-2">#</th>
                            <th class="pb-2">{{ t('delivery.restaurant') }}</th>
                            <th class="pb-2 text-right">{{ t('analytics.totalOrders') }}</th>
                            <th class="pb-2 text-right">{{ t('analytics.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(r, i) in topRestaurants" :key="r.id"
                            class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="py-2 text-gray-400">{{ i + 1 }}</td>
                            <td class="py-2 font-medium">{{ r.name }}</td>
                            <td class="py-2 text-right">{{ r.total_orders }}</td>
                            <td class="py-2 text-right">CHF {{ Number(r.total_revenue).toFixed(2) }}</td>
                        </tr>
                        <tr v-if="!topRestaurants.length">
                            <td colspan="4" class="py-4 text-center text-gray-400">{{ t('common.noData') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
