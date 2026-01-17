<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();

const props = defineProps<{
    transaction: {
        id: number;
        amount: number;
        currency: string;
        status: string;
        payment_method: string;
        provider_name: string;
        provider_transaction_id?: string;
        stripe_payment_intent_id?: string;
        refund_amount?: number;
        refund_reason?: string;
        refund_id?: string;
        refunded_at?: string;
        error_message?: string;
        error_code?: string;
        attempts: number;
        ip_address?: string;
        created_at: string;
        order?: {
            id: number;
            order_number: string;
            total: number;
        };
        user?: {
            id: number;
            first_name: string;
            last_name: string;
            email: string;
        };
    };
}>();

function statusSeverity(status: string) {
    const map: Record<string, string> = {
        PENDING: 'warning', PROCESSING: 'info', COMPLETED: 'success',
        FAILED: 'danger', REFUNDED: 'secondary', PARTIAL_REFUND: 'warn',
        CANCELLED: 'secondary', EXPIRED: 'danger',
    };
    return map[status] ?? 'secondary';
}

function fmt(dt?: string) {
    if (!dt) return '—';
    return new Date(dt).toLocaleString();
}

// ── Refund Dialog ──────────────────────────────────────────────────────────
const showRefundDialog = ref(false);
const refundAmount     = ref<number | null>(null);
const refundReason     = ref('');

function submitRefund() {
    if (!refundAmount.value || refundAmount.value <= 0) return;
    router.post(route('admin.payments.refund', props.transaction.order?.id ?? props.transaction.id), {
        amount: refundAmount.value,
        reason: refundReason.value,
    }, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: t('payment.refundProcessed'), life: 2000 });
            showRefundDialog.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`${t('payment.title')} #${transaction.id}`" />

    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Button icon="pi pi-arrow-left" severity="secondary" text
                    @click="router.visit(route('admin.payments.index'))" />
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('payment.title') }} #{{ transaction.id }}
                </h1>
                <Tag :value="transaction.status" :severity="statusSeverity(transaction.status)" />
            </div>
            <Button v-if="transaction.status === 'COMPLETED'" :label="t('payment.refund')"
                icon="pi pi-undo" severity="danger" @click="showRefundDialog = true" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Transaction Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h2 class="text-lg font-semibold">{{ t('payment.details') }}</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.amount') }}</span>
                        <span class="font-bold text-lg">{{ transaction.currency }} {{ transaction.amount?.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.method') }}</span>
                        <span>{{ transaction.payment_method }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.provider') }}</span>
                        <span>{{ transaction.provider_name }}</span>
                    </div>
                    <div v-if="transaction.provider_transaction_id" class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.providerTxId') }}</span>
                        <span class="font-mono text-xs">{{ transaction.provider_transaction_id }}</span>
                    </div>
                    <div v-if="transaction.stripe_payment_intent_id" class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.stripeIntent') }}</span>
                        <span class="font-mono text-xs">{{ transaction.stripe_payment_intent_id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.attempts') }}</span>
                        <span>{{ transaction.attempts }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('common.createdAt') }}</span>
                        <span>{{ fmt(transaction.created_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- Order & User -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h2 class="text-lg font-semibold">{{ t('payment.orderAndUser') }}</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.orderNumber') }}</span>
                        <span>{{ transaction.order?.order_number ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('common.user') }}</span>
                        <span v-if="transaction.user">
                            {{ transaction.user.first_name }} {{ transaction.user.last_name }}
                        </span>
                    </div>
                    <div v-if="transaction.user" class="flex justify-between">
                        <span class="text-gray-500">{{ t('common.email') }}</span>
                        <span>{{ transaction.user.email }}</span>
                    </div>
                    <div v-if="transaction.ip_address" class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.ipAddress') }}</span>
                        <span class="font-mono text-xs">{{ transaction.ip_address }}</span>
                    </div>
                </div>
            </div>

            <!-- Refund Info -->
            <div v-if="['REFUNDED','PARTIAL_REFUND'].includes(transaction.status)"
                class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h2 class="text-lg font-semibold">{{ t('payment.refundInfo') }}</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.refundAmount') }}</span>
                        <span>{{ transaction.currency }} {{ transaction.refund_amount?.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.refundedAt') }}</span>
                        <span>{{ fmt(transaction.refunded_at) }}</span>
                    </div>
                    <div v-if="transaction.refund_reason">
                        <span class="text-gray-500 block">{{ t('payment.refundReason') }}</span>
                        <span>{{ transaction.refund_reason }}</span>
                    </div>
                    <div v-if="transaction.refund_id" class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.refundId') }}</span>
                        <span class="font-mono text-xs">{{ transaction.refund_id }}</span>
                    </div>
                </div>
            </div>

            <!-- Error Info -->
            <div v-if="transaction.error_message" class="bg-red-50 dark:bg-red-900/20 rounded-xl shadow p-6 space-y-3">
                <h2 class="text-lg font-semibold text-red-700 dark:text-red-400">{{ t('payment.errorInfo') }}</h2>
                <div class="text-sm space-y-2">
                    <div v-if="transaction.error_code" class="flex justify-between">
                        <span class="text-gray-500">{{ t('payment.errorCode') }}</span>
                        <span class="font-mono text-xs text-red-600">{{ transaction.error_code }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">{{ t('payment.errorMessage') }}</span>
                        <span class="text-red-600">{{ transaction.error_message }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refund Dialog -->
    <Dialog v-model:visible="showRefundDialog" :header="t('payment.processRefund')" modal :style="{ width: '420px' }">
        <div class="space-y-4 pt-2">
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('payment.refundAmount') }}</label>
                <InputNumber v-model="refundAmount" :min="0.01" :max="transaction.amount"
                    mode="currency" currency="CHF" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('payment.refundReason') }}</label>
                <Textarea v-model="refundReason" rows="3" class="w-full" />
            </div>
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" severity="secondary" @click="showRefundDialog = false" />
            <Button :label="t('payment.processRefund')" icon="pi pi-undo" severity="danger"
                @click="submitRefund" :disabled="!refundAmount || refundAmount <= 0" />
        </template>
    </Dialog>
</template>
