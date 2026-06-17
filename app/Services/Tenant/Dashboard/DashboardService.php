<?php

declare(strict_types=1);

namespace App\Services\Tenant\Dashboard;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Http\Resources\Tenant\Inventory\InventoryMovementDataTable;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Models\Tenant\User;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class DashboardService extends BaseService
{
    private const LOW_STOCK_THRESHOLD = 3;

    /**
     * Headline counters for the dashboard tiles.
     *
     * @return array{revenue: float, todayUnits: int, records: int, stockUnits: int, stockValue: float, users: int, lowStock: int}
     */
    public function stats(): array
    {
        $revenue = (float) InventoryMovement::query()
            ->where('type', InventoryMovementTypeEnum::Sale)
            ->selectRaw('COALESCE(SUM(sale_price * quantity), 0) as aggregate')
            ->value('aggregate');

        $stockValue = (float) InventoryRecord::query()
            ->selectRaw('COALESCE(SUM(quantity * purchase_price_per_unit), 0) as aggregate')
            ->value('aggregate');

        return [
            'revenue' => round($revenue, 2),
            'todayUnits' => (int) InventoryMovement::query()
                ->where('type', InventoryMovementTypeEnum::Sale)
                ->whereDate('created_at', Carbon::today())
                ->sum('quantity'),
            'records' => InventoryRecord::query()->count(),
            'stockUnits' => (int) InventoryRecord::query()->sum('quantity'),
            'stockValue' => round($stockValue, 2),
            'users' => User::query()->count(),
            'lowStock' => InventoryRecord::query()
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', self::LOW_STOCK_THRESHOLD)
                ->count(),
        ];
    }

    /**
     * Paginated recent stock movements, reusing the Inventory movements
     * DataTable config (defaults to a small page size on the dashboard).
     *
     * @return array<string, mixed>
     */
    public function recentMovements(Request $request): array
    {
        $request->merge(['perPage' => $request->input('perPage', 5)]);

        return $this->fetchForDataTable(InventoryMovementDataTable::class, $request);
    }
}
