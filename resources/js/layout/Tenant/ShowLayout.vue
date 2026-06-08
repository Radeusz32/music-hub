<script setup lang="ts">
import PageToolbar from "./PageToolbar.vue";

interface Props {
    title: string;
}

defineProps<Props>();

defineSlots<{
    default(): void;
    actions(): void;
    toolbar(): void;
}>();
</script>

<template>
    <div class="flex flex-col gap-6">
        <!-- Page header toolbar -->
        <PageToolbar>
            <div class="show-toolbar">
                <h1 class="show-title">{{ title }}</h1>

                <div v-if="$slots.actions" class="show-toolbar-actions">
                    <slot name="actions" />
                </div>
            </div>
        </PageToolbar>

        <!-- Optional secondary toolbar -->
        <PageToolbar v-if="$slots.toolbar">
            <slot name="toolbar" />
        </PageToolbar>

        <!-- Content -->
        <slot />
    </div>
</template>

<style scoped>
.show-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.show-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    flex-wrap: wrap;
}

.show-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0;
}
</style>
