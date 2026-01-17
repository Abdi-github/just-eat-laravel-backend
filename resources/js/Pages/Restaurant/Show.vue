<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const props = defineProps<{
    restaurant: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        phone: string | null;
        email: string | null;
        website: string | null;
        logo: string | null;
        cover_image: string | null;
        is_active: boolean;
        is_featured: boolean;
        price_range: string;
        average_rating: number | string | null;
        total_reviews: number;
        minimum_order: number;
        delivery_fee: number;
        estimated_delivery_time: number;
        accepts_pickup: boolean;
        accepts_delivery: boolean;
        orders_count: number;
        reviews_count: number;
        brand: { id: number; name: string } | null;
        address: {
            street: string;
            street_number: string;
            zip_code: string;
            city: { name: string; canton: { name: string; code: string } };
        } | null;
        user: { id: number; first_name: string; last_name: string; email: string } | null;
        cuisines: Array<{ id: number; name: string | Record<string, string> }>;
        menu_categories: Array<{
            id: number;
            name: string | Record<string, string>;
            is_active: boolean;
            menu_items: Array<{ id: number; name: string | Record<string, string>; price: number; is_available: boolean }>;
        }>;
        delivery_zones: Array<{ id: number; zone_name: string; radius_km: number; delivery_fee: number; minimum_order: number }>;
        opening_hours: Array<{ id: number; day_of_week: number; open_time: string | null; close_time: string | null; is_closed: boolean }>;
        created_at: string;
    };
}>();

const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

function resolveName(val: string | Record<string, string> | null): string {
    if (!val) return '';
    if (typeof val === 'string') return val;
    return val.fr ?? val.en ?? val.de ?? Object.values(val)[0] ?? '';
}

function toggleActive() {
    router.put(route('admin.restaurants.update', props.restaurant.id), {
        is_active: !props.restaurant.is_active,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function toggleFeatured() {
    router.put(route('admin.restaurants.update', props.restaurant.id), {
        is_featured: !props.restaurant.is_featured,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', life: 2000 }),
    });
}

function confirmDelete() {
    confirm.require({
        message: `Delete restaurant "${props.restaurant.name}"?`,
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.restaurants.destroy', props.restaurant.id), {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Deleted', life: 2000 }),
            });
        },
    });
}
</script>

<template>
    <Head :title="restaurant.name" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link :href="route('admin.restaurants.index')" class="text-gray-500 hover:text-gray-700">
                    <i class="pi pi-arrow-left text-sm" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ restaurant.name }}</h1>
                    <p class="text-sm text-gray-500">{{ restaurant.slug }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button
                    :label="restaurant.is_featured ? 'Unfeature' : 'Feature'"
                    :icon="restaurant.is_featured ? 'pi pi-star-fill' : 'pi pi-star'"
                    size="small"
                    :severity="restaurant.is_featured ? 'warning' : 'secondary'"
                    @click="toggleFeatured"
                />
                <Button
                    :label="restaurant.is_active ? 'Deactivate' : 'Activate'"
                    size="small"
                    :severity="restaurant.is_active ? 'danger' : 'success'"
                    @click="toggleActive"
                />
                <Button label="Delete" icon="pi pi-trash" size="small" severity="danger" outlined @click="confirmDelete" />
            </div>
        </div>

        <!-- Status badges -->
        <div class="flex gap-2">
            <Tag :severity="restaurant.is_active ? 'success' : 'danger'" :value="restaurant.is_active ? 'Active' : 'Inactive'" />
            <Tag v-if="restaurant.is_featured" severity="warning" value="Featured" />
            <Tag :value="restaurant.price_range" severity="info" />
        </div>

        <!-- Info cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card>
                <template #content>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ restaurant.orders_count }}</p>
                        <p class="text-sm text-gray-500">Total Orders</p>
                    </div>
                </template>
            </Card>
            <Card>
                <template #content>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ restaurant.average_rating != null ? parseFloat(String(restaurant.average_rating)).toFixed(1) : 'N/A' }}</p>
                        <p class="text-sm text-gray-500">Avg Rating ({{ restaurant.total_reviews }} reviews)</p>
                    </div>
                </template>
            </Card>
            <Card>
                <template #content>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">CHF {{ restaurant.minimum_order }}</p>
                        <p class="text-sm text-gray-500">Minimum Order</p>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Details -->
        <Card>
            <template #title>Details</template>
            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div v-if="restaurant.address">
                        <span class="font-medium text-gray-600">Address:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ restaurant.address.street }} {{ restaurant.address.street_number }},
                            {{ restaurant.address.zip_code }} {{ restaurant.address.city?.name }}
                            ({{ restaurant.address.city?.canton?.code }})
                        </span>
                    </div>
                    <div v-if="restaurant.phone">
                        <span class="font-medium text-gray-600">Phone:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ restaurant.phone }}</span>
                    </div>
                    <div v-if="restaurant.email">
                        <span class="font-medium text-gray-600">Email:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ restaurant.email }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Delivery fee:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">CHF {{ restaurant.delivery_fee }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Est. delivery:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ restaurant.estimated_delivery_time }} min</span>
                    </div>
                    <div v-if="restaurant.brand">
                        <span class="font-medium text-gray-600">Brand:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ restaurant.brand.name }}</span>
                    </div>
                    <div v-if="restaurant.user">
                        <span class="font-medium text-gray-600">Owner:</span>
                        <Link :href="route('admin.users.show', restaurant.user.id)" class="ml-2 text-blue-600 hover:underline">
                            {{ restaurant.user.first_name }} {{ restaurant.user.last_name }}
                        </Link>
                    </div>
                    <div v-if="restaurant.cuisines.length">
                        <span class="font-medium text-gray-600">Cuisines:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ restaurant.cuisines.map(c => resolveName(c.name)).join(', ') }}
                        </span>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Tabs -->
        <TabView>
            <!-- Menu -->
            <TabPanel header="Menu">
                <div class="space-y-4">
                    <div v-for="cat in restaurant.menu_categories" :key="cat.id" class="border rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ resolveName(cat.name) }}</h3>
                            <Tag :severity="cat.is_active ? 'success' : 'secondary'" :value="cat.is_active ? 'Active' : 'Inactive'" />
                        </div>
                        <DataTable :value="cat.menu_items" striped-rows size="small">
                            <Column field="name" header="Item">
                                <template #body="{ data }">{{ resolveName(data.name) }}</template>
                            </Column>
                            <Column field="price" header="Price">
                                <template #body="{ data }">CHF {{ data.price }}</template>
                            </Column>
                            <Column field="is_available" header="Available">
                                <template #body="{ data }">
                                    <Tag :severity="data.is_available ? 'success' : 'danger'" :value="data.is_available ? 'Yes' : 'No'" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                    <p v-if="!restaurant.menu_categories.length" class="text-gray-500 text-sm">No menu categories.</p>
                </div>
            </TabPanel>

            <!-- Delivery Zones -->
            <TabPanel header="Delivery Zones">
                <DataTable :value="restaurant.delivery_zones" striped-rows>
                    <Column field="zone_name" header="Zone" />
                    <Column field="radius_km" header="Radius (km)" />
                    <Column field="delivery_fee" header="Fee">
                        <template #body="{ data }">CHF {{ data.delivery_fee }}</template>
                    </Column>
                    <Column field="minimum_order" header="Min Order">
                        <template #body="{ data }">CHF {{ data.minimum_order }}</template>
                    </Column>
                    <Column field="estimated_time" header="Est. Time (min)" />
                </DataTable>
                <p v-if="!restaurant.delivery_zones.length" class="text-gray-500 text-sm mt-2">No delivery zones.</p>
            </TabPanel>

            <!-- Opening Hours -->
            <TabPanel header="Opening Hours">
                <DataTable :value="restaurant.opening_hours" striped-rows>
                    <Column field="day_of_week" header="Day">
                        <template #body="{ data }">{{ dayNames[data.day_of_week] }}</template>
                    </Column>
                    <Column field="is_closed" header="Status">
                        <template #body="{ data }">
                            <Tag v-if="data.is_closed" severity="danger" value="Closed" />
                            <span v-else class="text-sm text-gray-700 dark:text-gray-300">
                                {{ data.open_time }} – {{ data.close_time }}
                            </span>
                        </template>
                    </Column>
                </DataTable>
                <p v-if="!restaurant.opening_hours.length" class="text-gray-500 text-sm mt-2">No opening hours.</p>
            </TabPanel>
        </TabView>
    </div>
</template>
