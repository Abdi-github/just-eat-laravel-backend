<script setup lang="ts">
import { ref } from 'vue';
import AppSidebar from './Components/AppSidebar.vue';
import AppTopbar from './Components/AppTopbar.vue';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';

const sidebarOpen = ref(true);

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}
</script>

<template>
    <div class="flex h-screen bg-gray-100 dark:bg-gray-900 overflow-hidden">
        <!-- Sidebar -->
        <AppSidebar :open="sidebarOpen" />

        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <AppTopbar @toggle-sidebar="toggleSidebar" />

            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>

        <!-- PrimeVue overlay components -->
        <Toast />
        <ConfirmDialog />
    </div>
</template>
