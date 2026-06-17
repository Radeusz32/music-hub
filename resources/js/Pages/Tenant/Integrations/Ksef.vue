<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Integracja KSeF"
            subtitle="Krajowy System e-Faktur"
            icon="pi pi-receipt"
            icon-color="#60a5fa"
        >
            <section class="placeholder">
                <i class="pi pi-receipt placeholder-icon" />
                <h2 class="placeholder-title">Integracja w przygotowaniu</h2>
                <p class="placeholder-text">
                    Wysyłka i odbiór faktur z Krajowego Systemu e-Faktur będą
                    dostępne wkrótce.
                </p>
            </section>
        </IndexLayout>
    </AppLayout>
</template>

<style scoped>
.placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.85rem;
    padding: 4rem 2rem;
    background: rgba(2, 6, 23, 0.4);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    text-align: center;
}
.placeholder-icon {
    font-size: 2.5rem;
    color: #60a5fa;
}
.placeholder-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0;
}
.placeholder-text {
    font-size: 0.875rem;
    color: rgba(148, 163, 184, 0.6);
    margin: 0;
    max-width: 28rem;
}
</style>
