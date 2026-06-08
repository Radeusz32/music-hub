<script setup lang="ts">
import { ref, computed } from "vue";

defineOptions({ name: "FileUpload" });

const props = withDefaults(
    defineProps<{
        accept?: string;
        maxFileSize?: number;
        disabled?: boolean;
    }>(),
    {
        accept: "*",
        maxFileSize: 10000000,
        disabled: false,
    },
);

const emit = defineEmits<{
    (e: "select", event: { files: File[] }): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);

const fileName = computed(() => selectedFile.value?.name ?? "");
const fileSize = computed(() => {
    if (!selectedFile.value) return "";
    const bytes = selectedFile.value.size;
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
});

function handleFileSelect(e: Event): void {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    if (file.size > props.maxFileSize) {
        alert(
            `Plik jest za duży. Maksymalny rozmiar: ${(props.maxFileSize / (1024 * 1024)).toFixed(0)} MB`,
        );
        input.value = "";
        return;
    }

    selectedFile.value = file;
    emit("select", { files: [file] });
}

function clearFile(): void {
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = "";
    }
}

function openFilePicker(): void {
    fileInput.value?.click();
}

defineExpose({ clearFile, openFilePicker });
</script>

<template>
    <div class="file-upload">
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            :disabled="disabled"
            class="file-input"
            @change="handleFileSelect"
        />

        <div v-if="!selectedFile" class="file-empty" @click="openFilePicker">
            <i class="pi pi-upload" />
            <span>Kliknij, aby wybrać plik</span>
        </div>

        <div v-else class="file-selected">
            <div class="file-info">
                <i class="pi pi-file" />
                <div class="file-details">
                    <span class="file-name">{{ fileName }}</span>
                    <span class="file-size">{{ fileSize }}</span>
                </div>
            </div>
            <button
                type="button"
                class="file-remove"
                :disabled="disabled"
                @click.stop="clearFile"
            >
                <i class="pi pi-times" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.file-upload {
    width: 100%;
}

.file-input {
    display: none;
}

.file-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: rgba(10, 16, 32, 0.7);
    border: 1px dashed rgba(56, 189, 248, 0.2);
    border-radius: 8px;
    color: rgba(148, 163, 184, 0.6);
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.file-empty:hover {
    border-color: rgba(56, 189, 248, 0.4);
    background: rgba(10, 16, 32, 0.9);
    color: rgba(148, 163, 184, 0.8);
}

.file-empty .pi {
    font-size: 1.5rem;
    color: rgba(56, 189, 248, 0.5);
}

.file-selected {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.7rem 1rem;
    background: rgba(10, 16, 32, 0.7);
    border: 1px solid rgba(56, 189, 248, 0.15);
    border-radius: 8px;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
}

.file-info .pi {
    font-size: 1.25rem;
    color: rgba(56, 189, 248, 0.6);
    flex-shrink: 0;
}

.file-details {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}

.file-name {
    font-size: 0.875rem;
    color: #e2e8f0;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-size {
    font-size: 0.75rem;
    color: rgba(148, 163, 184, 0.5);
}

.file-remove {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid rgba(248, 113, 113, 0.2);
    background: rgba(248, 113, 113, 0.08);
    color: rgba(248, 113, 113, 0.7);
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.15s;
    flex-shrink: 0;
}

.file-remove:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.15);
    border-color: rgba(248, 113, 113, 0.4);
    color: #f87171;
}

.file-remove:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
