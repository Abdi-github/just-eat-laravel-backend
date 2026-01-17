<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import ToggleSwitch from 'primevue/toggleswitch';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();

const form = ref({
    name: '',
    slug: '',
    description: '',
    logo: '',
    website: '',
    is_active: true,
});

const submitting = ref(false);

function generateSlug() {
    form.value.slug = form.value.name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
}

function submit() {
    submitting.value = true;
    router.post(route('admin.brands.store'), form.value, {
        onSuccess: () => toast.add({ severity: 'success', summary: t('common.created'), life: 2000 }),
        onError: () => toast.add({ severity: 'error', summary: t('common.error'), life: 3000 }),
        onFinish: () => { submitting.value = false; },
    });
}
</script>

<template>
    <Head :title="t('brand.create')" />

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <Button icon="pi pi-arrow-left" text @click="router.visit(route('admin.brands.index'))" />
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('brand.create') }}</h1>
        </div>

        <form class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-5" @submit.prevent="submit">
            <!-- Name -->
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('brand.name') }} *</label>
                <InputText v-model="form.name" @blur="generateSlug" required />
            </div>

            <!-- Slug -->
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('brand.slug') }}</label>
                <InputText v-model="form.slug" />
            </div>

            <!-- Description -->
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('brand.description') }}</label>
                <Textarea v-model="form.description" rows="3" />
            </div>

            <!-- Logo URL -->
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('brand.logo') }}</label>
                <InputText v-model="form.logo" placeholder="https://..." />
            </div>

            <!-- Website -->
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('brand.website') }}</label>
                <InputText v-model="form.website" placeholder="https://..." />
            </div>

            <!-- Active -->
            <div class="flex items-center gap-3">
                <ToggleSwitch v-model="form.is_active" />
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('common.active') }}</label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <Button
                    :label="t('common.cancel')"
                    severity="secondary"
                    type="button"
                    @click="router.visit(route('admin.brands.index'))"
                />
                <Button :label="t('common.save')" type="submit" :loading="submitting" />
            </div>
        </form>
    </div>
</template>
