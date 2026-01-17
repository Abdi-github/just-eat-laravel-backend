<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();

const typeOptions = [
    { label: 'Order Placed',       value: 'ORDER_PLACED' },
    { label: 'Order Confirmed',    value: 'ORDER_ACCEPTED' },
    { label: 'Order Delivered',    value: 'ORDER_DELIVERED' },
    { label: 'Order Cancelled',    value: 'ORDER_CANCELLED' },
    { label: 'Welcome',            value: 'WELCOME' },
    { label: 'Promotion',          value: 'PROMOTION_NEW' },
    { label: 'System',             value: 'SYSTEM' },
    { label: 'Restaurant Approved', value: 'RESTAURANT_APPROVED' },
];

const channelOptions = [
    { label: 'In-App',      value: 'IN_APP' },
    { label: 'Email',       value: 'EMAIL' },
    { label: 'Both',        value: 'BOTH' },
];

const priorityOptions = [
    { label: 'Low',    value: 'LOW' },
    { label: 'Normal', value: 'NORMAL' },
    { label: 'High',   value: 'HIGH' },
    { label: 'Urgent', value: 'URGENT' },
];

// ── Form state ─────────────────────────────────────────────────────────────────
const userIdsInput = ref('');
const type         = ref('SYSTEM');
const title        = ref('');
const body         = ref('');
const channel      = ref('BOTH');
const priority     = ref('NORMAL');
const sendToAll    = ref(false);
const loading      = ref(false);

function submit() {
    loading.value = true;

    const routeName = sendToAll.value
        ? 'admin.notifications.send-all'
        : 'admin.notifications.send';

    const payload: Record<string, unknown> = {
        type:     type.value,
        title:    title.value,
        body:     body.value,
        channel:  channel.value,
        priority: priority.value,
    };

    if (! sendToAll.value) {
        const ids = userIdsInput.value
            .split(',')
            .map(s => parseInt(s.trim(), 10))
            .filter(n => !isNaN(n));
        payload.user_ids = ids;
    }

    router.post(route(routeName), payload, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: t('notification.sent'), life: 3000 });
            title.value        = '';
            body.value         = '';
            userIdsInput.value = '';
        },
        onError: () => {
            toast.add({ severity: 'error', summary: t('common.error'), life: 3000 });
        },
        onFinish: () => { loading.value = false; },
    });
}
</script>

<template>
    <Head :title="t('notification.title')" />

    <div class="p-6 max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ t('notification.title') }}
            </h1>
        </div>

        <!-- Send Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-5">
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                {{ t('notification.send') }}
            </h2>

            <!-- Send to all toggle -->
            <div class="flex items-center gap-3">
                <input
                    id="send-all"
                    v-model="sendToAll"
                    type="checkbox"
                    class="w-4 h-4 accent-orange-500"
                />
                <label for="send-all" class="text-sm text-gray-700 dark:text-gray-300">
                    {{ t('notification.sendToAll') }}
                </label>
            </div>

            <!-- User IDs -->
            <div v-if="!sendToAll">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ t('notification.userIds') }}
                </label>
                <InputText
                    v-model="userIdsInput"
                    :placeholder="t('notification.userIdsPlaceholder')"
                    class="w-full"
                />
                <p class="text-xs text-gray-400 mt-1">{{ t('notification.userIdsHint') }}</p>
            </div>

            <!-- Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ t('notification.type') }}
                </label>
                <Select
                    v-model="type"
                    :options="typeOptions"
                    option-label="label"
                    option-value="value"
                    class="w-full"
                />
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ t('notification.notificationTitle') }}
                </label>
                <InputText v-model="title" class="w-full" :placeholder="t('notification.notificationTitle')" />
            </div>

            <!-- Body -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ t('notification.body') }}
                </label>
                <Textarea v-model="body" rows="3" class="w-full" :placeholder="t('notification.body')" />
            </div>

            <!-- Channel + Priority row -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('notification.channel') }}
                    </label>
                    <Select
                        v-model="channel"
                        :options="channelOptions"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('notification.priority') }}
                    </label>
                    <Select
                        v-model="priority"
                        :options="priorityOptions"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                    />
                </div>
            </div>

            <Button
                :label="t('notification.send')"
                icon="pi pi-send"
                :loading="loading"
                :disabled="!title || !body || (!sendToAll && !userIdsInput)"
                class="w-full"
                @click="submit"
            />
        </div>
    </div>
</template>
