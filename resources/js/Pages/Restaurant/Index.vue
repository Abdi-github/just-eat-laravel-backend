<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    restaurants: {
        data: Array<{
            id: number;
            name: string;
            is_active: boolean;
            is_featured: boolean;
            average_rating: number;
            total_reviews: number;
            orders_count: number;
            reviews_count: number;
            brand: { name: string } | null;
            address: { city: { name: string } } | null;
        }>;
        meta: { current_page: number; last_page: number; total: number; per_page: number };
        links: { next: string | null; prev: string | null };
    };
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');

const statusOptions = [
    { label: t('common.all'), value: '' },
    { label: t('common.active'), value: 'active' },
    { label: t('common.inactive'), value: 'inactive' },
];

function applyFilters() {
    router.get(route('admin.restaurants.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveScroll: true, preserveState: true });
}

function resetFilters() {
    search.value = '';
    statusFilter.value = '';
    applyFilters();
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});
watch(statusFilter, () => applyFilters());

function toggleActive(restaurant: { id: number; is_active: boolean }) {
    router.put(route('admin.restaurants.update', restaurant.id), {
        is_active: !restaurant.is_active,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function toggleFeatured(restaurant: { id: number; is_featured: boolean }) {
    router.put(route('admin.restaurants.update', restaurant.id), {
        is_featured: !restaurant.is_featured,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function deleteRestaurant(id: number) {
    confirm.require({
        message: t('common.confirmDelete'),
        header: t('common.delete'),
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined',
        accept: () => {
            router.delete(route('admin.restaurants.destroy', id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}
</script>

<template>
    <Head :title="t('restaurant.title')" />

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('restaurant.title') }}</h1>
            <Link :href="route('admin.restaurants.create')">
                <Button :label="t('restaurant.create')" icon="pi pi-plus" />
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500">{{ t('common.search') }}</label>
                <InputText v-model="search" :placeholder="t('common.search')" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500">{{ t('common.status') }}</label>
                <Select v-model="statusFilter" :options="statusOptions" option-label="label" option-value="value" />
            </div>
            <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
            <Button :label="t('common.reset')" icon="pi pi-times" severity="secondary" @click="resetFilters" />
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <DataTable :value="restaurants.data" striped-rows sort-mode="single">
                <Column field="name" :header="t('restaurant.name')" sortable />
                <Column :header="t('restaurant.status')">
                    <template #body="{ data }">
                        <Tag
                            :value="data.is_active ? t('common.active') : t('common.inactive')"
                            :severity="data.is_active ? 'success' : 'danger'"
                            class="cursor-pointer"
                            @click="toggleActive(data)"
                        />
                    </template>
                </Column>
                <Column :header="t('restaurant.featured')">
                    <template #body="{ data }">
                        <i
                            :class="data.is_featured ? 'pi pi-star-fill text-yellow-400' : 'pi pi-star text-gray-300'"
                            class="text-xl cursor-pointer"
                            @click="toggleFeatured(data)"
                        />
                    </template>
                </Column>
                <Column :header="t('restaurant.rating')">
                    <template #body="{ data }">
                        <span>★ {{ Number(data.average_rating).toFixed(1) }}</span>
                        <span class="text-xs text-gray-400 ml-1">({{ data.reviews_count }})</span>
                    </template>
                </Column>
                <Column field="orders_count" :header="t('order.title')" />
                <Column :header="t('common.actions')">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Link :href="route('admin.restaurants.show', data.id)">
                                <Button icon="pi pi-eye" text rounded severity="info" size="small" />
                            </Link>
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                severity="danger"
                                size="small"
                                @click="deleteRestaurant(data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination info -->
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 flex justify-between">
                <span>{{ t('common.total') }}: {{ restaurants.meta?.total ?? restaurants.data.length }}</span>
                <div class="flex gap-2">
                    <Link
                        v-if="restaurants.links?.prev"
                        :href="restaurants.links.prev"
                        preserve-scroll
                    >
                        <Button icon="pi pi-chevron-left" text size="small" />
                    </Link>
                    <span>{{ t('common.page') }} {{ restaurants.meta?.current_page }} {{ t('common.of') }} {{ restaurants.meta?.last_page }}</span>
                    <Link
                        v-if="restaurants.links?.next"
                        :href="restaurants.links.next"
                        preserve-scroll
                    >
                        <Button icon="pi pi-chevron-right" text size="small" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
