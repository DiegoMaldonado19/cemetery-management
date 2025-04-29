<?php

namespace App\Filament\Widgets;

use App\Models\Exhumation;
use App\Models\ExhumationStatus;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ExhumationsChart extends ChartWidget
{
    protected static ?string $heading = 'Exhumaciones por Estado';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        // Obtener estadísticas de exhumaciones por estado
        $exhumationStats = ExhumationStatus::leftJoin('exhumations', 'exhumation_statuses.id', '=', 'exhumations.exhumation_status_id')
            ->select('exhumation_statuses.name', DB::raw('count(exhumations.id) as total'))
            ->groupBy('exhumation_statuses.id', 'exhumation_statuses.name')
            ->get();

        // Preparar datos para el gráfico
        $labels = $exhumationStats->pluck('name')->toArray();
        $data = $exhumationStats->pluck('total')->toArray();

        // Definir colores para cada estado
        $backgroundColors = [
            'Solicitada' => '#f59e0b',   // Amber-500
            'Aprobada' => '#10b981',     // Emerald-500
            'Rechazada' => '#ef4444',    // Red-500
            'Completada' => '#6b7280',   // Gray-500
        ];

        $colors = $exhumationStats->map(function ($item) use ($backgroundColors) {
            return $backgroundColors[$item->name] ?? '#6b7280'; // Default to gray if not found
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Exhumaciones',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
