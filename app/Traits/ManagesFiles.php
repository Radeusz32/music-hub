<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ManagesFiles
{
    /**
     * Upload a single file to a media collection.
     *
     * Every upload passes through beforeUpload() and afterUpload() hooks,
     * which will be wired to StorageTracker and OptimizationService.
     */
    protected function uploadFile(HasMedia $model, UploadedFile $file, string $collection = 'default'): Media
    {
        $this->beforeUpload($file);

        $media = $model->addMedia($file)->toMediaCollection($collection);

        $this->afterUpload($media);

        return $media;
    }

    /**
     * Upload multiple files to a media collection.
     *
     * @param  UploadedFile[]  $files
     * @return Collection<int, Media>
     */
    protected function uploadFiles(HasMedia $model, array $files, string $collection = 'default'): Collection
    {
        return collect($files)->map(
            fn (UploadedFile $file): Media => $this->uploadFile($model, $file, $collection),
        );
    }

    /**
     * Remove a specific media item by ID.
     */
    protected function destroyFile(HasMedia $model, int $mediaId): void
    {
        $media = $model->media()->find($mediaId);

        if ($media instanceof Media) {
            $this->beforeDestroy($media);
            $media->delete();
        }
    }

    /**
     * Remove all media from a collection.
     */
    protected function destroyFiles(HasMedia $model, string $collection = 'default'): void
    {
        $model->getMedia($collection)->each(function (Media $media): void {
            $this->beforeDestroy($media);
        });

        $model->clearMediaCollection($collection);
    }

    // ── Hooks ────────────────────────────────────────────────────────────────
    // Called unconditionally on every file operation.
    // Wire StorageTracker and OptimizationService here when ready.

    /**
     * Runs before upload — intended for tenant disk quota checks.
     *
     * @future app(StorageTracker::class)->assertHasSpace($file->getSize())
     */
    protected function beforeUpload(UploadedFile $file): void {}

    /**
     * Runs after upload — intended for image optimization.
     *
     * @future app(OptimizationService::class)->optimize($media)
     */
    protected function afterUpload(Media $media): void {}

    /**
     * Runs before file removal — intended for freeing tracked storage quota.
     *
     * @future app(StorageTracker::class)->release($media->size)
     */
    protected function beforeDestroy(Media $media): void {}
}
