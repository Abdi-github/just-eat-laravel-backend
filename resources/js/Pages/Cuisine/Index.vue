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
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    cuisines: {
        data: Array<{
            id: number;
            name: Record<string, string>;
            slug: string;
            is_active: boolean;
            sort_order: number;
            restaurants_count: number;
        }>;
        meta: { current_page: number; last_page: number; total: number; per_page: number };
        links: { next: string | null; prev: string | null };
    };
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

function applyFilters() {
    router.get(route('admin.cuisines.index'), {
        search: search.value || undefined,
    }, { preserveScroll: true, preserveState: true });
}

function resetFilters() {
    search.value = '';
    applyFilters();
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

function toggleActive(cuisine: { id: number; is_active: boolean }) {
    router.put(route('admin.cuisines.update', cuisine.id), {
        is_active: !cuisine.is_active,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function deleteCuisine(id: number) {
    confirm.require({
        message: t('common.confirmDelete'),
        header: t('common.delete'),
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('admin.cuisines.destroy', id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}

function getCuisineName(name: Record<string, string>): string {
    return name?.en ?? name?.fr ?? name?.de ?? Object.values(name)[0] ?? '';
}
</script>

<template>
    <Head :title="t('cuisine.title')" />

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('cuisine.title') }}</h1>
            <Link :href="route('admin.cuisines.create')">
                <Button :label="t('cuisine.create')" icon="pi pi-plus" />
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500">{{ t('common.search') }}</label>
                <InputText v-model="search" :placeholder="t('common.search')" />
            </div>
            <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
            <Button :label="t('common.reset')" icon="pi pi-times" severity="secondary" @click="resetFilters" />
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <DataTable :value="cuisines.data" striped-rows>
                <Column :header="t('cuisine.name')">
                    <template #body="{ data }">
                        {{ getCuisineName(data.name) }}
                    </template>
                </Column>
                <Column field="slug" :header="t('cuisine.slug')" />
                <Column field="sort_order" :header="t('cuisine.sortOrder')" />
                <Column field="restaurants_count" :header="t('cuisine.restaurants')" />
                <Column :header="t('common.status')">
                    <template #body="{ data }">
                        <Tag
                            :value="data.is_active ? t('common.active') : t('common.inactive')"
                            :severity="data.is_active ? 'success' : 'danger'"
                            class="cursor-pointer"
                            @click="toggleActive(data)"
                        />
                    </template>
                </Column>
                <Column :header="t('common.actions')">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Link :href="route('admin.cuisines.show', data.id)">
                                <Button icon="pi pi-eye" text rounded severity="info" size="small" />
                            </Link>
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                severity="danger"
                                size="small"
                                @click="deleteCuisine(data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 flex justify-between">
                <span>{{ t('common.total') }}: {{ cuisines.meta?.total ?? cuisines.data.length }}</span>
                <div class="flex gap-2">
                    <Link v-if="cuisines.links?.prev" :href="cuisines.links.prev" preserve-scroll>
                        <Button icon="pi pi-chevron-left" text size="small" />
                    </Link>
                    <span>{{ t('common.page') }} {{ cuisines.meta?.current_page }} {{ t('common.of') }} {{ cuisines.meta?.last_page }}</span>
                    <Link v-if="cuisines.links?.next" :href="cuisines.links.next" preserve-scroll>
                        <Button icon="pi pi-chevron-right" text size="small" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
