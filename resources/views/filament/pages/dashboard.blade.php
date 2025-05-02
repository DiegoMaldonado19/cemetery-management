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
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:text-gray-300 dark:bg-gray-800">
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
                                <tr class="bg-white dark:bg-gray-900 border-b hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-medium dark:text-gray-300">{{ $contract->niche->code }}</td>
                                    <td class="px-4 py-2 dark:text-gray-300">{{ $contract->deceased->person->first_name }} {{ $contract->deceased->person->last_name }}</td>
                                    <td class="px-4 py-2 dark:text-gray-300">{{ $contract->responsible->first_name }} {{ $contract->responsible->last_name }}</td>
                                    <td class="px-4 py-2">
                                        <span class="text-red-600 dark:text-red-400 font-semibold">{{ $contract->end_date->format('d/m/Y') }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 block">En {{ $contract->end_date->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="#"
                                            x-on:click.prevent="$dispatch('open-modal', { id: 'view-contract-{{ $contract->id }}' })"
                                            class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                            Ver detalles
                                        </a>
                                        @if(Auth::hasUser() && Auth::user()->isAdmin())
                                            <a href="{{ route('filament.admin.resources.contracts.edit', $contract) }}"
                                               class="ml-2 text-amber-600 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-300">
                                                Renovar
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <a href="{{ route('filament.admin.resources.contracts.index') }}?tableFilters[expiring_soon][isActive]=true" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
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
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:text-gray-300 dark:bg-gray-800">
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
                                <tr class="bg-white dark:bg-gray-900 border-b hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-medium dark:text-gray-300">{{ $payment->receipt_number }}</td>
                                    <td class="px-4 py-2 dark:text-gray-300">{{ $payment->contract->responsible->first_name }} {{ $payment->contract->responsible->last_name }}</td>
                                    <td class="px-4 py-2 font-semibold dark:text-gray-300">Q{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-2 dark:text-gray-300">
                                        {{ $payment->issue_date->format('d/m/Y') }}
                                        <span class="text-xs text-gray-500 dark:text-gray-400 block">Hace {{ $payment->issue_date->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="#"
                                            x-on:click.prevent="$dispatch('open-modal', { id: 'view-payment-{{ $payment->id }}' })"
                                            class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                            Ver detalles
                                        </a>
                                        @if(Auth::hasUser() && Auth::user()->isAdmin())
                                            <a href="{{ route('filament.admin.resources.payments.edit', $payment) }}"
                                               class="ml-2 text-amber-600 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-300">
                                                Procesar
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <a href="{{ route('filament.admin.resources.payments.index') }}?tableFilters[payment_status_id][value]=2" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
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
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:text-gray-300 dark:bg-gray-800">
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
                            <tr class="bg-white dark:bg-gray-900 border-b hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2 font-medium dark:text-gray-300">{{ $exhumation->id }}</td>
                                <td class="px-4 py-2 dark:text-gray-300">{{ $exhumation->contract->niche->code }}</td>
                                <td class="px-4 py-2 dark:text-gray-300">{{ $exhumation->contract->deceased->person->first_name }} {{ $exhumation->contract->deceased->person->last_name }}</td>
                                <td class="px-4 py-2 dark:text-gray-300">{{ $exhumation->requester->first_name }} {{ $exhumation->requester->last_name }}</td>
                                <td class="px-4 py-2 dark:text-gray-300">
                                    {{ $exhumation->request_date->format('d/m/Y') }}
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">Hace {{ $exhumation->request_date->diffForHumans() }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <a href="#"
                                        x-on:click.prevent="$dispatch('open-modal', { id: 'view-exhumation-{{ $exhumation->id }}' })"
                                        class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                        Ver detalles
                                    </a>
                                    @if(Auth::hasUser() && Auth::user()->isAdmin())
                                        <a href="{{ route('filament.admin.resources.exhumations.edit', $exhumation) }}"
                                           class="ml-2 text-amber-600 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-300">
                                            Procesar
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right">
                <a href="{{ route('filament.admin.resources.exhumations.index') }}?tableFilters[exhumation_status_id][value]=1" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                    Ver todas las solicitudes de exhumación →
                </a>
            </div>
        @endif
    </x-filament::section>

    <!-- Modales para los contratos -->
    @foreach($expiringContracts as $contract)
        <x-filament::modal id="view-contract-{{ $contract->id }}" width="lg">
            <x-slot name="heading">
                Detalles del Contrato #{{ $contract->id }}
            </x-slot>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Nicho</h3>
                        <p class="mt-1 dark:text-gray-300">{{ $contract->niche->code }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</h3>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $contract->status->name === 'Vigente' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($contract->status->name === 'En Gracia' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                {{ $contract->status->name }}
                            </span>
                        </p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fallecido</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $contract->deceased->person->first_name }} {{ $contract->deceased->person->last_name }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Responsable</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $contract->responsible->first_name }} {{ $contract->responsible->last_name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Inicio</h3>
                        <p class="mt-1 dark:text-gray-300">{{ $contract->start_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Vencimiento</h3>
                        <p class="mt-1 dark:text-gray-300">{{ $contract->end_date->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Gracia</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $contract->grace_date->format('d/m/Y') }}</p>
                </div>

                @if($contract->notes)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $contract->notes }}</p>
                </div>
                @endif
            </div>

            <x-slot name="footerActions">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'view-contract-{{ $contract->id }}' })"
                >
                    Cerrar
                </x-filament::button>

                <x-filament::button
                    color="primary"
                    tag="a"
                    href="{{ route('filament.admin.resources.contracts.view', $contract) }}"
                >
                    Ver página completa
                </x-filament::button>

                @if(Auth::hasUser() && Auth::user()->isAdmin())
                    <x-filament::button
                        color="success"
                        tag="a"
                        href="{{ route('filament.admin.resources.contracts.edit', $contract) }}"
                    >
                        Renovar Contrato
                    </x-filament::button>
                @endif
            </x-slot>
        </x-filament::modal>
    @endforeach

    <!-- Modales para los pagos -->
    @foreach($pendingPayments as $payment)
        <x-filament::modal id="view-payment-{{ $payment->id }}" width="lg">
            <x-slot name="heading">
                Detalles del Pago - Recibo #{{ $payment->receipt_number }}
            </x-slot>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Contrato</h3>
                        <p class="mt-1 dark:text-gray-300"># {{ $payment->contract->id }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Nicho</h3>
                        <p class="mt-1 dark:text-gray-300">{{ $payment->contract->niche->code }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Responsable del Contrato</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $payment->contract->responsible->first_name }} {{ $payment->contract->responsible->last_name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Monto</h3>
                        <p class="mt-1 dark:text-gray-300 font-semibold">Q{{ number_format($payment->amount, 2) }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</h3>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payment->status->name === 'Pagado' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $payment->status->name }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Emisión</h3>
                        <p class="mt-1 dark:text-gray-300">{{ $payment->issue_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Pago</h3>
                        <p class="mt-1 dark:text-gray-300">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'Pendiente de pago' }}</p>
                    </div>
                </div>

                @if($payment->notes)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $payment->notes }}</p>
                </div>
                @endif
            </div>

            <x-slot name="footerActions">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'view-payment-{{ $payment->id }}' })"
                >
                    Cerrar
                </x-filament::button>

                <x-filament::button
                    color="primary"
                    tag="a"
                    href="{{ route('filament.admin.resources.payments.view', $payment) }}"
                >
                    Ver página completa
                </x-filament::button>

                @if(Auth::hasUser() && Auth::user()->isAdmin())
                    <x-filament::button
                        color="success"
                        tag="a"
                        href="{{ route('filament.admin.resources.payments.edit', $payment) }}"
                    >
                        Procesar Pago
                    </x-filament::button>
                @endif
            </x-slot>
        </x-filament::modal>
    @endforeach

    <!-- Modales para las exhumaciones -->
    @foreach($recentExhumations as $exhumation)
        <x-filament::modal id="view-exhumation-{{ $exhumation->id }}" width="lg">
            <x-slot name="heading">
                Detalles de la Solicitud de Exhumación #{{ $exhumation->id }}
            </x-slot>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Contrato</h3>
                        <p class="mt-1 dark:text-gray-300"># {{ $exhumation->contract->id }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Nicho</h3>
                        <p class="mt-1 dark:text-gray-300">{{ $exhumation->contract->niche->code }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fallecido</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $exhumation->contract->deceased->person->first_name }} {{ $exhumation->contract->deceased->person->last_name }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Solicitante</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $exhumation->requester->first_name }} {{ $exhumation->requester->last_name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Solicitud</h3>
                        <p class="mt-1 dark:text-gray-300">{{ $exhumation->request_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</h3>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $exhumation->status->name === 'Solicitada' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : ($exhumation->status->name === 'Aprobada' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($exhumation->status->name === 'Rechazada' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200')) }}">
                                {{ $exhumation->status->name }}
                            </span>
                        </p>
                    </div>
                </div>

                @if($exhumation->exhumation_date)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Programada de Exhumación</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $exhumation->exhumation_date->format('d/m/Y') }}</p>
                </div>
                @endif

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Motivo</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $exhumation->reason }}</p>
                </div>

                @if($exhumation->notes)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas</h3>
                    <p class="mt-1 dark:text-gray-300">{{ $exhumation->notes }}</p>
                </div>
                @endif
            </div>

            <x-slot name="footerActions">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'view-exhumation-{{ $exhumation->id }}' })"
                >
                    Cerrar
                </x-filament::button>

                <x-filament::button
                    color="primary"
                    tag="a"
                    href="{{ route('filament.admin.resources.exhumations.view', $exhumation) }}"
                >
                    Ver página completa
                </x-filament::button>

                @if(Auth::hasUser() && Auth::user()->isAdmin())
                    <x-filament::button
                        color="success"
                        tag="a"
                        href="{{ route('filament.admin.resources.exhumations.edit', $exhumation) }}"
                    >
                        Procesar Solicitud
                    </x-filament::button>
                @endif
            </x-slot>
        </x-filament::modal>
    @endforeach
</x-filament-panels::page>
