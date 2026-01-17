<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast   = useToast();
const confirm = useConfirm();

defineProps<{
    applications: {
        data: Array<{
            id: number;
            first_name: string;
            last_name: string;
            email: string;
            phone?: string;
            application_status: string;
            application_type?: string;
            application_note?: string;
            application_rejection_reason?: string;
            application_reviewed_at?: string;
            is_verified: boolean;
            created_at: string;
        }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    filters: Record<string, string>;
}>();

const localFilters = ref({ status: '', type: '', search: '' });
const showRejectDialog = ref(false);
const rejectingUserId  = ref<number | null>(null);
const rejectReason     = ref('');

function applyFilters() {
    router.get(route('admin.applications.index'), localFilters.value, { preserveState: true, replace: true });
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(() => localFilters.value.search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});
watch(() => [localFilters.value.status, localFilters.value.type], () => applyFilters());

function statusSeverity(status: string) {
    const map: Record<string, string> = {
        pending_approval: 'warning',
        approved:         'success',
        rejected:         'danger',
    };
    return map[status] ?? 'secondary';
}

function approve(userId: number) {
    confirm.require({
        message: t('application.confirmApprove'),
        header:  t('common.confirm'),
        icon:    'pi pi-check-circle',
        accept:  () => {
            router.patch(route('admin.applications.approve', userId), {}, {
                onSuccess: () => toast.add({ severity: 'success', summary: t('application.approved'), life: 2000 }),
            });
        },
    });
}

function openReject(userId: number) {
    rejectingUserId.value = userId;
    rejectReason.value    = '';
    showRejectDialog.value = true;
}

function submitReject() {
    if (!rejectingUserId.value) return;
    router.patch(route('admin.applications.reject', rejectingUserId.value), { reason: rejectReason.value }, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: t('application.rejected'), life: 2000 });
            showRejectDialog.value = false;
        },
    });
}
</script>

<template>
    <Head :title="t('application.title')" />

    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('application.title') }}</h1>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <InputText v-model="localFilters.search" :placeholder="t('common.search')" />

            <Select v-model="localFilters.status" :options="[
                { label: t('common.all'), value: '' },
                { label: t('application.pending'), value: 'pending_approval' },
                { label: t('application.approved'), value: 'approved' },
                { label: t('application.rejected'), value: 'rejected' },
            ]" option-label="label" option-value="value" :placeholder="t('common.status')" />

            <Select v-model="localFilters.type" :options="[
                { label: t('common.all'), value: '' },
                { label: t('application.restaurantOwner'), value: 'restaurant_owner' },
                { label: t('application.courier'), value: 'courier' },
            ]" option-label="label" option-value="value" :placeholder="t('application.type')" />

            <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
            <DataTable :value="applications.data" class="text-sm">
                <Column field="id" header="ID" style="width: 60px" />
                <Column :header="t('common.name')">
                    <template #body="{ data }">{{ data.first_name }} {{ data.last_name }}</template>
                </Column>
                <Column field="email" :header="t('common.email')" />
                <Column field="application_type" :header="t('application.type')">
                    <template #body="{ data }">
                        <Tag v-if="data.application_type"
                            :value="data.application_type === 'restaurant_owner' ? t('application.restaurantOwner') : t('application.courier')"
                            :severity="data.application_type === 'restaurant_owner' ? 'primary' : 'info'" />
                    </template>
                </Column>
                <Column field="application_status" :header="t('common.status')">
                    <template #body="{ data }">
                        <Tag :value="t('application.' + data.application_status.replace('_approval', ''))"
                            :severity="statusSeverity(data.application_status)" />
                    </template>
                </Column>
                <Column field="created_at" :header="t('common.createdAt')">
                    <template #body="{ data }">{{ new Date(data.created_at).toLocaleDateString() }}</template>
                </Column>
                <Column :header="t('common.actions')" style="width: 150px">
                    <template #body="{ data }">
                        <div v-if="data.application_status === 'pending_approval'" class="flex gap-1">
                            <Button icon="pi pi-check" severity="success" size="small" text
                                :title="t('application.approve')" @click="approve(data.id)" />
                            <Button icon="pi pi-times" severity="danger" size="small" text
                                :title="t('application.reject')" @click="openReject(data.id)" />
                        </div>
                        <span v-else class="text-gray-400 text-xs">—</span>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <!-- Reject Dialog -->
    <Dialog v-model:visible="showRejectDialog" :header="t('application.rejectTitle')" modal :style="{ width: '440px' }">
        <div class="space-y-3 pt-2">
            <label class="block text-sm font-medium">{{ t('application.rejectionReason') }}</label>
            <Textarea v-model="rejectReason" rows="4" class="w-full" maxlength="500" />
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" severity="secondary" @click="showRejectDialog = false" />
            <Button :label="t('application.reject')" severity="danger" icon="pi pi-times" @click="submitReject" />
        </template>
    </Dialog>
</template>
