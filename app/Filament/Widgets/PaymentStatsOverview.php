<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\PaymentStatus;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PaymentsStatsOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        // Obtener estado de pagos
        $paidStatusId = PaymentStatus::where('name', 'Pagado')->first()?->id;
        $unpaidStatusId = PaymentStatus::where('name', 'No Pagado')->first()?->id;

        if (!$paidStatusId || !$unpaidStatusId) {
            return [];
        }

        // Calcular pagos pendientes y totales
        $pendingPayments = Payment::where('payment_status_id', $unpaidStatusId)->count();
        $pendingAmount = Payment::where('payment_status_id', $unpaidStatusId)->sum('amount');

        // Calcular ingresos del año actual
        $currentYear = Carbon::now()->year;
        $incomeCurrentYear = Payment::where('payment_status_id', $paidStatusId)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');

        // Calcular ingresos del mes actual
        $currentMonth = Carbon::now()->month;
        $incomeCurrentMonth = Payment::where('payment_status_id', $paidStatusId)
            ->whereYear('payment_date', $currentYear)
            ->whereMonth('payment_date', $currentMonth)
            ->sum('amount');

        // Calcular ingresos por mes (últimos 6 meses)
        $monthlyIncome = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $income = Payment::where('payment_status_id', $paidStatusId)
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');

            $monthlyIncome[] = $income;
        }

        return [
            Stat::make('Pagos Pendientes', $pendingPayments)
                ->description('Total: Q' . number_format($pendingAmount, 2))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($pendingPayments > 0 ? 'danger' : 'success')
                ->chart($pendingPayments > 0 ? [100, 0] : [0, 100]),

            Stat::make('Ingresos ' . $currentYear, 'Q' . number_format($incomeCurrentYear, 2))
                ->description('Del año actual')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->chart($monthlyIncome),

            Stat::make('Ingresos ' . Carbon::now()->locale('es')->monthName, 'Q' . number_format($incomeCurrentMonth, 2))
                ->description('Del mes actual')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
