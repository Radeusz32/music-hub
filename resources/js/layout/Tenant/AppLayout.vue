<script setup lang="ts">
import { ref } from "vue";
import AppSidebar from "./AppSidebar.vue";
import AppTopbar from "./AppTopbar.vue";
import AppFooter from "./AppFooter.vue";

const sidebarOpen = ref(true);

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}
</script>

<template>
    <div
        class="flex min-h-screen font-sans"
        style="background: var(--surface-ground); color: var(--text-color)"
    >
        <AppSidebar :open="sidebarOpen" />

        <div class="flex flex-1 flex-col">
            <AppTopbar @toggle-sidebar="toggleSidebar" />

            <main class="flex-1 p-8">
                <slot />
            </main>

            <AppFooter />
        </div>
    </div>
</template>
