<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Inventory;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Inventory\BulkDestroyInventoryMovementsRequest;
use App\Http\Requests\Tenant\Inventory\StoreInventoryMovementRequest;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Services\Tenant\Inventory\InventoryRecordMovementsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class InventoryMovementController extends Controller
{
    public function __construct(private readonly InventoryRecordMovementsService $movements) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Tenant/Inventory/Movements/Index', [
            'movements' => $this->movements->index($request),
            'typeOptions' => InventoryMovementTypeEnum::options(),
            'manualTypeOptions' => InventoryMovementTypeEnum::manualOptions(),
            'recordOptions' => $this->movements->recordOptions(),
        ]);
    }

    public function store(StoreInventoryMovementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $record = InventoryRecord::query()->findOrFail($data['inventory_record_id']);

        $this->movements->record(
            $record,
            InventoryMovementTypeEnum::from($data['type']),
            (int) $data['quantity'],
            $data['note'] ?? null,
            auth()->id(),
        );

        return redirect()
            ->route('tenant.inventory.movements.index')
            ->with('success', 'Ruch magazynowy został zapisany.');
    }

    public function destroy(InventoryMovement $inventoryMovement): RedirectResponse
    {
        $this->movements->delete($inventoryMovement);

        return redirect()
            ->route('tenant.inventory.movements.index')
            ->with('success', 'Ruch magazynowy został usunięty, a stan magazynowy skorygowany.');
    }

    public function bulkDestroy(BulkDestroyInventoryMovementsRequest $request): RedirectResponse
    {
        $deleted = $this->movements->bulkDelete($request->validated()['ids']);

        return redirect()
            ->route('tenant.inventory.movements.index')
            ->with('success', "Usunięto zaznaczone ruchy ({$deleted}).");
    }
}
