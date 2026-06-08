<script setup lang="ts">
defineOptions({ name: "PageToolbar" });

defineSlots<{
    default(): void;
}>();
</script>

<template>
    <div class="page-toolbar">
        <slot />
    </div>
</template>

<style scoped>
.page-toolbar {
    background: linear-gradient(135deg, #0a0f1e 0%, #070b16 60%, #0d1428 100%);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    padding: 1.1rem 1.5rem;
}
</style>
