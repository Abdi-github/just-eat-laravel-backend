<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    cantons: {
        data: Array<{ id: number; code: string; name: Record<string, string>; region: string; cities_count: number }>;
        meta: { current_page: number; last_page: number; total: number };
    };
    cities: {
        data: Array<{ id: number; name: string; zip_code: string; canton: { id: number; code: string; name: Record<string, string> } }>;
        meta: { current_page: number; last_page: number; total: number };
    };
    allCantons: Array<{ id: number; code: string; name: Record<string, string> }>;
    filters: { search?: string; city_search?: string; canton_id?: number | string };
}>();

// ─── Tab ─────────────────────────────────────────────────────────────────────
const activeTab = ref<'cantons' | 'cities'>('cantons');

// ─── Filters ─────────────────────────────────────────────────────────────────
const cantonSearch  = ref(props.filters.search ?? '');
const citySearch    = ref(props.filters.city_search ?? '');
const selectedCanton = ref<number | null>(props.filters.canton_id ? Number(props.filters.canton_id) : null);

function applyCantonFilters() {
    router.get(route('admin.locations.index'), {
        search: cantonSearch.value || undefined,
    }, { preserveScroll: true, preserveState: true });
}

function applyCityFilters() {
    router.get(route('admin.locations.index'), {
        city_search: citySearch.value || undefined,
        canton_id: selectedCanton.value || undefined,
    }, { preserveScroll: true, preserveState: true });
}

let cantonTimeout: ReturnType<typeof setTimeout>;
watch(cantonSearch, () => {
    clearTimeout(cantonTimeout);
    cantonTimeout = setTimeout(() => applyCantonFilters(), 300);
});
let cityTimeout: ReturnType<typeof setTimeout>;
watch(citySearch, () => {
    clearTimeout(cityTimeout);
    cityTimeout = setTimeout(() => applyCityFilters(), 300);
});
watch(selectedCanton, () => applyCityFilters());

// ─── Canton Dialog ────────────────────────────────────────────────────────────
const showCantonDialog = ref(false);
const cantonForm = ref({ id: 0, code: '', name_fr: '', name_de: '', region: '' });
const isEditingCanton = ref(false);

function openNewCanton() {
    cantonForm.value = { id: 0, code: '', name_fr: '', name_de: '', region: '' };
    isEditingCanton.value = false;
    showCantonDialog.value = true;
}

function openEditCanton(canton: { id: number; code: string; name: Record<string, string>; region: string }) {
    cantonForm.value = {
        id: canton.id,
        code: canton.code,
        name_fr: canton.name?.fr ?? '',
        name_de: canton.name?.de ?? '',
        region: canton.region ?? '',
    };
    isEditingCanton.value = true;
    showCantonDialog.value = true;
}

function saveCanton() {
    const payload = {
        code: cantonForm.value.code,
        name: { fr: cantonForm.value.name_fr, de: cantonForm.value.name_de },
        region: cantonForm.value.region,
    };

    if (isEditingCanton.value) {
        router.put(route('admin.locations.cantons.update', cantonForm.value.id), payload, {
            preserveScroll: true,
            onSuccess: () => {
                showCantonDialog.value = false;
                toast.add({ severity: 'success', summary: 'Updated', life: 2000 });
            },
        });
    } else {
        router.post(route('admin.locations.cantons.store'), payload, {
            preserveScroll: true,
            onSuccess: () => {
                showCantonDialog.value = false;
                toast.add({ severity: 'success', summary: 'Created', life: 2000 });
            },
        });
    }
}

function deleteCanton(id: number) {
    confirm.require({
        message: t('common.confirmDelete'),
        header: t('common.delete'),
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('admin.locations.cantons.destroy', id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
                onError: (e) => toast.add({ severity: 'error', summary: Object.values(e)[0] as string, life: 4000 }),
            });
        },
    });
}

// ─── City Dialog ──────────────────────────────────────────────────────────────
const showCityDialog = ref(false);
const cityForm = ref({ id: 0, name: '', canton_id: null as number | null, zip_code: '' });
const isEditingCity = ref(false);

function openNewCity() {
    cityForm.value = { id: 0, name: '', canton_id: null, zip_code: '' };
    isEditingCity.value = false;
    showCityDialog.value = true;
}

function openEditCity(city: { id: number; name: string; zip_code: string; canton: { id: number } }) {
    cityForm.value = { id: city.id, name: city.name, canton_id: city.canton?.id ?? null, zip_code: city.zip_code };
    isEditingCity.value = true;
    showCityDialog.value = true;
}

function saveCity() {
    const payload = {
        name: cityForm.value.name,
        canton_id: cityForm.value.canton_id,
        zip_code: cityForm.value.zip_code,
    };

    if (isEditingCity.value) {
        router.put(route('admin.locations.cities.update', cityForm.value.id), payload, {
            preserveScroll: true,
            onSuccess: () => {
                showCityDialog.value = false;
                toast.add({ severity: 'success', summary: 'Updated', life: 2000 });
            },
        });
    } else {
        router.post(route('admin.locations.cities.store'), payload, {
            preserveScroll: true,
            onSuccess: () => {
                showCityDialog.value = false;
                toast.add({ severity: 'success', summary: 'Created', life: 2000 });
            },
        });
    }
}

function deleteCity(id: number) {
    confirm.require({
        message: t('common.confirmDelete'),
        header: t('common.delete'),
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('admin.locations.cities.destroy', id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
                onError: (e) => toast.add({ severity: 'error', summary: Object.values(e)[0] as string, life: 4000 }),
            });
        },
    });
}

function getCantonName(name: Record<string, string>): string {
    return name?.fr ?? name?.de ?? '';
}

const cantonOptions = computed(() =>
    props.allCantons.map(c => ({ label: `${c.code} — ${getCantonName(c.name)}`, value: c.id }))
);
</script>

<template>
    <Head :title="t('location.title')" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800">{{ t('location.title') }}</h1>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 border-b border-gray-200">
            <button
                v-for="tab in ['cantons', 'cities'] as const"
                :key="tab"
                class="px-4 py-2 text-sm font-medium transition-colors"
                :class="activeTab === tab
                    ? 'border-b-2 border-orange-500 text-orange-600'
                    : 'text-gray-500 hover:text-gray-700'"
                @click="activeTab = tab"
            >
                {{ tab === 'cantons' ? t('location.cantons') : t('location.cities') }}
                <span class="ml-1 text-xs text-gray-400">({{ tab === 'cantons' ? cantons.meta?.total : cities.meta?.total }})</span>
            </button>
        </div>

        <!-- ── Cantons Tab ──────────────────────────────────────────────────── -->
        <template v-if="activeTab === 'cantons'">
            <div class="flex items-center gap-3">
                <InputText v-model="cantonSearch" :placeholder="t('common.search')" class="w-64" />
                <Button :label="t('common.search')" icon="pi pi-search" @click="applyCantonFilters" />
                <Button :label="t('location.newCanton')" icon="pi pi-plus" severity="success" class="ml-auto" @click="openNewCanton" />
            </div>

            <DataTable :value="cantons.data" striped-rows class="text-sm">
                <Column field="code" :header="t('location.code')" style="width: 80px" />
                <Column :header="t('location.name')">
                    <template #body="{ data }">{{ getCantonName(data.name) }}</template>
                </Column>
                <Column field="region" :header="t('location.region')" />
                <Column :header="t('location.cities')" style="width: 100px; text-align: right">
                    <template #body="{ data }">{{ data.cities_count }}</template>
                </Column>
                <Column :header="t('common.actions')" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button icon="pi pi-pencil" text size="small" @click="openEditCanton(data)" />
                            <Button icon="pi pi-trash" text size="small" severity="danger" @click="deleteCanton(data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </template>

        <!-- ── Cities Tab ──────────────────────────────────────────────────── -->
        <template v-if="activeTab === 'cities'">
            <div class="flex items-center gap-3 flex-wrap">
                <InputText v-model="citySearch" :placeholder="t('common.search')" class="w-64" />
                <Select
                    v-model="selectedCanton"
                    :options="cantonOptions"
                    option-label="label"
                    option-value="value"
                    :placeholder="t('location.canton')"
                    class="w-48"
                    show-clear
                    @change="applyCityFilters"
                />
                <Button :label="t('common.search')" icon="pi pi-search" @click="applyCityFilters" />
                <Button :label="t('location.newCity')" icon="pi pi-plus" severity="success" class="ml-auto" @click="openNewCity" />
            </div>

            <DataTable :value="cities.data" striped-rows class="text-sm">
                <Column field="name" :header="t('location.name')" />
                <Column field="zip_code" :header="t('location.zipCode')" style="width: 100px" />
                <Column :header="t('location.canton')" style="width: 120px">
                    <template #body="{ data }">{{ data.canton?.code }}</template>
                </Column>
                <Column :header="t('common.actions')" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button icon="pi pi-pencil" text size="small" @click="openEditCity(data)" />
                            <Button icon="pi pi-trash" text size="small" severity="danger" @click="deleteCity(data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </template>
    </div>

    <!-- ── Canton Dialog ──────────────────────────────────────────────────── -->
    <Dialog v-model:visible="showCantonDialog" :header="isEditingCanton ? t('location.canton') : t('location.newCanton')" modal style="width: 480px">
        <div class="space-y-4 pt-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('location.code') }} *</label>
                <InputText v-model="cantonForm.code" class="w-full" maxlength="2" placeholder="GE" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('location.name') }} (FR) *</label>
                <InputText v-model="cantonForm.name_fr" class="w-full" placeholder="Genève" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('location.name') }} (DE)</label>
                <InputText v-model="cantonForm.name_de" class="w-full" placeholder="Genf" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('location.region') }}</label>
                <InputText v-model="cantonForm.region" class="w-full" placeholder="Romandy" />
            </div>
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" text @click="showCantonDialog = false" />
            <Button :label="t('common.save')" icon="pi pi-check" @click="saveCanton" />
        </template>
    </Dialog>

    <!-- ── City Dialog ──────────────────────────────────────────────────────── -->
    <Dialog v-model:visible="showCityDialog" :header="isEditingCity ? t('location.city') : t('location.newCity')" modal style="width: 480px">
        <div class="space-y-4 pt-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('location.name') }} *</label>
                <InputText v-model="cityForm.name" class="w-full" placeholder="Geneva" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('location.canton') }} *</label>
                <Select
                    v-model="cityForm.canton_id"
                    :options="cantonOptions"
                    option-label="label"
                    option-value="value"
                    :placeholder="t('location.canton')"
                    class="w-full"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('location.zipCode') }} *</label>
                <InputText v-model="cityForm.zip_code" class="w-full" placeholder="1200" />
            </div>
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" text @click="showCityDialog = false" />
            <Button :label="t('common.save')" icon="pi pi-check" @click="saveCity" />
        </template>
    </Dialog>
</template>
