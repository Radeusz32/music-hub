<script setup lang="ts">
import PageToolbar from "./PageToolbar.vue";

interface Props {
    title: string;
    subtitle?: string;
    icon?: string;
    iconColor?: string;
}

withDefaults(defineProps<Props>(), {
    iconColor: "#38bdf8",
});

defineSlots<{
    default(): void;
    toolbar(): void;
}>();
</script>

<template>
    <div class="index-page">
        <!-- Toolbar (title + actions) -->
        <PageToolbar>
            <div class="index-toolbar">
                <div class="index-titles">
                    <h1 class="index-title">
                        <i
                            v-if="icon"
                            :class="icon"
                            class="index-title-icon"
                            :style="{ color: iconColor }"
                        />
                        {{ title }}
                    </h1>
                    <p v-if="subtitle" class="index-subtitle">
                        {{ subtitle }}
                    </p>
                </div>

                <div v-if="$slots.toolbar" class="index-toolbar-actions">
                    <slot name="toolbar" />
                </div>
            </div>
        </PageToolbar>

        <!-- Content (DataTable) -->
        <slot />
    </div>
</template>

<style scoped>
.index-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.index-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.index-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    flex-wrap: wrap;
}

.index-title {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0 0 0.25rem;
}

.index-title-icon {
    font-size: 1.2rem;
}

.index-subtitle {
    font-size: 0.875rem;
    color: rgba(148, 163, 184, 0.6);
    margin: 0;
}
</style>
