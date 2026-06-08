<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Tooltip from "@/Components/Tooltip.vue";
import FileUpload from "@/Components/FileUpload.vue";
import { useToast } from "@/composables/useToast";
import type { InventoryRecord } from "../inventory.resource";
import VinylDisc from "./VinylDisc.vue";
import CoverLightbox from "./CoverLightbox.vue";

defineOptions({ name: "InventoryCover" });

const props = defineProps<{
    record: InventoryRecord;
    formatColor: string;
}>();

const toast = useToast();
const fileUpload = ref<InstanceType<typeof FileUpload> | null>(null);
const coverForm = useForm<{ cover: File | null }>({ cover: null });
const coverPreview = ref<string | null>(props.record.cover_image ?? null);
const showLightbox = ref(false);

function onCoverSelected(e: { files: File[] }): void {
    const file = e.files?.[0];
    if (!file) return;
    coverPreview.value = URL.createObjectURL(file);
    coverForm.cover = file;
    coverForm.post(
        route("tenant.inventory.records.cover", {
            inventoryRecord: props.record.id,
        }),
        {
            forceFormData: true,
            onSuccess: () => {
                fileUpload.value?.clearFile();
            },
            onError: (errors) => {
                coverPreview.value = props.record.cover_image ?? null;
                fileUpload.value?.clearFile();
                const message = Object.values(errors)[0];
                if (message) toast.error(message);
            },
        },
    );
}

function removeCover(): void {
    coverForm.delete(
        route("tenant.inventory.records.cover.destroy", {
            inventoryRecord: props.record.id,
        }),
        {
            onSuccess: () => {
                coverPreview.value = null;
            },
        },
    );
}

function openLightbox(): void {
    if (coverPreview.value) {
        showLightbox.value = true;
    }
}
</script>

<template>
    <div class="cover-wrap">
        <div class="cover-img-box">
            <img
                v-if="coverPreview"
                :src="coverPreview"
                alt="Okładka"
                class="cover-img"
            />
            <VinylDisc v-else :format="record.format" :color="formatColor" />

            <!-- upload overlay -->
            <div
                class="cover-overlay"
                :class="{ 'cover-uploading': coverForm.processing }"
                @click="fileUpload?.openFilePicker()"
            >
                <i v-if="coverForm.processing" class="pi pi-spin pi-spinner" />
                <i v-else class="pi pi-camera" />
                <span>{{ coverPreview ? "Zmień" : "Dodaj okładkę" }}</span>
            </div>

            <!-- zoom button -->
            <Tooltip
                v-if="coverPreview"
                content="Pełny ekran"
                position="left"
                class="cover-zoom-wrap"
            >
                <button class="cover-zoom-btn" @click.stop="openLightbox">
                    <i class="pi pi-expand" />
                </button>
            </Tooltip>
        </div>

        <FileUpload
            ref="fileUpload"
            accept="image/jpeg,image/png,image/webp"
            class="cover-file-upload"
            @select="onCoverSelected"
        />

        <button
            v-if="coverPreview"
            type="button"
            class="cover-remove-btn"
            :disabled="coverForm.processing"
            @click="removeCover"
        >
            <i class="pi pi-trash" />
            Usuń zdjęcie
        </button>

        <CoverLightbox
            :show="showLightbox"
            :src="coverPreview"
            :alt="record.name"
            @close="showLightbox = false"
        />
    </div>
</template>

<style scoped>
.cover-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
}

.cover-img-box {
    position: relative;
    width: 180px;
    height: 180px;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    border: 1px solid rgba(56, 189, 248, 0.12);
}

.cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.cover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    color: transparent;
    transition: all 0.2s;
}

.cover-img-box:hover .cover-overlay {
    background: rgba(0, 0, 0, 0.55);
    color: rgba(255, 255, 255, 0.9);
}

.cover-overlay .pi {
    font-size: 1.2rem;
}

.cover-uploading {
    background: rgba(0, 0, 0, 0.55) !important;
    color: rgba(255, 255, 255, 0.9) !important;
}

.cover-file-upload {
    display: none;
}

.cover-remove-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.75rem;
    background: rgba(248, 113, 113, 0.06);
    border: 1px solid rgba(248, 113, 113, 0.18);
    border-radius: 6px;
    color: rgba(248, 113, 113, 0.7);
    font-size: 0.72rem;
    cursor: pointer;
    transition: all 0.15s;
    width: 180px;
    justify-content: center;
}

.cover-remove-btn:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.12);
    color: #f87171;
}

.cover-remove-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.cover-zoom-wrap {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 2;
    opacity: 0;
    transition: opacity 0.15s;
}

.cover-img-box:hover .cover-zoom-wrap {
    opacity: 1;
}

.cover-zoom-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(0, 0, 0, 0.55);
    color: rgba(255, 255, 255, 0.85);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    transition:
        background 0.15s,
        border-color 0.15s;
}

.cover-zoom-btn:hover {
    background: rgba(56, 189, 248, 0.35);
    border-color: rgba(56, 189, 248, 0.5);
    color: #fff;
}

@media (max-width: 640px) {
    .cover-wrap {
        display: none;
    }
}
</style>
