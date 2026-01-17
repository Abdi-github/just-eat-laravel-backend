<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { reactive } from 'vue';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import ToggleSwitch from 'primevue/toggleswitch';
import Button from 'primevue/button';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const form = reactive({
    name: { fr: '', de: '', en: '' },
    slug: '',
    description: { fr: '', de: '', en: '' },
    sort_order: 0,
    is_active: true,
    processing: false,
    errors: {} as Record<string, string>,
});

function autoSlug() {
    if (!form.name.fr) return;
    form.slug = form.name.fr
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function submit() {
    form.processing = true;
    form.errors = {};

    router.post(route('admin.cuisines.store'), {
        name:        form.name,
        slug:        form.slug,
        description: form.description,
        sort_order:  form.sort_order,
        is_active:   form.is_active,
    }, {
        onError: (errors) => {
            form.errors = errors;
            form.processing = false;
        },
        onFinish: () => { form.processing = false; },
    });
}
</script>

<template>
    <Head :title="t('cuisine.create')" />

    <div class="max-w-2xl space-y-6">
        <div class="flex items-center gap-3">
            <Link :href="route('admin.cuisines.index')">
                <Button icon="pi pi-arrow-left" text rounded />
            </Link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('cuisine.create') }}</h1>
        </div>

        <form class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-5" @submit.prevent="submit">
            <!-- Name (FR / DE / EN) -->
            <fieldset class="space-y-3">
                <legend class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('cuisine.name') }}</legend>
                <div class="grid grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">FR *</label>
                        <InputText v-model="form.name.fr" @blur="autoSlug" :invalid="!!form.errors['name.fr']" />
                        <small v-if="form.errors['name.fr']" class="text-red-500">{{ form.errors['name.fr'] }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">DE</label>
                        <InputText v-model="form.name.de" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">EN</label>
                        <InputText v-model="form.name.en" />
                    </div>
                </div>
            </fieldset>

            <!-- Slug -->
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('cuisine.slug') }} *</label>
                <InputText v-model="form.slug" :invalid="!!form.errors.slug" />
                <small v-if="form.errors.slug" class="text-red-500">{{ form.errors.slug }}</small>
            </div>

            <!-- Description (FR / DE / EN) -->
            <fieldset class="space-y-3">
                <legend class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('common.description') }}</legend>
                <div class="grid grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">FR</label>
                        <InputText v-model="form.description.fr" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">DE</label>
                        <InputText v-model="form.description.de" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">EN</label>
                        <InputText v-model="form.description.en" />
                    </div>
                </div>
            </fieldset>

            <!-- Sort order + Active -->
            <div class="flex gap-6 items-end">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('cuisine.sortOrder') }}</label>
                    <InputNumber v-model="form.sort_order" :min="0" show-buttons />
                </div>
                <div class="flex items-center gap-2">
                    <ToggleSwitch v-model="form.is_active" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ t('common.active') }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <Button
                    type="submit"
                    :label="t('common.save')"
                    icon="pi pi-check"
                    :loading="form.processing"
                />
                <Link :href="route('admin.cuisines.index')">
                    <Button :label="t('common.cancel')" icon="pi pi-times" severity="secondary" type="button" />
                </Link>
            </div>
        </form>
    </div>
</template>
