<?php

declare(strict_types=1);

namespace App\MediaLibrary;

use DateTimeInterface;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

final class TenantUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        if (tenancy()->initialized) {
            $tenantId = tenant('id');
            $diskRoot = mb_rtrim(config('filesystems.disks.public.root'), '/');
            $fullPath = $this->media->getPath();
            $relativePath = mb_ltrim(str_replace($diskRoot, '', $fullPath), '/');

            return url("/storage/tenant{$tenantId}/{$relativePath}");
        }

        return parent::getUrl();
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        return $this->getUrl();
    }
}
