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
import { format } from 'date-fns';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    reviews: {
        data: Array<{
            id: number;
            rating: number;
            title: string | null;
            comment: string | null;
            is_visible: boolean;
            is_verified: boolean;
            created_at: string;
            user: { first_name: string; last_name: string } | null;
            restaurant: { name: string } | null;
        }>;
        meta: { current_page: number; last_page: number; total: number; per_page: number };
        links: { next: string | null; prev: string | null };
    };
    filters: { search?: string; visible?: string };
}>();

const search = ref(props.filters.search ?? '');
const visibleFilter = ref(props.filters.visible ?? '');

const visibleOptions = [
    { label: t('common.all'), value: '' },
    { label: t('review.visible'), value: 'true' },
    { label: 'Hidden', value: 'false' },
];

function applyFilters() {
    router.get(route('admin.reviews.index'), {
        search: search.value || undefined,
        visible: visibleFilter.value || undefined,
    }, { preserveScroll: true, preserveState: true });
}

function resetFilters() {
    search.value = '';
    visibleFilter.value = '';
    applyFilters();
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});
watch(visibleFilter, () => applyFilters());

function toggleVisibility(review: { id: number; is_visible: boolean }) {
    router.put(route('admin.reviews.update', review.id), {
        is_visible: !review.is_visible,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function deleteReview(id: number) {
    confirm.require({
        message: t('common.confirmDelete'),
        header: t('common.delete'),
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('admin.reviews.destroy', id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}

function renderStars(rating: number): string {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
}
</script>

<template>
    <Head :title="t('review.title')" />

    <div class="space-y-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('review.title') }}</h1>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500">{{ t('common.search') }}</label>
                <InputText v-model="search" :placeholder="t('common.search')" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500">{{ t('review.visible') }}</label>
                <Select v-model="visibleFilter" :options="visibleOptions" option-label="label" option-value="value" />
            </div>
            <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
            <Button :label="t('common.reset')" icon="pi pi-times" severity="secondary" @click="resetFilters" />
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <DataTable :value="reviews.data" striped-rows sort-mode="single">
                <Column :header="t('review.customer')" sort-field="user.first_name" sortable>
                    <template #body="{ data }">
                        {{ data.user ? `${data.user.first_name} ${data.user.last_name}` : '-' }}
                    </template>
                </Column>
                <Column :header="t('review.restaurant')">
                    <template #body="{ data }">
                        {{ data.restaurant?.name ?? '-' }}
                    </template>
                </Column>
                <Column :header="t('review.rating')" sort-field="rating" sortable>
                    <template #body="{ data }">
                        <span class="text-yellow-500">{{ renderStars(data.rating) }}</span>
                    </template>
                </Column>
                <Column :header="t('review.comment')">
                    <template #body="{ data }">
                        <span class="text-sm line-clamp-2">{{ data.comment ?? '-' }}</span>
                    </template>
                </Column>
                <Column :header="t('review.visible')">
                    <template #body="{ data }">
                        <Tag
                            :value="data.is_visible ? t('review.visible') : 'Hidden'"
                            :severity="data.is_visible ? 'success' : 'danger'"
                            class="cursor-pointer"
                            @click="toggleVisibility(data)"
                        />
                    </template>
                </Column>
                <Column :header="t('common.createdAt')">
                    <template #body="{ data }">
                        {{ format(new Date(data.created_at), 'dd.MM.yyyy') }}
                    </template>
                </Column>
                <Column :header="t('common.actions')">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Link :href="route('admin.reviews.show', data.id)">
                                <Button icon="pi pi-eye" text rounded severity="info" size="small" />
                            </Link>
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                severity="danger"
                                size="small"
                                @click="deleteReview(data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 flex justify-between">
                <span>{{ t('common.total') }}: {{ reviews.meta?.total ?? reviews.data.length }}</span>
                <div class="flex gap-2">
                    <Link v-if="reviews.links?.prev" :href="reviews.links.prev" preserve-scroll>
                        <Button icon="pi pi-chevron-left" text size="small" />
                    </Link>
                    <span>{{ t('common.page') }} {{ reviews.meta?.current_page }} {{ t('common.of') }} {{ reviews.meta?.last_page }}</span>
                    <Link v-if="reviews.links?.next" :href="reviews.links.next" preserve-scroll>
                        <Button icon="pi pi-chevron-right" text size="small" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
