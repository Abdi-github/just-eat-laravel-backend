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
    users: {
        data: Array<{
            id: number;
            first_name: string;
            last_name: string;
            email: string;
            username: string;
            is_active: boolean;
            preferred_language: string;
            orders_count: number;
            reviews_count: number;
            favorites_count: number;
            created_at: string;
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
    router.get(route('admin.users.index'), {
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

function toggleActive(user: { id: number; is_active: boolean }) {
    router.put(route('admin.users.update', user.id), {
        is_active: !user.is_active,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function deleteUser(id: number) {
    confirm.require({
        message: t('common.confirmDelete'),
        header: t('common.delete'),
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('admin.users.destroy', id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}
</script>

<template>
    <Head :title="t('user.title')" />

    <div class="space-y-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('user.title') }}</h1>

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
            <DataTable :value="users.data" striped-rows sort-mode="single">
                <Column :header="t('user.firstName') + ' / ' + t('user.lastName')" sort-field="first_name" sortable>
                    <template #body="{ data }">
                        {{ data.first_name }} {{ data.last_name }}
                    </template>
                </Column>
                <Column field="email" :header="t('user.email')" sortable />
                <Column field="username" :header="t('user.username')" sortable />
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
                <Column field="orders_count" :header="t('user.totalOrders')" sortable />
                <Column :header="t('common.createdAt')">
                    <template #body="{ data }">
                        {{ format(new Date(data.created_at), 'dd.MM.yyyy') }}
                    </template>
                </Column>
                <Column :header="t('common.actions')">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Link :href="route('admin.users.show', data.id)">
                                <Button icon="pi pi-eye" text rounded severity="info" size="small" />
                            </Link>
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                severity="danger"
                                size="small"
                                @click="deleteUser(data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 flex justify-between">
                <span>{{ t('common.total') }}: {{ users.meta?.total ?? users.data.length }}</span>
                <div class="flex gap-2">
                    <Link v-if="users.links?.prev" :href="users.links.prev" preserve-scroll>
                        <Button icon="pi pi-chevron-left" text size="small" />
                    </Link>
                    <span>{{ t('common.page') }} {{ users.meta?.current_page }} {{ t('common.of') }} {{ users.meta?.last_page }}</span>
                    <Link v-if="users.links?.next" :href="users.links.next" preserve-scroll>
                        <Button icon="pi pi-chevron-right" text size="small" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
