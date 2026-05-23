<script setup lang="ts">
import { ref } from "vue";
import AppFooter from "./AppFooter.vue";
import AppSidebar from "./AppSidebar.vue";
import AppTopbar from "./AppTopbar.vue";

const sidebarOpen = ref(true);

function toggleSidebar(): void {
    sidebarOpen.value = !sidebarOpen.value;
}
</script>

<template>
    <div
        class="flex min-h-screen font-sans"
        style="background: var(--surface-ground); color: var(--text-color)"
    >
        <AppSidebar :open="sidebarOpen" />

        <div class="flex min-w-0 flex-1 flex-col">
            <AppTopbar @toggle-sidebar="toggleSidebar" />

            <main class="flex-1 overflow-auto p-6">
                <slot />
            </main>

            <AppFooter />
        </div>

        <Toast position="top-right" />
        <ConfirmDialog />
    </div>
</template>
