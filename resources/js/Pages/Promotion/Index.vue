<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
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
const toast  = useToast();
const confirm = useConfirm();

interface Promotion {
    id: number;
    code: string;
    title: string;
    type: 'percentage' | 'fixed';
    value: number;
    minimum_order: number | null;
    usage_count: number;
    usage_limit: number | null;
    is_active: boolean;
    starts_at: string | null;
    expires_at: string | null;
    restaurant: { id: number; name: string } | null;
}

const props = defineProps<{
    promotions: {
        data: Promotion[];
        meta: { current_page: number; last_page: number; total: number };
    };
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

function applyFilters() {
    router.get(route('admin.promotions.index'), { search: search.value || undefined }, {
        preserveScroll: true,
        preserveState: true,
    });
}

function clearFilters() {
    search.value = '';
    applyFilters();
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

function toggleActive(promo: Promotion) {
    router.patch(route('admin.promotions.update', promo.id), { is_active: !promo.is_active }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: t('common.updated'), life: 2000 }),
    });
}

function deletePromotion(id: number) {
    confirm.require({
        message: t('common.confirmDelete'),
        header:  t('common.delete'),
        icon:    'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('admin.promotions.destroy', id), {
                onSuccess: () => toast.add({ severity: 'success', summary: t('common.deleted'), life: 2000 }),
                onError:   (e) => toast.add({ severity: 'error', summary: Object.values(e)[0] as string, life: 4000 }),
            });
        },
    });
}

function statusSeverity(promo: Promotion): 'success' | 'secondary' | 'danger' {
    if (!promo.is_active) return 'secondary';
    if (promo.expires_at && new Date(promo.expires_at) < new Date()) return 'danger';
    return 'success';
}

function statusLabel(promo: Promotion): string {
    if (!promo.is_active) return t('promotion.inactive');
    if (promo.expires_at && new Date(promo.expires_at) < new Date()) return t('promotion.expired');
    return t('promotion.active');
}
</script>

<template>
    <Head :title="t('promotion.title')" />

    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('promotion.title') }}</h1>
            <Button
                :label="t('promotion.create')"
                icon="pi pi-plus"
                @click="router.visit(route('admin.promotions.create'))"
            />
        </div>

        <!-- Filters -->
        <div class="flex gap-3 mb-4">
            <InputText
                v-model="search"
                :placeholder="t('common.search')"
                class="w-64"
               
            />
            <Button :label="t('common.search')" icon="pi pi-search" @click="applyFilters" />
            <Button :label="t('common.clear')" icon="pi pi-times" severity="secondary" @click="clearFilters" />
        </div>

        <!-- Table -->
        <DataTable :value="promotions.data" class="rounded-xl overflow-hidden shadow-sm">
            <Column field="code" :header="t('promotion.code')" class="font-mono font-bold" />
            <Column field="title" :header="t('common.name')" />
            <Column :header="t('promotion.type')">
                <template #body="{ data: row }">
                    <span>
                        {{ row.type === 'percentage' ? t('promotion.percentage') : t('promotion.fixed') }}
                        ({{ row.type === 'percentage' ? row.value + '%' : 'CHF ' + row.value }})
                    </span>
                </template>
            </Column>
            <Column :header="t('restaurant.title')">
                <template #body="{ data: row }">
                    {{ row.restaurant?.name ?? t('promotion.global') }}
                </template>
            </Column>
            <Column :header="t('promotion.usageCount')">
                <template #body="{ data: row }">
                    {{ row.usage_count }}{{ row.usage_limit ? ' / ' + row.usage_limit : '' }}
                </template>
            </Column>
            <Column :header="t('promotion.expiresAt')">
                <template #body="{ data: row }">
                    {{ row.expires_at ? new Date(row.expires_at).toLocaleDateString() : '—' }}
                </template>
            </Column>
            <Column :header="t('common.status')">
                <template #body="{ data: row }">
                    <Tag :severity="statusSeverity(row)" :value="statusLabel(row)" rounded />
                </template>
            </Column>
            <Column :header="t('common.actions')" style="width: 160px">
                <template #body="{ data: row }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            size="small"
                            severity="secondary"
                            @click="router.visit(route('admin.promotions.show', row.id))"
                        />
                        <Button
                            :icon="row.is_active ? 'pi pi-eye-slash' : 'pi pi-eye'"
                            size="small"
                            :severity="row.is_active ? 'warn' : 'success'"
                            @click="toggleActive(row)"
                        />
                        <Button
                            icon="pi pi-trash"
                            size="small"
                            severity="danger"
                            @click="deletePromotion(row.id)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- Pagination -->
        <div v-if="promotions.meta.last_page > 1" class="flex justify-center mt-4 gap-2">
            <Button
                v-for="page in promotions.meta.last_page"
                :key="page"
                :label="String(page)"
                :severity="page === promotions.meta.current_page ? 'primary' : 'secondary'"
                size="small"
                @click="router.get(route('admin.promotions.index'), { page, search: search || undefined })"
            />
        </div>
    </div>
</template>
