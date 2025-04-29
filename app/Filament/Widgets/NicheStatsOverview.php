<?php

namespace App\Filament\Widgets;

use App\Models\Niche;
use App\Models\NicheStatus;
use App\Models\Contract;
use App\Models\ContractStatus;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NicheStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Calcular total de nichos y sus estados
        $totalNiches = Niche::count();
        $availableNiches = Niche::whereHas('status', function ($query) {
            $query->where('name', 'Disponible');
        })->count();
        $occupiedNiches = Niche::whereHas('status', function ($query) {
            $query->where('name', 'Ocupado');
        })->count();
        $exhumationNiches = Niche::whereHas('status', function ($query) {
            $query->where('name', 'Proceso de Exhumación');
        })->count();

        // Calcular porcentajes
        $availablePercentage = $totalNiches > 0 ? round(($availableNiches / $totalNiches) * 100, 1) : 0;
        $occupiedPercentage = $totalNiches > 0 ? round(($occupiedNiches / $totalNiches) * 100, 1) : 0;

        // Calcular contratos próximos a vencer
        $today = Carbon::today();
        $in30Days = $today->copy()->addDays(30);
        $in90Days = $today->copy()->addDays(90);

        $expiringIn30Days = Contract::whereHas('status', function ($query) {
            $query->where('name', 'Vigente');
        })
            ->where('end_date', '>=', $today)
            ->where('end_date', '<=', $in30Days)
            ->count();

        $expiringIn90Days = Contract::whereHas('status', function ($query) {
            $query->where('name', 'Vigente');
        })
            ->where('end_date', '>=', $today)
            ->where('end_date', '<=', $in90Days)
            ->count();

        // Calcular contratos en período de gracia
        $inGrace = Contract::whereHas('status', function ($query) {
            $query->where('name', 'En Gracia');
        })->count();

        return [
            Stat::make('Nichos Disponibles', $availableNiches)
                ->description("$availablePercentage% del total")
                ->color('success')
                ->chart([
                    $availablePercentage,
                    $occupiedPercentage,
                    100 - $availablePercentage - $occupiedPercentage
                ]),

            Stat::make('Nichos Ocupados', $occupiedNiches)
                ->description("$occupiedPercentage% del total")
                ->color('warning')
                ->chart([
                    $occupiedPercentage,
                    $availablePercentage,
                    100 - $availablePercentage - $occupiedPercentage
                ]),

            Stat::make('Contratos Próximos a Vencer', $expiringIn30Days)
                ->description("En los próximos 30 días")
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringIn30Days > 0 ? 'danger' : 'success')
                ->chart([
                    $expiringIn30Days,
                    $expiringIn90Days - $expiringIn30Days,
                    $inGrace
                ]),
        ];
    }
}
