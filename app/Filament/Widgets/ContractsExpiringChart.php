<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use App\Models\ContractStatus;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ContractsExpiringChart extends ChartWidget
{
    protected static ?string $heading = 'Contratos por Vencer (Próximos 3 meses)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $activeStatusId = ContractStatus::where('name', 'Vigente')->first()?->id;

        if (!$activeStatusId) {
            return [
                'datasets' => [
                    [
                        'label' => 'Contratos por vencer',
                        'data' => [],
                        'backgroundColor' => '#f97316',
                    ],
                ],
                'labels' => [],
            ];
        }

        $today = Carbon::today();
        $in90Days = $today->copy()->addDays(90);

        // Consultar contratos que vencen en los próximos 90 días, agrupados por mes
        $data = Contract::where('contract_status_id', $activeStatusId)
            ->where('end_date', '>=', $today)
            ->where('end_date', '<=', $in90Days)
            ->select(DB::raw('MONTH(end_date) as month'), DB::raw('YEAR(end_date) as year'), DB::raw('count(*) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Preparar datos para el gráfico
        $labels = [];
        $chartData = [];

        $currentMonth = $today->month;
        $currentYear = $today->year;

        // Crear array de los próximos 3 meses
        for ($i = 0; $i < 3; $i++) {
            $date = $today->copy()->addMonths($i);
            $monthLabel = $date->locale('es')->monthName . ' ' . $date->year;
            $labels[] = $monthLabel;

            // Buscar si hay datos para este mes/año
            $monthData = $data->first(function ($item) use ($date) {
                return $item->month == $date->month && $item->year == $date->year;
            });

            $chartData[] = $monthData ? $monthData->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Contratos por vencer',
                    'data' => $chartData,
                    'backgroundColor' => '#f97316',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
