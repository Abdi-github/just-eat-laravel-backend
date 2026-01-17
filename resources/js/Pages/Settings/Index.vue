<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();
const toast = useToast();
const page  = usePage();

interface AuthAdmin {
    preferred_language?: string;
}

const LANGUAGES = [
    { label: 'Français', value: 'fr' },
    { label: 'Deutsch',  value: 'de' },
    { label: 'English',  value: 'en' },
];

const currentLang = ref<string>(locale.value || 'fr');

function saveLanguage() {
    router.patch(route('admin.settings.language'), { preferred_language: currentLang.value }, {
        onSuccess: () => {
            locale.value = currentLang.value;
            toast.add({ severity: 'success', summary: t('settings.saved'), life: 2000 });
        },
    });
}
</script>

<template>
    <Head :title="t('settings.title')" />

    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('settings.title') }}</h1>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 max-w-lg space-y-6">
            <!-- Language -->
            <div>
                <h2 class="text-lg font-semibold mb-4">{{ t('settings.language') }}</h2>
                <div class="flex items-center gap-4">
                    <Select v-model="currentLang" :options="LANGUAGES" option-label="label" option-value="value"
                        :placeholder="t('settings.selectLanguage')" class="w-48" />
                    <Button :label="t('common.save')" icon="pi pi-check" @click="saveLanguage" />
                </div>
                <p class="text-sm text-gray-500 mt-2">{{ t('settings.languageHint') }}</p>
            </div>
        </div>
    </div>
</template>
