<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineProps<{ open: boolean }>();

const { t } = useI18n();
const page = usePage();

const navItems = [
    {
        label: () => t('nav.dashboard'),
        icon: 'pi pi-home',
        route: 'admin.dashboard',
    },
    {
        label: () => t('nav.analytics'),
        icon: 'pi pi-chart-bar',
        route: 'admin.analytics.index',
    },
    {
        label: () => t('nav.restaurants'),
        icon: 'pi pi-building',
        route: 'admin.restaurants.index',
    },
    {
        label: () => t('nav.pendingRestaurants'),
        icon: 'pi pi-clock',
        route: 'admin.restaurants.pending',
    },
    {
        label: () => t('nav.users'),
        icon: 'pi pi-users',
        route: 'admin.users.index',
    },
    {
        label: () => t('nav.orders'),
        icon: 'pi pi-shopping-cart',
        route: 'admin.orders.index',
    },
    {
        label: () => t('nav.cuisines'),
        icon: 'pi pi-tag',
        route: 'admin.cuisines.index',
    },
    {
        label: () => t('nav.reviews'),
        icon: 'pi pi-star',
        route: 'admin.reviews.index',
    },
    {
        label: () => t('nav.locations'),
        icon: 'pi pi-map-marker',
        route: 'admin.locations.index',
    },
    {
        label: () => t('nav.promotions'),
        icon: 'pi pi-percentage',
        route: 'admin.promotions.index',
    },
    {
        label: () => t('nav.stampCards'),
        icon: 'pi pi-id-card',
        route: 'admin.stamp-cards.index',
    },
    {
        label: () => t('nav.notifications'),
        icon: 'pi pi-bell',
        route: 'admin.notifications.index',
    },
    {
        label: () => t('nav.applications'),
        icon: 'pi pi-id-card',
        route: 'admin.applications.index',
    },
    {
        label: () => t('nav.deliveries'),
        icon: 'pi pi-truck',
        route: 'admin.deliveries.index',
    },
    {
        label: () => t('nav.payments'),
        icon: 'pi pi-credit-card',
        route: 'admin.payments.index',
    },
    {
        label: () => t('nav.settings'),
        icon: 'pi pi-cog',
        route: 'admin.settings.index',
    },
    {
        label: () => t('nav.rbac'),
        icon: 'pi pi-lock',
        route: 'admin.rbac.index',
    },
];

function isActive(routeName: string): boolean {
    return page.url.startsWith('/' + routeName.replace('admin.', 'admin/').replace('.index', '').replace('.', '/'));
}
</script>

<template>
    <aside
        :class="[
            'bg-gray-900 text-white transition-all duration-300 flex flex-col',
            open ? 'w-64' : 'w-16',
        ]"
    >
        <!-- Logo -->
        <div class="flex items-center h-16 px-4 border-b border-gray-700">
            <span v-if="open" class="text-orange-400 font-bold text-xl truncate">
                Just Eat Admin
            </span>
            <span v-else class="text-orange-400 font-bold text-xl">JE</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 py-4 overflow-y-auto">
            <Link
                v-for="item in navItems"
                :key="item.route"
                :href="route(item.route)"
                :class="[
                    'flex items-center px-4 py-3 text-sm transition-colors duration-150',
                    page.url.includes(item.route.replace('admin.', '/admin/').replace('.index', ''))
                        ? 'bg-orange-500 text-white'
                        : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                ]"
            >
                <i :class="[item.icon, 'text-lg flex-shrink-0']" />
                <span v-if="open" class="ml-3 truncate">{{ item.label() }}</span>
            </Link>
        </nav>
    </aside>
</template>
