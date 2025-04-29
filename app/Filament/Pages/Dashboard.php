<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ContractsExpiringChart;
use App\Filament\Widgets\ExhumationsChart;
use App\Filament\Widgets\NicheStatsOverview;
use App\Filament\Widgets\PaymentsStatsOverview;
use App\Models\Contract;
use App\Models\Exhumation;
use App\Models\Niche;
use App\Models\Payment;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Carbon\Carbon;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = 'Panel de Control';

    protected static ?string $title = 'Panel de Control';

    protected static ?int $navigationSort = 1;

    protected function getHeaderWidgets(): array
    {
        return [
            NicheStatsOverview::class,
            PaymentsStatsOverview::class,
            ContractsExpiringChart::class,
            ExhumationsChart::class,
        ];
    }

    public function getRealTimeNotifications()
    {
        $today = Carbon::today();

        // Contratos próximos a vencer (30 días)
        $expiringContracts = Contract::whereHas('status', function ($query) {
            $query->where('name', 'Vigente');
        })
            ->where('end_date', '>=', $today)
            ->where('end_date', '<=', $today->copy()->addDays(30))
            ->with(['niche', 'deceased.person', 'responsible'])
            ->orderBy('end_date')
            ->take(5)
            ->get();

        // Pagos pendientes más antiguos
        $pendingPayments = Payment::whereHas('status', function ($query) {
            $query->where('name', 'No Pagado');
        })
            ->with(['contract.niche', 'contract.deceased.person', 'contract.responsible'])
            ->orderBy('issue_date')
            ->take(5)
            ->get();

        // Solicitudes de exhumación recientes
        $recentExhumations = Exhumation::whereHas('status', function ($query) {
            $query->where('name', 'Solicitada');
        })
            ->with(['contract.niche', 'contract.deceased.person', 'requester'])
            ->orderBy('request_date', 'desc')
            ->take(5)
            ->get();

        return [
            'expiringContracts' => $expiringContracts,
            'pendingPayments' => $pendingPayments,
            'recentExhumations' => $recentExhumations,
        ];
    }
}
