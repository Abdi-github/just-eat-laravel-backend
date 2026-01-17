<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast   = useToast();
const confirm = useConfirm();

// ── Props ─────────────────────────────────────────────────────────────────────
interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: string[];
}

const props = defineProps<{
    roles:       Role[];
    permissions: Permission[];
    grouped:     Record<string, Permission[]>;
}>();

// ── Active tab ────────────────────────────────────────────────────────────────
const activeTab = ref<'roles' | 'permissions'>('roles');

// ── Role CRUD ─────────────────────────────────────────────────────────────────
const showRoleDialog      = ref(false);
const editingRole         = ref<Role | null>(null);
const roleForm            = ref({ name: '' });
const roleFormErrors      = ref<Record<string, string>>({});

function openCreateRole() {
    editingRole.value = null;
    roleForm.value    = { name: '' };
    roleFormErrors.value = {};
    showRoleDialog.value = true;
}

function openEditRole(role: Role) {
    editingRole.value   = role;
    roleForm.value      = { name: role.name };
    roleFormErrors.value = {};
    showRoleDialog.value = true;
}

function saveRole() {
    if (!roleForm.value.name.trim()) {
        roleFormErrors.value = { name: t('rbac.nameRequired') };
        return;
    }
    if (editingRole.value) {
        router.put(route('admin.rbac.roles.update', editingRole.value.id), roleForm.value, {
            preserveScroll: true,
            onSuccess: () => { showRoleDialog.value = false; toast.add({ severity: 'success', summary: t('common.updated'), life: 2000 }); },
            onError:   (e) => { roleFormErrors.value = e; },
        });
    } else {
        router.post(route('admin.rbac.roles.store'), roleForm.value, {
            preserveScroll: true,
            onSuccess: () => { showRoleDialog.value = false; toast.add({ severity: 'success', summary: t('common.created'), life: 2000 }); },
            onError:   (e) => { roleFormErrors.value = e; },
        });
    }
}

function deleteRole(role: Role) {
    confirm.require({
        message: t('rbac.confirmDeleteRole', { name: role.name }),
        header:  t('common.delete'),
        icon:    'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.rbac.roles.destroy', role.id), {
                preserveScroll: true,
                onSuccess: () => toast.add({ severity: 'success', summary: t('common.deleted'), life: 2000 }),
                onError:   (e) => toast.add({ severity: 'error', summary: Object.values(e)[0] as string, life: 4000 }),
            });
        },
    });
}

// ── Permission Matrix ──────────────────────────────────────────────────────────
const showMatrixDialog = ref(false);
const matrixRole       = ref<Role | null>(null);
const selectedPerms    = ref<string[]>([]);

function openMatrix(role: Role) {
    matrixRole.value  = role;
    selectedPerms.value = [...role.permissions];
    showMatrixDialog.value = true;
}

function togglePerm(permName: string) {
    const idx = selectedPerms.value.indexOf(permName);
    if (idx === -1) selectedPerms.value.push(permName);
    else selectedPerms.value.splice(idx, 1);
}

function hasPerm(permName: string): boolean {
    return selectedPerms.value.includes(permName);
}

function toggleAll(resource: string, perms: Permission[]) {
    const names = perms.map(p => p.name);
    const allSelected = names.every(n => selectedPerms.value.includes(n));
    if (allSelected) {
        selectedPerms.value = selectedPerms.value.filter(p => !names.includes(p));
    } else {
        names.forEach(n => { if (!selectedPerms.value.includes(n)) selectedPerms.value.push(n); });
    }
}

function resourceAllSelected(perms: Permission[]): boolean {
    return perms.every(p => selectedPerms.value.includes(p.name));
}

function resourceSomeSelected(perms: Permission[]): boolean {
    return perms.some(p => selectedPerms.value.includes(p.name)) && !resourceAllSelected(perms);
}

function saveMatrix() {
    if (!matrixRole.value) return;
    router.put(route('admin.rbac.roles.permissions.sync', matrixRole.value.id), {
        permissions: selectedPerms.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showMatrixDialog.value = false;
            toast.add({ severity: 'success', summary: t('rbac.permissionsUpdated'), life: 2000 });
        },
        onError: (e) => toast.add({ severity: 'error', summary: Object.values(e)[0] as string, life: 4000 }),
    });
}

// ── Permission CRUD ────────────────────────────────────────────────────────────
const showPermDialog  = ref(false);
const permForm        = ref({ name: '' });
const permFormErrors  = ref<Record<string, string>>({});

function openCreatePerm() {
    permForm.value   = { name: '' };
    permFormErrors.value = {};
    showPermDialog.value = true;
}

function savePerm() {
    if (!permForm.value.name.trim()) {
        permFormErrors.value = { name: t('rbac.nameRequired') };
        return;
    }
    router.post(route('admin.rbac.permissions.store'), permForm.value, {
        preserveScroll: true,
        onSuccess: () => { showPermDialog.value = false; toast.add({ severity: 'success', summary: t('common.created'), life: 2000 }); },
        onError:   (e) => { permFormErrors.value = e; },
    });
}

function deletePermission(perm: Permission) {
    confirm.require({
        message: t('rbac.confirmDeletePermission', { name: perm.name }),
        header:  t('common.delete'),
        icon:    'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.rbac.permissions.destroy', perm.id), {
                preserveScroll: true,
                onSuccess: () => toast.add({ severity: 'success', summary: t('common.deleted'), life: 2000 }),
                onError:   (e) => toast.add({ severity: 'error', summary: Object.values(e)[0] as string, life: 4000 }),
            });
        },
    });
}

const permSearch = ref('');
const filteredPerms = computed(() => {
    if (!permSearch.value) return props.permissions;
    return props.permissions.filter(p => p.name.toLowerCase().includes(permSearch.value.toLowerCase()));
});

const groupedKeys = computed(() => Object.keys(props.grouped).sort());
</script>

<template>
    <Head :title="t('rbac.title')" />

    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('rbac.title') }}</h1>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700">
            <button
                :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'roles' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
                data-testid="tab-roles"
                @click="activeTab = 'roles'"
            >
                {{ t('rbac.rolesTab') }} ({{ roles.length }})
            </button>
            <button
                :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'permissions' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
                data-testid="tab-permissions"
                @click="activeTab = 'permissions'"
            >
                {{ t('rbac.permissionsTab') }} ({{ permissions.length }})
            </button>
        </div>

        <!-- ── ROLES TAB ── -->
        <div v-if="activeTab === 'roles'">
            <div class="flex justify-end mb-4">
                <Button
                    :label="t('rbac.createRole')"
                    icon="pi pi-plus"
                    data-testid="btn-create-role"
                    @click="openCreateRole"
                />
            </div>

            <DataTable :value="roles" class="rounded-xl overflow-hidden shadow-sm" data-testid="roles-table">
                <Column field="name" :header="t('rbac.roleName')" />
                <Column :header="t('rbac.permissionCount')">
                    <template #body="{ data: row }">
                        <Tag :value="String(row.permissions.length)" severity="info" rounded />
                    </template>
                </Column>
                <Column :header="t('rbac.assignedPermissions')">
                    <template #body="{ data: row }">
                        <div class="flex flex-wrap gap-1 max-w-lg">
                            <Tag
                                v-for="p in row.permissions.slice(0, 5)"
                                :key="p"
                                :value="p"
                                severity="secondary"
                                rounded
                                class="text-xs"
                            />
                            <Tag
                                v-if="row.permissions.length > 5"
                                :value="'+' + (row.permissions.length - 5)"
                                severity="contrast"
                                rounded
                                class="text-xs"
                            />
                        </div>
                    </template>
                </Column>
                <Column :header="t('common.actions')" style="width: 200px">
                    <template #body="{ data: row }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-shield"
                                size="small"
                                severity="info"
                                :title="t('rbac.editPermissions')"
                                :data-testid="'btn-matrix-' + row.name"
                                @click="openMatrix(row)"
                            />
                            <Button
                                icon="pi pi-pencil"
                                size="small"
                                severity="secondary"
                                :title="t('common.edit')"
                                :data-testid="'btn-edit-role-' + row.name"
                                @click="openEditRole(row)"
                            />
                            <Button
                                icon="pi pi-trash"
                                size="small"
                                severity="danger"
                                :title="t('common.delete')"
                                :data-testid="'btn-delete-role-' + row.name"
                                @click="deleteRole(row)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- ── PERMISSIONS TAB ── -->
        <div v-if="activeTab === 'permissions'">
            <div class="flex justify-between mb-4">
                <InputText
                    v-model="permSearch"
                    :placeholder="t('common.search')"
                    class="w-64"
                    data-testid="perm-search"
                />
                <Button
                    :label="t('rbac.createPermission')"
                    icon="pi pi-plus"
                    data-testid="btn-create-permission"
                    @click="openCreatePerm"
                />
            </div>

            <div class="space-y-4">
                <div
                    v-for="resource in groupedKeys"
                    :key="resource"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden"
                >
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-200 uppercase text-xs tracking-wider">
                        {{ resource }}
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div
                            v-for="perm in grouped[resource].filter(p => !permSearch || p.name.includes(permSearch))"
                            :key="perm.id"
                            class="flex items-center justify-between px-4 py-2"
                        >
                            <span class="font-mono text-sm text-gray-800 dark:text-gray-200">{{ perm.name }}</span>
                            <Button
                                icon="pi pi-trash"
                                size="small"
                                severity="danger"
                                text
                                :data-testid="'btn-delete-perm-' + perm.id"
                                @click="deletePermission(perm)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Role Create/Edit Dialog ── -->
    <Dialog
        v-model:visible="showRoleDialog"
        :header="editingRole ? t('rbac.editRole') : t('rbac.createRole')"
        :modal="true"
        :style="{ width: '400px' }"
        data-testid="role-dialog"
    >
        <div class="flex flex-col gap-4 py-2">
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('rbac.roleName') }}</label>
                <InputText
                    v-model="roleForm.name"
                    class="w-full"
                    :placeholder="t('rbac.roleNamePlaceholder')"
                    data-testid="input-role-name"
                    @keyup.enter="saveRole"
                />
                <p v-if="roleFormErrors.name" class="text-red-500 text-xs mt-1">{{ roleFormErrors.name }}</p>
            </div>
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" severity="secondary" @click="showRoleDialog = false" />
            <Button :label="t('common.save')" data-testid="btn-save-role" @click="saveRole" />
        </template>
    </Dialog>

    <!-- ── Permission Create Dialog ── -->
    <Dialog
        v-model:visible="showPermDialog"
        :header="t('rbac.createPermission')"
        :modal="true"
        :style="{ width: '420px' }"
        data-testid="permission-dialog"
    >
        <div class="flex flex-col gap-4 py-2">
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('rbac.permissionName') }}</label>
                <InputText
                    v-model="permForm.name"
                    class="w-full"
                    placeholder="resource.action"
                    data-testid="input-permission-name"
                    @keyup.enter="savePerm"
                />
                <p class="text-xs text-gray-500 mt-1">{{ t('rbac.permissionNameHint') }}</p>
                <p v-if="permFormErrors.name" class="text-red-500 text-xs mt-1">{{ permFormErrors.name }}</p>
            </div>
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" severity="secondary" @click="showPermDialog = false" />
            <Button :label="t('common.save')" data-testid="btn-save-permission" @click="savePerm" />
        </template>
    </Dialog>

    <!-- ── Permissions Matrix Dialog ── -->
    <Dialog
        v-model:visible="showMatrixDialog"
        :header="t('rbac.editPermissionsFor', { role: matrixRole?.name })"
        :modal="true"
        :style="{ width: '700px', maxHeight: '80vh' }"
        :pt="{ content: { style: 'overflow-y: auto; max-height: 60vh;' } }"
        data-testid="matrix-dialog"
    >
        <div class="space-y-4 py-2">
            <div
                v-for="resource in groupedKeys"
                :key="resource"
                class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden"
            >
                <!-- Resource header with "select all" checkbox -->
                <div
                    class="flex items-center gap-3 px-4 py-2 bg-gray-50 dark:bg-gray-700 cursor-pointer"
                    @click="toggleAll(resource, grouped[resource])"
                >
                    <Checkbox
                        :modelValue="resourceAllSelected(grouped[resource])"
                        :binary="true"
                        :indeterminate="resourceSomeSelected(grouped[resource])"
                        :inputId="'chk-resource-' + resource"
                        class="pointer-events-none"
                    />
                    <span class="font-semibold text-sm uppercase tracking-wider text-gray-700 dark:text-gray-200">
                        {{ resource }}
                    </span>
                </div>

                <!-- Individual permissions -->
                <div class="grid grid-cols-2 gap-1 p-3">
                    <div
                        v-for="perm in grouped[resource]"
                        :key="perm.id"
                        class="flex items-center gap-2 cursor-pointer rounded px-2 py-1 hover:bg-gray-50 dark:hover:bg-gray-700"
                        :data-testid="'matrix-perm-' + perm.name"
                        @click="togglePerm(perm.name)"
                    >
                        <Checkbox
                            :modelValue="hasPerm(perm.name)"
                            :binary="true"
                            :inputId="'chk-' + perm.id"
                            class="pointer-events-none"
                        />
                        <label :for="'chk-' + perm.id" class="text-sm font-mono cursor-pointer">
                            {{ perm.name.split('.')[1] }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between w-full">
                <span class="text-sm text-gray-500">
                    {{ t('rbac.selectedCount', { count: selectedPerms.length }) }}
                </span>
                <div class="flex gap-2">
                    <Button :label="t('common.cancel')" severity="secondary" @click="showMatrixDialog = false" />
                    <Button :label="t('common.save')" data-testid="btn-save-matrix" @click="saveMatrix" />
                </div>
            </div>
        </template>
    </Dialog>
</template>
