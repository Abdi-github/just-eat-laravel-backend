<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from 'primevue/button';
import Menu from 'primevue/menu';

const emit = defineEmits<{ (e: 'toggle-sidebar'): void }>();

const { t, locale } = useI18n();
const page = usePage();
const userMenu = ref<InstanceType<typeof Menu>>();

const admin = computed(() => (page.props as any).auth?.admin ?? { name: 'Admin' });

const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
});

function toggleDark() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

const languages = [
    { label: 'EN', value: 'en' },
    { label: 'FR', value: 'fr' },
    { label: 'DE', value: 'de' },
];

const userMenuItems = computed(() => [
    {
        label: admin.value.name,
        items: [
            {
                label: t('nav.logout'),
                icon: 'pi pi-sign-out',
                command: () => {
                    router.post(route('logout'));
                },
            },
        ],
    },
]);

function switchLocale(lang: string) {
    locale.value = lang;
}
</script>

<template>
    <header class="bg-white dark:bg-gray-800 shadow-sm h-16 flex items-center justify-between px-4 flex-shrink-0">
        <!-- Left: hamburger -->
        <Button
            icon="pi pi-bars"
            text
            rounded
            severity="secondary"
            @click="emit('toggle-sidebar')"
        />

        <!-- Right: locale + user menu -->
        <div class="flex items-center gap-2">
            <!-- Dark mode toggle -->
            <Button
                :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'"
                text
                rounded
                severity="secondary"
                :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                @click="toggleDark"
            />

            <!-- Language switcher -->
            <div class="flex gap-1">
                <Button
                    v-for="lang in languages"
                    :key="lang.value"
                    :label="lang.label"
                    :text="locale !== lang.value"
                    size="small"
                    severity="secondary"
                    @click="switchLocale(lang.value)"
                />
            </div>

            <!-- User menu -->
            <Button
                :label="admin.name"
                icon="pi pi-chevron-down"
                icon-pos="right"
                text
                severity="secondary"
                @click="(e) => userMenu?.toggle(e)"
            />
            <Menu ref="userMenu" :model="userMenuItems" popup />
        </div>
    </header>
</template>
