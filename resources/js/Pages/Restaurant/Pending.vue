<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const confirm = useConfirm();

withDefaults(defineProps<{
    restaurants: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            phone: string;
            created_at: string;
            user?: { first_name: string; last_name: string; email: string };
            address?: { city?: { name: string }; street: string };
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
}>(), {});

function approve(id: number) {
    router.patch(route('admin.restaurants.approve', id));
}

function reject(id: number, name: string) {
    confirm.require({
        message: `Reject and permanently delete "${name}"?`,
        header: 'Reject Restaurant',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        acceptLabel: 'Reject & Delete',
        accept: () => router.delete(route('admin.restaurants.reject', id)),
    });
}
</script>

<template>
    <Head :title="t('restaurant.pendingApprovals')" />

    <ConfirmDialog />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('restaurant.pendingApprovals') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ restaurants.total }} {{ t('restaurant.pendingCount') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
            <DataTable :value="restaurants.data" :rows="20" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center py-8 text-gray-400">{{ t('restaurant.noPendingApprovals') }}</div>
                </template>

                <Column field="name" :header="t('common.name')" />
                <Column :header="t('user.owner')">
                    <template #body="{ data }">
                        <span v-if="data.user">{{ data.user.first_name }} {{ data.user.last_name }}</span>
                        <span v-else class="text-gray-400">—</span>
                    </template>
                </Column>
                <Column :header="t('common.city')">
                    <template #body="{ data }">
                        {{ data.address?.city?.name ?? '—' }}
                    </template>
                </Column>
                <Column field="email" :header="t('common.email')" />
                <Column field="phone" :header="t('common.phone')" />
                <Column :header="t('common.submittedAt')">
                    <template #body="{ data }">
                        {{ new Date(data.created_at).toLocaleDateString() }}
                    </template>
                </Column>
                <Column :header="t('common.status')">
                    <template #body>
                        <Tag :value="t('restaurant.pendingLabel')" severity="warn" />
                    </template>
                </Column>
                <Column :header="t('common.actions')" style="width: 200px">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                :label="t('restaurant.approve')"
                                icon="pi pi-check"
                                severity="success"
                                size="small"
                                @click="approve(data.id)"
                            />
                            <Button
                                :label="t('restaurant.reject')"
                                icon="pi pi-times"
                                severity="danger"
                                size="small"
                                @click="reject(data.id, data.name)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
