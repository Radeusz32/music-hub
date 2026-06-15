<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Inventory\StoreInventorySaleRequest;
use App\Models\Tenant\InventoryRecord;
use App\Services\Tenant\Inventory\InventorySaleService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class InventorySaleController extends Controller
{
    public function __construct(private readonly InventorySaleService $service) {}

    public function index(): Response
    {
        return Inertia::render('Tenant/Inventory/Sales/Index', [
            'records' => $this->service->sellableRecords(),
            'recentSales' => $this->service->recentSales(),
            'todayStats' => $this->service->todayStats(),
        ]);
    }

    public function store(StoreInventorySaleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $record = InventoryRecord::query()->findOrFail($data['inventory_record_id']);

        $this->service->sell(
            $record,
            (int) $data['quantity'],
            (string) $data['sale_price'],
            $data['note'] ?? null,
            auth()->id(),
        );

        return redirect()
            ->route('tenant.inventory.sales.index')
            ->with('success', 'Sprzedaż została zapisana.');
    }
}
