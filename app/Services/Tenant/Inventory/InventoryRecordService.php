<?php

declare(strict_types=1);

namespace App\Services\Tenant\Inventory;

use App\Exports\Tenant\Inventory\InventoryRecordExport;
use App\Http\Resources\Tenant\Inventory\InventoryRecordDataTable;
use App\Imports\Tenant\Inventory\InventoryRecordImport;
use App\Models\Tenant\InventoryRecord;
use App\Services\BaseService;
use App\Traits\ManagesFiles;
use App\Transformers\Tenant\Inventory\InventoryRecordTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class InventoryRecordService extends BaseService
{
    use ManagesFiles;

    public function __construct(private readonly InventoryRecordMovements $movements) {}

    /**
     * @return array<string, mixed>
     */
    public function index(Request $request): array
    {
        return $this->fetchForDataTable(InventoryRecordDataTable::class, $request);
    }

    /**
     * @return array<string, mixed>
     */
    public function show(InventoryRecord $inventoryRecord): array
    {
        $inventoryRecord->loadMissing(InventoryRecordTransformer::eagerLoads());
        $inventoryRecord->load([
            'movements' => fn ($query) => $query
                ->latest()
                ->with(['user', 'user.roles:id,name']),
        ]);

        return (new InventoryRecordTransformer())->toArray($inventoryRecord, request());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): InventoryRecord
    {
        $record = InventoryRecord::query()->create($data);

        $this->movements->recordInitial($record, $data['user_id'] ?? auth()->id());

        return $record;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(InventoryRecord $inventoryRecord, array $data): InventoryRecord
    {
        $oldQuantity = $inventoryRecord->quantity;

        $inventoryRecord->update($data);

        if (array_key_exists('quantity', $data)) {
            $this->movements->recordQuantityChange(
                $inventoryRecord,
                $oldQuantity,
                (int) $inventoryRecord->quantity,
                auth()->id(),
            );
        }

        return $inventoryRecord->fresh();
    }

    public function delete(InventoryRecord $inventoryRecord): void
    {
        $inventoryRecord->delete();
    }

    /**
     * Delete multiple records by id. Returns the number of records deleted.
     *
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return InventoryRecord::query()->whereIn('id', $ids)->delete();
    }

    public function importExcel(UploadedFile $file): void
    {
        try {
            Excel::import(new class implements WithMultipleSheets
            {
                public function sheets(): array
                {
                    return [0 => new InventoryRecordImport()];
                }
            }, $file);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failure = $e->failures()[0];

            throw ValidationException::withMessages([
                'file' => "Wiersz {$failure->row()}: {$failure->errors()[0]}",
            ]);
        }
    }

    public function exportExcelTemplate(): BinaryFileResponse
    {
        return Excel::download(new InventoryRecordExport(), 'szablon-plyt.xlsx');
    }

    public function uploadCover(InventoryRecord $inventoryRecord, UploadedFile $file): Media
    {
        return $this->uploadFile($inventoryRecord, $file, 'cover');
    }

    public function destroyCover(InventoryRecord $inventoryRecord): void
    {
        $this->destroyFiles($inventoryRecord, 'cover');
    }
}
