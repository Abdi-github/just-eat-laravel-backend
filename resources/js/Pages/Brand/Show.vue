<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    brand: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        logo: string | null;
        website: string | null;
        is_active: boolean;
        restaurants_count: number;
        restaurants: Array<{ id: number; name: string; slug: string }>;
    };
}>();

const editMode = ref(false);
const form = ref({
    name: props.brand.name,
    slug: props.brand.slug,
    description: props.brand.description ?? '',
    logo: props.brand.logo ?? '',
    website: props.brand.website ?? '',
    is_active: props.brand.is_active,
});

function save() {
    router.put(route('admin.brands.update', props.brand.id), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            editMode.value = false;
            toast.add({ severity: 'success', summary: 'Saved', life: 2000 });
        },
    });
}

function confirmDelete() {
    confirm.require({
        message: `Delete brand "${props.brand.name}"?`,
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.brands.destroy', props.brand.id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}
</script>

<template>
    <Head :title="brand.name" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link :href="route('admin.brands.index')" class="text-gray-500 hover:text-gray-700">
                    <i class="pi pi-arrow-left text-sm" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ brand.name }}</h1>
                    <p class="text-sm text-gray-500">{{ brand.slug }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button
                    v-if="!editMode"
                    label="Edit"
                    icon="pi pi-pencil"
                    size="small"
                    severity="secondary"
                    @click="editMode = true"
                />
                <template v-else>
                    <Button label="Cancel" size="small" severity="secondary" @click="editMode = false" />
                    <Button label="Save" icon="pi pi-check" size="small" @click="save" />
                </template>
                <Button label="Delete" icon="pi pi-trash" size="small" severity="danger" outlined @click="confirmDelete" />
            </div>
        </div>

        <div class="flex gap-2">
            <Tag :severity="brand.is_active ? 'success' : 'danger'" :value="brand.is_active ? t('common.active') : t('common.inactive')" />
            <Tag :value="`${brand.restaurants_count} ${t('brand.restaurants')}`" severity="info" />
        </div>

        <!-- Detail / Edit Card -->
        <Card>
            <template #content>
                <div v-if="!editMode" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ t('brand.description') }}</span>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ brand.description ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ t('brand.website') }}</span>
                            <p class="mt-1">
                                <a v-if="brand.website" :href="brand.website" target="_blank" class="text-primary-600 hover:underline">{{ brand.website }}</a>
                                <span v-else class="text-gray-400">—</span>
                            </p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ t('brand.logo') }}</span>
                            <p class="mt-1">
                                <img v-if="brand.logo" :src="brand.logo" alt="logo" class="h-10 object-contain" />
                                <span v-else class="text-gray-400">—</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Edit form -->
                <div v-else class="space-y-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">{{ t('brand.name') }}</label>
                        <InputText v-model="form.name" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">{{ t('brand.slug') }}</label>
                        <InputText v-model="form.slug" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">{{ t('brand.description') }}</label>
                        <Textarea v-model="form.description" rows="3" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">{{ t('brand.logo') }}</label>
                        <InputText v-model="form.logo" placeholder="https://..." />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">{{ t('brand.website') }}</label>
                        <InputText v-model="form.website" placeholder="https://..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <ToggleSwitch v-model="form.is_active" />
                        <span class="text-sm font-medium">{{ t('common.active') }}</span>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Restaurants list -->
        <Card v-if="brand.restaurants && brand.restaurants.length">
            <template #title>{{ t('brand.restaurants') }}</template>
            <template #content>
                <ul class="space-y-2">
                    <li v-for="r in brand.restaurants" :key="r.id" class="flex items-center justify-between text-sm">
                        <span>{{ r.name }}</span>
                        <Link :href="route('admin.restaurants.show', r.id)" class="text-primary-600 hover:underline text-xs">View</Link>
                    </li>
                </ul>
            </template>
        </Card>
    </div>
</template>
