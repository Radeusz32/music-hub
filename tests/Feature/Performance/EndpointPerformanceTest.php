<?php

declare(strict_types=1);

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Enums\Tenant\RoleEnum;
use App\Models\Central\Admin;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Endpoint performance benchmark
|--------------------------------------------------------------------------
| Seeds a realistic dataset once, then measures wall time and the number of
| executed SQL queries for every heavy GET endpoint (tenant + central panel).
|
| Output:
|   - storage/app/performance/report.json  (raw samples)
|   - storage/app/performance/report.html  (self-contained Chart.js report)
|
| The query-count budget acts as a deterministic N+1 regression gate; the
| timings are informational and feed the chart only (wall time is too
| machine-dependent to assert on without flakiness).
*/

const PERF_ITERATIONS = 6;          // measured runs per endpoint (after 1 warmup)
const PERF_QUERY_BUDGET = 30;       // N+1 regression ceiling (current max ~13)

it('benchmarks application endpoints and writes a performance report', function (): void {
    /*
     | 1. Seed data. Admin is created on the central connection BEFORE tenancy
     |    is initialized so it does not leak into the tenant database.
     */
    $admin = Admin::factory()->create();

    $tenant = createBootedTenant();
    $owner = tenantUser(RoleEnum::Owner);

    InventoryRecord::factory()->count(120)->create();

    $records = InventoryRecord::query()->get();
    $recordCount = $records->count();

    foreach ($records as $record) {
        $saleCount = random_int(0, 4);

        for ($i = 0; $i < $saleCount; $i++) {
            InventoryMovement::factory()->create([
                'inventory_record_id' => $record->id,
                'type' => InventoryMovementTypeEnum::Sale,
                'sale_price' => fake()->randomFloat(2, 20, 400),
                'created_at' => Carbon::today()->subDays(random_int(0, 29)),
            ]);
        }
    }

    $saleMovementCount = InventoryMovement::query()
        ->where('type', InventoryMovementTypeEnum::Sale)
        ->count();

    /*
     | 2. Define the endpoints under test. Each closure issues a fully
     |    authenticated request; tenant requests are routed through the
     |    tenant domain so InitializeTenancyByDomain bootstraps the right DB.
     */
    /*
     | Central endpoints must run outside tenant context. createBootedTenant()
     | (and the tenant requests below) leave tenancy initialized in the test
     | process, which would route central models to the tenant database.
     */
    $central = function () use ($admin) {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        return $this->actingAs($admin, 'superadmin');
    };

    $endpoints = [
        ['label' => 'Tenant: Dashboard', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/dashboard'))],
        ['label' => 'Tenant: Inventory index', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/inventory/records'))],
        ['label' => 'Tenant: Movements', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/inventory/movements'))],
        ['label' => 'Tenant: Sales', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/inventory/sales'))],
        ['label' => 'Analytics: Overview', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/analytics/overview'))],
        ['label' => 'Analytics: Sales', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/analytics/sales'))],
        ['label' => 'Analytics: Artists', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/analytics/artists'))],
        ['label' => 'Analytics: Reports', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/analytics/reports'))],
        ['label' => 'Tenant: Users index', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/users'))],
        ['label' => 'Tenant: Profile settings', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/settings/profile'))],
        ['label' => 'Tenant: Organization settings', 'group' => 'Tenant', 'request' => fn () => $this->actingAs($owner)->get(tenantUrl($tenant, '/settings/organization'))],

        ['label' => 'Central: Dashboard', 'group' => 'Central', 'request' => fn () => $central()->get('http://localhost/panel/central/superadmin')],
        ['label' => 'Central: Tenants index', 'group' => 'Central', 'request' => fn () => $central()->get('http://localhost/panel/central/superadmin/tenants')],
        ['label' => 'Central: Invitations index', 'group' => 'Central', 'request' => fn () => $central()->get('http://localhost/panel/central/superadmin/invitations')],
        ['label' => 'Central: Features index', 'group' => 'Central', 'request' => fn () => $central()->get('http://localhost/panel/central/superadmin/features')],
    ];

    /*
     | 3. Measure. A single DB listener counts every query for the in-flight
     |    request; the counter is reset around each measured call.
     */
    $queryCount = 0;
    DB::listen(function () use (&$queryCount): void {
        $queryCount++;
    });

    $results = [];

    foreach ($endpoints as $endpoint) {
        $request = $endpoint['request'];

        $request()->assertOk(); // warm-up (route/view resolution, opcache, etc.)

        $timings = [];
        $queries = 0;

        for ($i = 0; $i < PERF_ITERATIONS; $i++) {
            $queryCount = 0;
            $start = hrtime(true);

            $response = $request();

            $elapsedMs = (hrtime(true) - $start) / 1_000_000;

            $response->assertOk();

            $timings[] = $elapsedMs;
            $queries = $queryCount;
        }

        sort($timings);
        $count = count($timings);

        $results[] = [
            'label' => $endpoint['label'],
            'group' => $endpoint['group'],
            'queries' => $queries,
            'avgMs' => round(array_sum($timings) / $count, 2),
            'minMs' => round($timings[0], 2),
            'maxMs' => round($timings[$count - 1], 2),
            'p95Ms' => round($timings[(int) floor($count * 0.95) - 1] ?? $timings[$count - 1], 2),
        ];
    }

    /*
     | 4. Persist the report (raw JSON + standalone HTML chart).
     */
    $dir = storage_path('app/performance');
    File::ensureDirectoryExists($dir);

    $payload = [
        'generatedAt' => now()->toIso8601String(),
        'iterations' => PERF_ITERATIONS,
        'dataset' => [
            'inventoryRecords' => $recordCount,
            'saleMovements' => $saleMovementCount,
        ],
        'results' => $results,
    ];

    File::put($dir.'/report.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    File::put($dir.'/report.html', performanceReportHtml($payload));

    /*
     | 5. Deterministic regression gate: every endpoint must respond and stay
     |    well under the catastrophic-N+1 ceiling. Tighten PERF_QUERY_BUDGET
     |    (or add per-endpoint budgets) once you have reviewed the report.
     */
    foreach ($results as $result) {
        expect($result['queries'])
            ->toBeLessThan(
                PERF_QUERY_BUDGET,
                "{$result['label']} executed {$result['queries']} queries (budget ".PERF_QUERY_BUDGET.')'
            );
    }

    expect($results)->toHaveCount(count($endpoints));
});

/**
 * Build a self-contained HTML performance report (Chart.js via CDN).
 *
 * @param  array<string, mixed>  $payload
 */
function performanceReportHtml(array $payload): string
{
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

    return <<<HTML
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MusicHub — raport wydajności</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <style>
            body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; background: #0f172a; color: #e2e8f0; }
            .wrap { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }
            h1 { font-size: 1.5rem; margin-bottom: .25rem; }
            .meta { color: #94a3b8; font-size: .875rem; margin-bottom: 2rem; }
            .card { background: #1e293b; border: 1px solid #334155; border-radius: .75rem; padding: 1.5rem; margin-bottom: 2rem; }
            .card h2 { font-size: 1rem; margin: 0 0 1rem; color: #cbd5e1; }
            table { width: 100%; border-collapse: collapse; font-size: .875rem; }
            th, td { text-align: left; padding: .5rem .75rem; border-bottom: 1px solid #334155; }
            th { color: #94a3b8; font-weight: 600; }
            td.num { text-align: right; font-variant-numeric: tabular-nums; }
        </style>
    </head>
    <body>
        <div class="wrap">
            <h1>MusicHub — raport wydajności</h1>
            <div class="meta" id="meta"></div>

            <div class="card">
                <h2>Średni czas odpowiedzi (ms)</h2>
                <canvas id="timeChart" height="140"></canvas>
            </div>

            <div class="card">
                <h2>Liczba zapytań SQL na żądanie</h2>
                <canvas id="queryChart" height="140"></canvas>
            </div>

            <div class="card">
                <h2>Szczegóły</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Endpoint</th><th class="num">Zapytania</th>
                            <th class="num">avg</th><th class="num">p95</th>
                            <th class="num">min</th><th class="num">max</th>
                        </tr>
                    </thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
        </div>

        <script>
            const data = {$json};
            const labels = data.results.map(r => r.label);
            const colors = data.results.map(r => r.group === 'Central' ? '#f59e0b' : '#38bdf8');

            document.getElementById('meta').textContent =
                'Wygenerowano: ' + new Date(data.generatedAt).toLocaleString('pl-PL')
                + ' · iteracji: ' + data.iterations
                + ' · rekordów: ' + data.dataset.inventoryRecords
                + ' · sprzedaży: ' + data.dataset.saleMovements;

            const baseOpts = { indexAxis: 'y', responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { ticks: { color: '#94a3b8' }, grid: { color: '#334155' } },
                          y: { ticks: { color: '#cbd5e1' }, grid: { display: false } } } };

            new Chart(document.getElementById('timeChart'), {
                type: 'bar',
                data: { labels, datasets: [{ data: data.results.map(r => r.avgMs), backgroundColor: colors }] },
                options: baseOpts,
            });

            new Chart(document.getElementById('queryChart'), {
                type: 'bar',
                data: { labels, datasets: [{ data: data.results.map(r => r.queries), backgroundColor: colors }] },
                options: baseOpts,
            });

            document.getElementById('rows').innerHTML = data.results.map(r =>
                '<tr><td>' + r.label + '</td>'
                + '<td class="num">' + r.queries + '</td>'
                + '<td class="num">' + r.avgMs + '</td>'
                + '<td class="num">' + r.p95Ms + '</td>'
                + '<td class="num">' + r.minMs + '</td>'
                + '<td class="num">' + r.maxMs + '</td></tr>'
            ).join('');
        </script>
    </body>
    </html>
    HTML;
}
