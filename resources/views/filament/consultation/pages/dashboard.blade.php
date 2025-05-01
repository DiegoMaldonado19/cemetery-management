<x-filament-panels::page>
    <div class="space-y-8">
        <!-- Sección de bienvenida -->
        <x-filament::section>
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Bienvenido al Sistema de Consulta de Nichos</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Desde aquí podrá buscar información sobre nichos, solicitar boletas de pago y enviar solicitudes de exhumación.
                </p>
            </div>
        </x-filament::section>

        <!-- Pestañas de navegación -->
        <div x-data="{ activeTab: 'search' }">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-6">
                    <button
                        @click="activeTab = 'search'"
                        :class="{ 'border-primary-500 text-primary-600 dark:text-primary-500': activeTab === 'search', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== 'search' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Buscar Nichos
                    </button>
                    <button
                        @click="activeTab = 'contracts'"
                        :class="{ 'border-primary-500 text-primary-600 dark:text-primary-500': activeTab === 'contracts', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== 'contracts' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Mis Contratos
                    </button>
                    <button
                        @click="activeTab = 'payments'"
                        :class="{ 'border-primary-500 text-primary-600 dark:text-primary-500': activeTab === 'payments', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== 'payments' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Solicitar Boleta
                    </button>
                    <button
                        @click="activeTab = 'exhumation'"
                        :class="{ 'border-primary-500 text-primary-600 dark:text-primary-500': activeTab === 'exhumation', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== 'exhumation' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Solicitar Exhumación
                    </button>
                </nav>
            </div>

            <!-- Contenido de las pestañas -->
            <div class="mt-4">
                <!-- Pestaña de búsqueda de nichos -->
                <div x-show="activeTab === 'search'" x-transition>
                    <x-filament::section>
                        <x-slot name="heading">Búsqueda de Nichos</x-slot>

                        <form wire:submit="search">
                            {{ $this->searchForm }}

                            <div class="mt-4 flex justify-end">
                                <x-filament::button type="submit">
                                    Buscar
                                </x-filament::button>
                            </div>
                        </form>

                        @if($hasSearched)
                            <div class="mt-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Resultados de la búsqueda</h3>

                                @if(count($searchResults) > 0)
                                    <div class="mt-4 space-y-6">
                                        @foreach($searchResults as $niche)
                                            <div class="bg-white dark:bg-gray-800 p-4 border dark:border-gray-700 rounded-lg shadow-sm">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="text-lg font-bold dark:text-gray-200">Nicho: {{ $niche['code'] }}</h4>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                                            Tipo: {{ $niche['type']['name'] }} |
                                                            Estado: {{ $niche['status']['name'] }}
                                                        </p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                            Ubicación:
                                                            Calle {{ $niche['street']['street_number'] }},
                                                            Avenida {{ $niche['avenue']['avenue_number'] }},
                                                            {{ $niche['street']['block']['section']['name'] }}
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $niche['status']['name'] === 'Disponible' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                                                            {{ $niche['status']['name'] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                @if(isset($niche['contracts']) && count($niche['contracts']) > 0)
                                                    @foreach($niche['contracts'] as $contract)
                                                        <div class="mt-4 border-t pt-4 dark:border-gray-700">
                                                            <h5 class="font-medium dark:text-gray-200">Contrato #{{ $contract['id'] }}</h5>

                                                            @if(isset($contract['deceased']) && isset($contract['deceased']['person']))
                                                                <p class="text-sm dark:text-gray-300">
                                                                    <span class="font-medium">Fallecido:</span>
                                                                    {{ $contract['deceased']['person']['first_name'] }}
                                                                    {{ $contract['deceased']['person']['last_name'] }}
                                                                </p>
                                                            @endif

                                                            @if(isset($contract['responsible']))
                                                                <p class="text-sm dark:text-gray-300">
                                                                    <span class="font-medium">Responsable:</span>
                                                                    {{ $contract['responsible']['first_name'] }}
                                                                    {{ $contract['responsible']['last_name'] }}
                                                                </p>
                                                            @endif

                                                            <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                                                <div>
                                                                    <p class="dark:text-gray-300"><span class="font-medium">Inicio:</span> {{ \Carbon\Carbon::parse($contract['start_date'])->format('d/m/Y') }}</p>
                                                                    <p class="dark:text-gray-300"><span class="font-medium">Fin:</span> {{ \Carbon\Carbon::parse($contract['end_date'])->format('d/m/Y') }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="dark:text-gray-300"><span class="font-medium">Gracia hasta:</span> {{ \Carbon\Carbon::parse($contract['grace_date'])->format('d/m/Y') }}</p>
                                                                    <p>
                                                                        <span class="font-medium dark:text-gray-300">Estado:</span>
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $contract['status']['name'] === 'Vigente' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($contract['status']['name'] === 'En Gracia' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                                                            {{ $contract['status']['name'] }}
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 italic">Este nicho no tiene contratos asociados.</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-center">
                                        <p class="text-gray-600 dark:text-gray-400">No se encontraron resultados para los criterios de búsqueda.</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </x-filament::section>
                </div>

                <!-- Pestaña de mis contratos -->
                <div x-show="activeTab === 'contracts'" x-transition>
                    <x-filament::section>
                        <x-slot name="heading">Mis Contratos</x-slot>

                        @php
                            $userContracts = $this->getUserContracts();
                        @endphp

                        @if($userContracts->isEmpty())
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 text-center rounded-lg">
                                <p class="text-gray-600 dark:text-gray-400">No tiene contratos registrados a su nombre.</p>
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach($userContracts as $contract)
                                    <div class="bg-white dark:bg-gray-800 p-4 border dark:border-gray-700 rounded-lg shadow-sm">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-lg font-bold dark:text-gray-200">Contrato #{{ $contract->id }}</h4>
                                                <p class="text-sm dark:text-gray-300">
                                                    <span class="font-medium">Nicho:</span> {{ $contract->niche->code }}
                                                    ({{ $contract->niche->type->name }})
                                                </p>
                                                <p class="text-sm dark:text-gray-300">
                                                    <span class="font-medium">Fallecido:</span>
                                                    {{ $contract->deceased->person->first_name }} {{ $contract->deceased->person->last_name }}
                                                </p>

                                                <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                                    <div>
                                                        <p class="dark:text-gray-300"><span class="font-medium">Inicio:</span> {{ $contract->start_date->format('d/m/Y') }}</p>
                                                        <p class="dark:text-gray-300"><span class="font-medium">Fin:</span> {{ $contract->end_date->format('d/m/Y') }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="dark:text-gray-300"><span class="font-medium">Gracia hasta:</span> {{ $contract->grace_date->format('d/m/Y') }}</p>
                                                        <p>
                                                            <span class="font-medium dark:text-gray-300">Estado:</span>
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $contract->status->name === 'Vigente' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($contract->status->name === 'En Gracia' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                                                {{ $contract->status->name }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="mt-3 border-t pt-3 dark:border-gray-700">
                                                    <h5 class="font-medium dark:text-gray-200">Pagos Recientes</h5>

                                                    @if($contract->payments->isEmpty())
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 italic">No hay pagos registrados para este contrato.</p>
                                                    @else
                                                        <div class="mt-2 overflow-x-auto">
                                                            <table class="min-w-full text-sm">
                                                                <thead>
                                                                    <tr class="border-b dark:border-gray-700">
                                                                        <th class="text-left py-2 px-2 dark:text-gray-300">Recibo</th>
                                                                        <th class="text-left py-2 px-2 dark:text-gray-300">Monto</th>
                                                                        <th class="text-left py-2 px-2 dark:text-gray-300">Emitida</th>
                                                                        <th class="text-left py-2 px-2 dark:text-gray-300">Estado</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($contract->payments->take(3) as $payment)
                                                                        <tr class="border-b dark:border-gray-700">
                                                                            <td class="py-2 px-2 dark:text-gray-300">{{ $payment->receipt_number }}</td>
                                                                            <td class="py-2 px-2 dark:text-gray-300">Q{{ number_format($payment->amount, 2) }}</td>
                                                                            <td class="py-2 px-2 dark:text-gray-300">{{ $payment->issue_date->format('d/m/Y') }}</td>
                                                                            <td class="py-2 px-2">
                                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payment->status->name === 'Pagado' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                                                    {{ $payment->status->name }}
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $contract->status->name === 'Vigente' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($contract->status->name === 'En Gracia' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                                    {{ $contract->status->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-filament::section>
                </div>

                <!-- Pestaña de solicitud de boleta -->
                <div x-show="activeTab === 'payments'" x-transition>
                    <x-filament::section>
                        <x-slot name="heading">Solicitar Boleta de Pago</x-slot>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Utilice este formulario para solicitar una boleta de pago para renovación de contrato.
                            Un administrador procesará su solicitud y generará la boleta correspondiente.
                        </p>

                        <form wire:submit="requestPayment">
                            {{ $this->paymentRequestForm }}

                            <div class="mt-4 flex justify-end">
                                <x-filament::button type="submit">
                                    Enviar Solicitud
                                </x-filament::button>
                            </div>
                        </form>
                    </x-filament::section>
                </div>

                <!-- Pestaña de solicitud de exhumación -->
                <div x-show="activeTab === 'exhumation'" x-transition>
                    <x-filament::section>
                        <x-slot name="heading">Solicitar Exhumación</x-slot>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Complete este formulario para solicitar una exhumación. Tenga en cuenta que las exhumaciones no pueden
                            realizarse en nichos que contengan restos de personajes históricos.
                        </p>

                        <form wire:submit="requestExhumation">
                            {{ $this->exhumationRequestForm }}

                            <div class="mt-4 flex justify-end">
                                <x-filament::button type="submit">
                                    Enviar Solicitud
                                </x-filament::button>
                            </div>
                        </form>
                    </x-filament::section>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
