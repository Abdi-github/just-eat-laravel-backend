<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast   = useToast();
const confirm = useConfirm();

const props = defineProps<{
    restaurant: {
        id: number;
        name: string;
        menu_categories: Array<{
            id: number;
            name: string | Record<string, string>;
            is_active: boolean;
            sort_order: number;
            menu_items: Array<{
                id: number;
                name: string | Record<string, string>;
                price: number;
                is_available: boolean;
                is_featured: boolean;
                preparation_time: number | null;
            }>;
        }>;
    };
}>();

function resolveName(val: string | Record<string, string> | null | undefined): string {
    if (!val) return '';
    if (typeof val === 'string') return val;
    return val.fr ?? val.en ?? val.de ?? Object.values(val)[0] ?? '';
}

// ── Category form ──────────────────────────────────────────────────────────────
const showCatDialog = ref(false);
const editingCat    = ref<{ id?: number; name_fr: string; name_de: string; name_en: string; sort_order: number; is_active: boolean } | null>(null);

function openNewCategory() {
    editingCat.value  = { name_fr: '', name_de: '', name_en: '', sort_order: 0, is_active: true };
    showCatDialog.value = true;
}

function openEditCategory(cat: (typeof props.restaurant.menu_categories)[0]) {
    const n = cat.name as Record<string, string>;
    editingCat.value = {
        id:         cat.id,
        name_fr:    n.fr ?? '',
        name_de:    n.de ?? '',
        name_en:    n.en ?? '',
        sort_order: cat.sort_order ?? 0,
        is_active:  cat.is_active,
    };
    showCatDialog.value = true;
}

function submitCategory() {
    if (!editingCat.value) return;

    const payload = {
        name:       { fr: editingCat.value.name_fr, de: editingCat.value.name_de, en: editingCat.value.name_en },
        sort_order: editingCat.value.sort_order,
        is_active:  editingCat.value.is_active,
    };

    const routeName = editingCat.value.id
        ? 'admin.menu.categories.update'
        : 'admin.menu.categories.store';

    const routeArgs = editingCat.value.id
        ? [props.restaurant.id, editingCat.value.id]
        : [props.restaurant.id];

    const method = editingCat.value.id ? router.put : router.post;

    method(route(routeName, routeArgs), payload, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: t('common.saved'), life: 2000 });
            showCatDialog.value = false;
        },
        onError: () => toast.add({ severity: 'error', summary: t('common.error'), life: 3000 }),
    });
}

function deleteCategory(catId: number) {
    confirm.require({
        message: t('menu.confirmDeleteCategory'),
        header: t('common.confirm'),
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.menu.categories.destroy', [props.restaurant.id, catId]), {
                onSuccess: () => toast.add({ severity: 'success', summary: t('common.deleted'), life: 2000 }),
            });
        },
    });
}

// ── Item form ──────────────────────────────────────────────────────────────────
const showItemDialog = ref(false);
const editingItem    = ref<{
    id?: number;
    menu_category_id: number;
    name_fr: string; name_de: string; name_en: string;
    price: number; is_available: boolean; is_featured: boolean;
    preparation_time: number | null;
} | null>(null);

const selectedCategoryId = ref<number | null>(null);

const currentCategoryItems = computed(() => {
    if (!selectedCategoryId.value) return [];
    const cat = props.restaurant.menu_categories.find(c => c.id === selectedCategoryId.value);
    return cat ? cat.menu_items : [];
});

function openNewItem(catId: number) {
    selectedCategoryId.value = catId;
    editingItem.value = {
        menu_category_id: catId,
        name_fr: '', name_de: '', name_en: '',
        price: 0, is_available: true, is_featured: false, preparation_time: null,
    };
    showItemDialog.value = true;
}

function openEditItem(catId: number, item: (typeof props.restaurant.menu_categories)[0]['menu_items'][0]) {
    selectedCategoryId.value = catId;
    const n = item.name as Record<string, string>;
    editingItem.value = {
        id:               item.id,
        menu_category_id: catId,
        name_fr:          n.fr ?? '',
        name_de:          n.de ?? '',
        name_en:          n.en ?? '',
        price:            item.price,
        is_available:     item.is_available,
        is_featured:      item.is_featured,
        preparation_time: item.preparation_time,
    };
    showItemDialog.value = true;
}

function submitItem() {
    if (!editingItem.value) return;

    const payload = {
        menu_category_id: editingItem.value.menu_category_id,
        name:             { fr: editingItem.value.name_fr, de: editingItem.value.name_de, en: editingItem.value.name_en },
        price:            editingItem.value.price,
        is_available:     editingItem.value.is_available,
        is_featured:      editingItem.value.is_featured,
        preparation_time: editingItem.value.preparation_time,
    };

    const routeName = editingItem.value.id ? 'admin.menu.items.update' : 'admin.menu.items.store';
    const routeArgs = editingItem.value.id
        ? [props.restaurant.id, editingItem.value.id]
        : [props.restaurant.id];

    const method = editingItem.value.id ? router.put : router.post;

    method(route(routeName, routeArgs), payload, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: t('common.saved'), life: 2000 });
            showItemDialog.value = false;
        },
        onError: () => toast.add({ severity: 'error', summary: t('common.error'), life: 3000 }),
    });
}

function deleteItem(catId: number, itemId: number) {
    confirm.require({
        message: t('menu.confirmDeleteItem'),
        header: t('common.confirm'),
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.menu.items.destroy', [props.restaurant.id, itemId]), {
                onSuccess: () => toast.add({ severity: 'success', summary: t('common.deleted'), life: 2000 }),
            });
        },
    });
}
</script>

<template>
    <Head :title="`${t('menu.title')} — ${restaurant.name}`" />

    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Button icon="pi pi-arrow-left" severity="secondary" text
                    @click="router.visit(route('admin.restaurants.show', restaurant.id))" />
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('menu.title') }} — {{ restaurant.name }}
                </h1>
            </div>
            <Button :label="t('menu.newCategory')" icon="pi pi-plus" @click="openNewCategory" />
        </div>

        <!-- Categories -->
        <div v-for="cat in restaurant.menu_categories" :key="cat.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
            <!-- Category header -->
            <div class="flex items-center justify-between px-4 py-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <div class="flex items-center gap-3">
                    <Tag :value="resolveName(cat.name)" />
                    <Tag :severity="cat.is_active ? 'success' : 'secondary'"
                        :value="cat.is_active ? t('common.active') : t('common.inactive')" />
                </div>
                <div class="flex gap-2">
                    <Button icon="pi pi-plus" size="small" :label="t('menu.newItem')" @click="openNewItem(cat.id)" />
                    <Button icon="pi pi-pencil" severity="secondary" size="small" text @click="openEditCategory(cat)" />
                    <Button icon="pi pi-trash" severity="danger" size="small" text @click="deleteCategory(cat.id)" />
                </div>
            </div>

            <!-- Items table -->
            <DataTable :value="cat.menu_items" class="text-sm" :rows="50">
                <Column field="name" :header="t('common.name')">
                    <template #body="{ data }">{{ resolveName(data.name) }}</template>
                </Column>
                <Column field="price" :header="t('menu.price')">
                    <template #body="{ data }">CHF {{ data.price.toFixed(2) }}</template>
                </Column>
                <Column field="preparation_time" :header="t('menu.prepTime')">
                    <template #body="{ data }">{{ data.preparation_time ? data.preparation_time + ' min' : '—' }}</template>
                </Column>
                <Column field="is_available" :header="t('common.available')">
                    <template #body="{ data }">
                        <Tag :severity="data.is_available ? 'success' : 'secondary'"
                            :value="data.is_available ? t('common.yes') : t('common.no')" />
                    </template>
                </Column>
                <Column field="is_featured" :header="t('menu.featured')">
                    <template #body="{ data }">
                        <Tag v-if="data.is_featured" severity="warning" value="Featured" />
                    </template>
                </Column>
                <Column :header="t('common.actions')" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button icon="pi pi-pencil" severity="secondary" text size="small"
                                @click="openEditItem(cat.id, data)" />
                            <Button icon="pi pi-trash" severity="danger" text size="small"
                                @click="deleteItem(cat.id, data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <p v-if="!cat.menu_items.length" class="text-center text-gray-400 text-sm py-4">
                {{ t('menu.noItems') }}
            </p>
        </div>

        <p v-if="!restaurant.menu_categories.length" class="text-center text-gray-400 py-8">
            {{ t('menu.noCategories') }}
        </p>
    </div>

    <!-- Category Dialog -->
    <Dialog v-model:visible="showCatDialog" :header="editingCat?.id ? t('menu.editCategory') : t('menu.newCategory')"
        modal :style="{ width: '480px' }">
        <div v-if="editingCat" class="space-y-4 pt-2">
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('common.name') }} (FR) *</label>
                <InputText v-model="editingCat.name_fr" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('common.name') }} (DE)</label>
                <InputText v-model="editingCat.name_de" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('common.name') }} (EN)</label>
                <InputText v-model="editingCat.name_en" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('menu.sortOrder') }}</label>
                <InputNumber v-model="editingCat.sort_order" :min="0" class="w-full" />
            </div>
            <div class="flex items-center gap-2">
                <input id="cat-active" v-model="editingCat.is_active" type="checkbox" class="w-4 h-4 accent-orange-500" />
                <label for="cat-active" class="text-sm">{{ t('common.active') }}</label>
            </div>
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" severity="secondary" @click="showCatDialog = false" />
            <Button :label="t('common.save')" icon="pi pi-check" @click="submitCategory" />
        </template>
    </Dialog>

    <!-- Item Dialog -->
    <Dialog v-model:visible="showItemDialog" :header="editingItem?.id ? t('menu.editItem') : t('menu.newItem')"
        modal :style="{ width: '520px' }">
        <div v-if="editingItem" class="space-y-4 pt-2">
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('common.name') }} (FR) *</label>
                <InputText v-model="editingItem.name_fr" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('common.name') }} (DE)</label>
                <InputText v-model="editingItem.name_de" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('common.name') }} (EN)</label>
                <InputText v-model="editingItem.name_en" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('menu.price') }} (CHF) *</label>
                <InputNumber v-model="editingItem.price" :min-fraction-digits="2" :max-fraction-digits="2" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('menu.prepTime') }} (min)</label>
                <InputNumber v-model="editingItem.preparation_time" :min="0" class="w-full" />
            </div>
            <div class="flex gap-6">
                <div class="flex items-center gap-2">
                    <input id="item-available" v-model="editingItem.is_available" type="checkbox" class="w-4 h-4 accent-orange-500" />
                    <label for="item-available" class="text-sm">{{ t('common.available') }}</label>
                </div>
                <div class="flex items-center gap-2">
                    <input id="item-featured" v-model="editingItem.is_featured" type="checkbox" class="w-4 h-4 accent-orange-500" />
                    <label for="item-featured" class="text-sm">{{ t('menu.featured') }}</label>
                </div>
            </div>
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" severity="secondary" @click="showItemDialog = false" />
            <Button :label="t('common.save')" icon="pi pi-check" @click="submitItem" />
        </template>
    </Dialog>
</template>
