<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <x-filament::section>
            <x-slot name="heading">Contratos Próximos a Vencer (30 días)</x-slot>

            @php
                $notifications = $this->getRealTimeNotifications();
                $expiringContracts = $notifications['expiringContracts'];
            @endphp

            @if($expiringContracts->isEmpty())
                <p class="text-gray-500 text-center py-4">No hay contratos próximos a vencer en los próximos 30 días.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-2">Nicho</th>
                                <th class="px-4 py-2">Fallecido</th>
                                <th class="px-4 py-2">Responsable</th>
                                <th class="px-4 py-2">Vence</th>
                                <th class="px-4 py-2">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringContracts as $contract)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium">{{ $contract->niche->code }}</td>
                                    <td class="px-4 py-2">{{ $contract->deceased->person->first_name }} {{ $contract->deceased->person->last_name }}</td>
                                    <td class="px-4 py-2">{{ $contract->responsible->first_name }} {{ $contract->responsible->last_name }}</td>
                                    <td class="px-4 py-2">
                                        <span class="text-red-600 font-semibold">{{ $contract->end_date->format('d/m/Y') }}</span>
                                        <span class="text-xs text-gray-500 block">En {{ $contract->end_date->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.admin.resources.contracts.view', $contract) }}" class="text-primary-600 hover:text-primary-900">Ver detalles</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <a href="{{ route('filament.admin.resources.contracts.index', ['tableFilters[expiring_soon][isActive]' => 'true']) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                        Ver todos los contratos próximos a vencer →
                    </a>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Pagos Pendientes</x-slot>

            @php
                $pendingPayments = $notifications['pendingPayments'];
            @endphp

            @if($pendingPayments->isEmpty())
                <p class="text-gray-500 text-center py-4">No hay pagos pendientes actualmente.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-2">Recibo</th>
                                <th class="px-4 py-2">Responsable</th>
                                <th class="px-4 py-2">Monto</th>
                                <th class="px-4 py-2">Emitido</th>
                                <th class="px-4 py-2">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayments as $payment)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium">{{ $payment->receipt_number }}</td>
                                    <td class="px-4 py-2">{{ $payment->contract->responsible->first_name }} {{ $payment->contract->responsible->last_name }}</td>
                                    <td class="px-4 py-2 font-semibold">Q{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-2">
                                        {{ $payment->issue_date->format('d/m/Y') }}
                                        <span class="text-xs text-gray-500 block">Hace {{ $payment->issue_date->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.admin.resources.payments.view', $payment) }}" class="text-primary-600 hover:text-primary-900">Ver detalles</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <a href="{{ route('filament.admin.resources.payments.index', ['tableFilters[payment_status_id][value]' => 2]) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                        Ver todos los pagos pendientes →
                    </a>
                </div>
            @endif
        </x-filament::section>
    </div>

    <x-filament::section>
        <x-slot name="heading">Solicitudes de Exhumación Recientes</x-slot>

        @php
            $recentExhumations = $notifications['recentExhumations'];
        @endphp

        @if($recentExhumations->isEmpty())
            <p class="text-gray-500 text-center py-4">No hay solicitudes de exhumación pendientes actualmente.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-2"># Solicitud</th>
                            <th class="px-4 py-2">Nicho</th>
                            <th class="px-4 py-2">Fallecido</th>
                            <th class="px-4 py-2">Solicitante</th>
                            <th class="px-4 py-2">Fecha Solicitud</th>
                            <th class="px-4 py-2">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentExhumations as $exhumation)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ $exhumation->id }}</td>
                                <td class="px-4 py-2">{{ $exhumation->contract->niche->code }}</td>
                                <td class="px-4 py-2">{{ $exhumation->contract->deceased->person->first_name }} {{ $exhumation->contract->deceased->person->last_name }}</td>
                                <td class="px-4 py-2">{{ $exhumation->requester->first_name }} {{ $exhumation->requester->last_name }}</td>
                                <td class="px-4 py-2">
                                    {{ $exhumation->request_date->format('d/m/Y') }}
                                    <span class="text-xs text-gray-500 block">Hace {{ $exhumation->request_date->diffForHumans() }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('filament.admin.resources.exhumations.view', $exhumation) }}" class="text-primary-600 hover:text-primary-900">Ver detalles</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right">
                <a href="{{ route('filament.admin.resources.exhumations.index', ['tableFilters[exhumation_status_id][value]' => 1]) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                    Ver todas las solicitudes de exhumación →
                </a>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
