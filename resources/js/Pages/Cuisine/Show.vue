<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    cuisine: {
        id: number;
        slug: string;
        name: Record<string, string>;
        description: Record<string, string> | null;
        icon: string | null;
        image: string | null;
        is_active: boolean;
        sort_order: number;
        restaurants_count: number;
    };
}>();

const editMode = ref(false);
const form = ref({
    name: { ...props.cuisine.name },
    is_active: props.cuisine.is_active,
    sort_order: props.cuisine.sort_order,
});

function save() {
    router.put(route('admin.cuisines.update', props.cuisine.id), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            editMode.value = false;
            toast.add({ severity: 'success', summary: 'Saved', life: 2000 });
        },
    });
}

function confirmDelete() {
    confirm.require({
        message: `Delete cuisine "${props.cuisine.name.fr ?? props.cuisine.slug}"?`,
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.cuisines.destroy', props.cuisine.id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}
</script>

<template>
    <Head :title="cuisine.name.fr ?? cuisine.slug" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link :href="route('admin.cuisines.index')" class="text-gray-500 hover:text-gray-700">
                    <i class="pi pi-arrow-left text-sm" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ cuisine.name.fr }}</h1>
                    <p class="text-sm text-gray-500">{{ cuisine.slug }}</p>
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
            <Tag :severity="cuisine.is_active ? 'success' : 'danger'" :value="cuisine.is_active ? 'Active' : 'Inactive'" />
            <Tag :value="`${cuisine.restaurants_count} restaurants`" severity="info" />
        </div>

        <!-- Details / Edit form -->
        <Card>
            <template #title>Details</template>
            <template #content>
                <div v-if="!editMode" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">FR:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ cuisine.name.fr }}</span>
                    </div>
                    <div v-if="cuisine.name.de">
                        <span class="font-medium text-gray-600">DE:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ cuisine.name.de }}</span>
                    </div>
                    <div v-if="cuisine.name.en">
                        <span class="font-medium text-gray-600">EN:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ cuisine.name.en }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Sort order:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ cuisine.sort_order }}</span>
                    </div>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Name (FR) *</label>
                        <InputText v-model="form.name.fr" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Name (DE)</label>
                        <InputText v-model="form.name.de" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Name (EN)</label>
                        <InputText v-model="form.name.en" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Sort Order</label>
                        <InputNumber v-model="form.sort_order" class="w-full" />
                    </div>
                </div>
            </template>
        </Card>

        <Card v-if="cuisine.image || cuisine.icon">
            <template #title>Media</template>
            <template #content>
                <div class="flex gap-4">
                    <div v-if="cuisine.icon" class="text-center">
                        <p class="text-xs text-gray-500 mb-1">Icon</p>
                        <img :src="cuisine.icon" alt="icon" class="h-12 w-12 object-contain" />
                    </div>
                    <div v-if="cuisine.image" class="text-center">
                        <p class="text-xs text-gray-500 mb-1">Image</p>
                        <img :src="cuisine.image" alt="image" class="h-24 w-40 object-cover rounded" />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
