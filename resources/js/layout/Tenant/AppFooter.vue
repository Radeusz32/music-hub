<script setup lang="ts"></script>

<template>
    <footer
        class="h-12 flex items-center justify-center text-xs"
        style="
            background: var(--surface-card);
            border-top: 1px solid var(--surface-border);
            color: var(--text-color-secondary);
        "
    >
        © {{ new Date().getFullYear() }} SoundBase
    </footer>
</template>
