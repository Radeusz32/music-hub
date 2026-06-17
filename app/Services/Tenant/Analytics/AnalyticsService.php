<?php

declare(strict_types=1);

namespace App\Services\Tenant\Analytics;

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class AnalyticsService
{
    /** @var list<string> */
    private const MONTHS_SHORT_PL = [
        'Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze',
        'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru',
    ];

    private const REVENUE_EXPR = 'COALESCE(SUM(inventory_movements.sale_price * inventory_movements.quantity), 0)';

    private const LOW_STOCK_THRESHOLD = 3;

    /* ──────────────────────────────────────────────────────────────────────
     | Page payloads
     ───────────────────────────────────────────────────────────────────── */

    /** @return array<string, mixed> */
    public function overview(): array
    {
        return [
            'sales' => $this->salesTotals(),
            'stock' => $this->stockTotals(),
            'revenueTrend' => $this->dailyRevenue(30),
            'salesByFormat' => $this->salesByFormat(),
            'topArtists' => $this->topArtistsBySales(5),
        ];
    }

    /** @return array<string, mixed> */
    public function sales(): array
    {
        return [
            'kpis' => $this->salesKpisWithDelta(),
            'monthlyRevenue' => $this->monthlyRevenue(12),
            'dailyRevenue' => $this->dailyRevenue(30),
            'byFormat' => $this->salesByFormat(),
            'recent' => $this->recentSales(10),
        ];
    }

    /** @return array<string, mixed> */
    public function artists(): array
    {
        return [
            'byRevenue' => $this->topArtistsBySales(15),
            'byStock' => $this->topArtistsByStock(15),
            'distinctArtists' => (int) InventoryRecord::query()->distinct()->count('artist'),
        ];
    }

    /** @return array<string, mixed> */
    public function reports(): array
    {
        return [
            'byFormat' => $this->reportByFormat(),
            'byCondition' => $this->reportByCondition(),
            'topGenres' => $this->topGenres(10),
            'monthlyRevenue' => $this->monthlyRevenue(12),
        ];
    }

    /* ──────────────────────────────────────────────────────────────────────
     | Aggregations
     ───────────────────────────────────────────────────────────────────── */

    /** @return Builder<InventoryMovement> */
    private function saleQuery(): Builder
    {
        return InventoryMovement::query()
            ->where('inventory_movements.type', InventoryMovementTypeEnum::Sale->value);
    }

    /** @return array{revenue: float, units: int, transactions: int, avg: float} */
    private function salesTotals(): array
    {
        $row = $this->saleQuery()
            ->selectRaw(self::REVENUE_EXPR.' as revenue, COALESCE(SUM(inventory_movements.quantity), 0) as units, COUNT(*) as transactions')
            ->first();

        $revenue = (float) ($row->revenue ?? 0);
        $transactions = (int) ($row->transactions ?? 0);

        return [
            'revenue' => round($revenue, 2),
            'units' => (int) ($row->units ?? 0),
            'transactions' => $transactions,
            'avg' => $transactions > 0 ? round($revenue / $transactions, 2) : 0.0,
        ];
    }

    /** @return array{units: int, value: float, titles: int, lowStock: int, outOfStock: int} */
    private function stockTotals(): array
    {
        $row = InventoryRecord::query()
            ->selectRaw('COALESCE(SUM(quantity), 0) as units, COALESCE(SUM(quantity * purchase_price_per_unit), 0) as value, COUNT(*) as titles')
            ->first();

        return [
            'units' => (int) ($row->units ?? 0),
            'value' => round((float) ($row->value ?? 0), 2),
            'titles' => (int) ($row->titles ?? 0),
            'lowStock' => InventoryRecord::query()
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', self::LOW_STOCK_THRESHOLD)
                ->count(),
            'outOfStock' => InventoryRecord::query()->where('quantity', 0)->count(),
        ];
    }

    /**
     * Daily revenue for the last $days days, with gaps filled with zeros.
     *
     * @return list<array{date: string, label: string, revenue: float, units: int}>
     */
    private function dailyRevenue(int $days): array
    {
        $from = Carbon::today()->subDays($days - 1);

        $rows = $this->saleQuery()
            ->where('inventory_movements.created_at', '>=', $from)
            ->selectRaw('DATE(inventory_movements.created_at) as bucket, '.self::REVENUE_EXPR.' as revenue, COALESCE(SUM(inventory_movements.quantity), 0) as units')
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            $row = $rows->get($date->toDateString());

            $series[] = [
                'date' => $date->toDateString(),
                'label' => $date->format('d.m'),
                'revenue' => round((float) ($row->revenue ?? 0), 2),
                'units' => (int) ($row->units ?? 0),
            ];
        }

        return $series;
    }

    /**
     * Revenue for the last $months calendar months, gaps filled with zeros.
     *
     * @return list<array{month: string, label: string, revenue: float, units: int}>
     */
    private function monthlyRevenue(int $months): array
    {
        $from = Carbon::today()->startOfMonth()->subMonths($months - 1);

        $rows = $this->saleQuery()
            ->where('inventory_movements.created_at', '>=', $from)
            ->selectRaw("DATE_FORMAT(inventory_movements.created_at, '%Y-%m') as bucket, ".self::REVENUE_EXPR.' as revenue, COALESCE(SUM(inventory_movements.quantity), 0) as units')
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $series = [];
        for ($i = 0; $i < $months; $i++) {
            $date = $from->copy()->addMonths($i);
            $row = $rows->get($date->format('Y-m'));

            $series[] = [
                'month' => $date->format('Y-m'),
                'label' => self::MONTHS_SHORT_PL[(int) $date->format('n') - 1].' '.$date->format('Y'),
                'revenue' => round((float) ($row->revenue ?? 0), 2),
                'units' => (int) ($row->units ?? 0),
            ];
        }

        return $series;
    }

    /**
     * Sales aggregated by disc format.
     *
     * @return list<array{format: string, label: string, color: string, units: int, revenue: float}>
     */
    private function salesByFormat(): array
    {
        return $this->saleQuery()
            ->join('inventory_records', 'inventory_records.id', '=', 'inventory_movements.inventory_record_id')
            ->whereNull('inventory_records.deleted_at')
            ->groupBy('inventory_records.format')
            ->selectRaw('inventory_records.format as format, COALESCE(SUM(inventory_movements.quantity), 0) as units, '.self::REVENUE_EXPR.' as revenue')
            ->orderByDesc('revenue')
            ->get()
            ->map(function (InventoryMovement $row): array {
                $format = DiscFormatEnum::from((string) $row->format);

                return [
                    'format' => $format->value,
                    'label' => $format->label(),
                    'color' => $format->color(),
                    'units' => (int) $row->units,
                    'revenue' => round((float) $row->revenue, 2),
                ];
            })
            ->all();
    }

    /**
     * Best-selling artists by revenue.
     *
     * @return list<array{artist: string, units: int, revenue: float, titles: int}>
     */
    private function topArtistsBySales(int $limit): array
    {
        return $this->saleQuery()
            ->join('inventory_records', 'inventory_records.id', '=', 'inventory_movements.inventory_record_id')
            ->whereNull('inventory_records.deleted_at')
            ->groupBy('inventory_records.artist')
            ->selectRaw('inventory_records.artist as artist, COALESCE(SUM(inventory_movements.quantity), 0) as units, '.self::REVENUE_EXPR.' as revenue, COUNT(DISTINCT inventory_records.id) as titles')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn (InventoryMovement $row): array => [
                'artist' => (string) $row->artist,
                'units' => (int) $row->units,
                'revenue' => round((float) $row->revenue, 2),
                'titles' => (int) $row->titles,
            ])
            ->all();
    }

    /**
     * Artists with the most stock on hand.
     *
     * @return list<array{artist: string, titles: int, units: int, value: float}>
     */
    private function topArtistsByStock(int $limit): array
    {
        return InventoryRecord::query()
            ->groupBy('artist')
            ->selectRaw('artist, COUNT(*) as titles, COALESCE(SUM(quantity), 0) as units, COALESCE(SUM(quantity * purchase_price_per_unit), 0) as value')
            ->orderByDesc('units')
            ->limit($limit)
            ->get()
            ->map(fn (InventoryRecord $row): array => [
                'artist' => (string) $row->artist,
                'titles' => (int) $row->titles,
                'units' => (int) $row->units,
                'value' => round((float) $row->value, 2),
            ])
            ->all();
    }

    /**
     * Latest sales for the side feed.
     *
     * @return list<array{id: int, name: string|null, artist: string|null, quantity: int, sale_price: float, total: float, created_at: string|null}>
     */
    private function recentSales(int $limit): array
    {
        return $this->saleQuery()
            ->with('inventoryRecord:id,name,artist')
            ->latest('inventory_movements.created_at')
            ->limit($limit)
            ->get()
            ->map(fn (InventoryMovement $movement): array => [
                'id' => $movement->id,
                'name' => $movement->inventoryRecord?->name,
                'artist' => $movement->inventoryRecord?->artist,
                'quantity' => $movement->quantity,
                'sale_price' => round((float) $movement->sale_price, 2),
                'total' => round((float) $movement->sale_price * $movement->quantity, 2),
                'created_at' => $movement->created_at?->toISOString(),
            ])
            ->all();
    }

    /** @return array{revenue: array{value: float, delta: float|null}, units: array{value: int, delta: float|null}, transactions: array{value: int, delta: float|null}} */
    private function salesKpisWithDelta(): array
    {
        $today = Carbon::today();
        $currentFrom = $today->copy()->subDays(29);
        $previousFrom = $currentFrom->copy()->subDays(30);

        $current = $this->aggregateBetween($currentFrom, $today->copy()->endOfDay());
        $previous = $this->aggregateBetween($previousFrom, $currentFrom->copy()->subDay()->endOfDay());

        return [
            'revenue' => ['value' => $current['revenue'], 'delta' => $this->delta($current['revenue'], $previous['revenue'])],
            'units' => ['value' => $current['units'], 'delta' => $this->delta($current['units'], $previous['units'])],
            'transactions' => ['value' => $current['transactions'], 'delta' => $this->delta($current['transactions'], $previous['transactions'])],
        ];
    }

    /** @return array{revenue: float, units: int, transactions: int} */
    private function aggregateBetween(Carbon $from, Carbon $to): array
    {
        $row = $this->saleQuery()
            ->whereBetween('inventory_movements.created_at', [$from, $to])
            ->selectRaw(self::REVENUE_EXPR.' as revenue, COALESCE(SUM(inventory_movements.quantity), 0) as units, COUNT(*) as transactions')
            ->first();

        return [
            'revenue' => round((float) ($row->revenue ?? 0), 2),
            'units' => (int) ($row->units ?? 0),
            'transactions' => (int) ($row->transactions ?? 0),
        ];
    }

    private function delta(float|int $current, float|int $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Combined stock + sales summary per disc format.
     *
     * @return list<array{format: string, label: string, color: string, titles: int, stockUnits: int, stockValue: float, soldUnits: int, revenue: float}>
     */
    private function reportByFormat(): array
    {
        $stock = InventoryRecord::query()
            ->groupBy('format')
            ->selectRaw('format, COUNT(*) as titles, COALESCE(SUM(quantity), 0) as stock_units, COALESCE(SUM(quantity * purchase_price_per_unit), 0) as stock_value')
            ->toBase()
            ->get()
            ->keyBy('format');

        $sold = collect($this->salesByFormat())->keyBy('format');

        $report = [];
        foreach (DiscFormatEnum::cases() as $format) {
            $stockRow = $stock->get($format->value);
            $soldRow = $sold->get($format->value);

            if ($stockRow === null && $soldRow === null) {
                continue;
            }

            $report[] = [
                'format' => $format->value,
                'label' => $format->label(),
                'color' => $format->color(),
                'titles' => (int) ($stockRow->titles ?? 0),
                'stockUnits' => (int) ($stockRow->stock_units ?? 0),
                'stockValue' => round((float) ($stockRow->stock_value ?? 0), 2),
                'soldUnits' => (int) ($soldRow['units'] ?? 0),
                'revenue' => round((float) ($soldRow['revenue'] ?? 0), 2),
            ];
        }

        return $report;
    }

    /**
     * Stock distribution by disc condition.
     *
     * @return list<array{condition: string, label: string, color: string, titles: int, units: int}>
     */
    private function reportByCondition(): array
    {
        $stock = InventoryRecord::query()
            ->groupBy('condition')
            ->selectRaw('`condition`, COUNT(*) as titles, COALESCE(SUM(quantity), 0) as units')
            ->toBase()
            ->get()
            ->keyBy('condition');

        $report = [];
        foreach (DiscConditionEnum::cases() as $condition) {
            $row = $stock->get($condition->value);

            if ($row === null) {
                continue;
            }

            $report[] = [
                'condition' => $condition->value,
                'label' => $condition->label(),
                'color' => $condition->color(),
                'titles' => (int) $row->titles,
                'units' => (int) $row->units,
            ];
        }

        return $report;
    }

    /**
     * Genres with the most titles in stock.
     *
     * @return list<array{genre: string, titles: int, units: int}>
     */
    private function topGenres(int $limit): array
    {
        return InventoryRecord::query()
            ->groupBy('genre')
            ->selectRaw('genre, COUNT(*) as titles, COALESCE(SUM(quantity), 0) as units')
            ->orderByDesc('titles')
            ->limit($limit)
            ->get()
            ->map(fn (InventoryRecord $row): array => [
                'genre' => (string) $row->genre,
                'titles' => (int) $row->titles,
                'units' => (int) $row->units,
            ])
            ->all();
    }
}
