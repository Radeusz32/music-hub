<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Inventory;

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Inventory\BulkDestroyInventoryRecordsRequest;
use App\Http\Requests\Tenant\Inventory\ImportInventoryRecordsRequest;
use App\Http\Requests\Tenant\Inventory\StoreInventoryRecordRequest;
use App\Http\Requests\Tenant\Inventory\UpdateInventoryRecordRequest;
use App\Http\Requests\Tenant\Inventory\UploadCoverRequest;
use App\Models\Tenant\InventoryRecord;
use App\Services\Tenant\Inventory\InventoryRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class InventoryRecordController extends Controller
{
    public function __construct(private readonly InventoryRecordService $inventoryRecordService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Tenant/Inventory/Index', [
            'records' => $this->inventoryRecordService->index($request),
            'formatOptions' => DiscFormatEnum::options(),
            'conditionOptions' => DiscConditionEnum::options(),
        ]);
    }

    public function store(StoreInventoryRecordRequest $request): RedirectResponse
    {
        $this->inventoryRecordService->create(
            array_merge($request->validated(), ['user_id' => auth()->id()])
        );

        return redirect()
            ->route('tenant.inventory.records.index')
            ->with('success', 'Płyta została dodana do magazynu.');
    }

    public function show(InventoryRecord $inventoryRecord): Response
    {
        return Inertia::render('Tenant/Inventory/Show', [
            'record' => $this->inventoryRecordService->show($inventoryRecord),
            'formatOptions' => DiscFormatEnum::options(),
            'conditionOptions' => DiscConditionEnum::options(),
            'movementTypeOptions' => InventoryMovementTypeEnum::options(),
        ]);
    }

    public function update(UpdateInventoryRecordRequest $request, InventoryRecord $inventoryRecord): RedirectResponse
    {
        $this->inventoryRecordService->update($inventoryRecord, $request->validated());

        return redirect()
            ->route('tenant.inventory.records.index')
            ->with('success', 'Dane płyty zostały zaktualizowane.');
    }

    public function destroy(InventoryRecord $inventoryRecord): RedirectResponse
    {
        $this->inventoryRecordService->delete($inventoryRecord);

        return redirect()
            ->route('tenant.inventory.records.index')
            ->with('success', 'Płyta została usunięta z magazynu.');
    }

    public function bulkDestroy(BulkDestroyInventoryRecordsRequest $request): RedirectResponse
    {
        $deleted = $this->inventoryRecordService->bulkDelete($request->validated()['ids']);

        return redirect()
            ->route('tenant.inventory.records.index')
            ->with('success', "Usunięto zaznaczone płyty ({$deleted}).");
    }

    public function importExcel(ImportInventoryRecordsRequest $request): RedirectResponse
    {
        $this->inventoryRecordService->importExcel($request->file('file'));

        return redirect()
            ->route('tenant.inventory.records.index')
            ->with('success', 'Import zakończony pomyślnie. Płyty zostały dodane do magazynu.');
    }

    public function exportExcelTemplate(): BinaryFileResponse
    {
        return $this->inventoryRecordService->exportExcelTemplate();
    }

    public function uploadCover(UploadCoverRequest $request, InventoryRecord $inventoryRecord): RedirectResponse
    {
        $this->inventoryRecordService->uploadCover($inventoryRecord, $request->file('cover'));

        return back()->with('success', 'Zdjęcie okładki zostało zaktualizowane.');
    }

    public function destroyCover(InventoryRecord $inventoryRecord): RedirectResponse
    {
        $this->inventoryRecordService->destroyCover($inventoryRecord);

        return back()->with('success', 'Zdjęcie okładki zostało usunięte.');
    }
}
