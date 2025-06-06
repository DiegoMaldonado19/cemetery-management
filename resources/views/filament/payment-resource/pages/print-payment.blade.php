<x-filament-panels::page>
    <!-- El contenedor principal con fondo blanco forzado y texto negro forzado -->
    <div id="printable-content" class="p-6 bg-white text-black rounded-lg shadow">
        <div class="mb-8 flex justify-between items-center border-b pb-4">
            <div>
                <h2 class="text-2xl font-bold text-black">CEMENTERIO GENERAL DE QUETZALTENANGO</h2>
                <p class="text-sm text-black">6ta. Calle 7-20 Zona 1, Quetzaltenango</p>
                <p class="text-sm text-black">Tel: 7761-2121</p>
            </div>
            <div class="text-right">
                <h1 class="text-3xl font-bold text-red-600">BOLETA DE PAGO</h1>
                <p class="text-xl font-semibold text-black">No. {{ $record->receipt_number }}</p>
                <p class="text-sm text-black">Fecha de emisión: {{ $record->issue_date->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-lg font-semibold mb-2 text-black">Información del Contrato</h3>
                <p class="text-black"><span class="font-medium">Contrato No:</span> {{ $record->contract->id }}</p>
                <p class="text-black"><span class="font-medium">Nicho:</span> {{ $record->contract->niche->code }}</p>
                <p class="text-black"><span class="font-medium">Ubicación:</span> Calle {{ $record->contract->niche->street->street_number }},
                   Avenida {{ $record->contract->niche->avenue->avenue_number }},
                   {{ $record->contract->niche->street->block->section->name }}</p>
                <p class="text-black"><span class="font-medium">Tipo:</span> {{ $record->contract->niche->type->name }}</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-2 text-black">Información del Fallecido</h3>
                <p class="text-black"><span class="font-medium">Nombre:</span>
                   {{ $record->contract->deceased->person->first_name }}
                   {{ $record->contract->deceased->person->last_name }}</p>
                <p class="text-black"><span class="font-medium">CUI/DPI:</span> {{ $record->contract->deceased->person->cui }}</p>
                <p class="text-black"><span class="font-medium">Fecha de fallecimiento:</span>
                   {{ $record->contract->deceased->death_date->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2 text-black">Información del Responsable</h3>
            <p class="text-black"><span class="font-medium">Nombre:</span>
               {{ $record->contract->responsible->first_name }}
               {{ $record->contract->responsible->last_name }}</p>
            <p class="text-black"><span class="font-medium">CUI/DPI:</span> {{ $record->contract->responsible->cui }}</p>
            <p class="text-black"><span class="font-medium">Teléfono:</span> {{ $record->contract->responsible->phone ?? 'No registrado' }}</p>
            <p class="text-black"><span class="font-medium">Correo:</span> {{ $record->contract->responsible->email ?? 'No registrado' }}</p>
        </div>

        <div class="mb-8 border-t border-b py-4">
            <h3 class="text-lg font-semibold mb-4 text-black">Detalle de Pago</h3>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left text-black">Descripción</th>
                        <th class="px-4 py-2 text-right text-black">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 py-2 border-b text-black">
                            @if($record->amount == 600)
                                Pago por contrato de nicho (renovación por 6 años)
                            @else
                                {{ $record->notes ?? 'Pago por servicios de cementerio' }}
                            @endif
                        </td>
                        <td class="px-4 py-2 border-b text-right font-semibold text-black">Q {{ number_format($record->amount, 2) }}</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="px-4 py-2 text-right font-bold text-black">Total:</td>
                        <td class="px-4 py-2 text-right font-bold text-black">Q {{ number_format($record->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-lg font-semibold mb-2 text-black">Estado del Pago</h3>
                <p class="inline-flex items-center px-2.5 py-0.5 rounded-full {{ $record->status->name === 'Pagado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} text-xs font-medium">
                    {{ $record->status->name }}
                </p>
                @if($record->payment_date)
                    <p class="mt-1 text-black"><span class="font-medium">Fecha de pago:</span> {{ $record->payment_date->format('d/m/Y') }}</p>
                @endif
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-2 text-black">Información Adicional</h3>
                <p class="text-black"><span class="font-medium">Emitido por:</span> {{ $record->user->name }}</p>
                @if($record->notes)
                    <p class="text-black"><span class="font-medium">Notas:</span> {{ $record->notes }}</p>
                @endif
            </div>
        </div>

        <div class="flex justify-between items-center mt-12 pt-8 border-t">
            <div class="text-center w-1/3">
                <div class="border-t border-gray-400 w-full mx-auto"></div>
                <p class="mt-2 text-black">Firma del Responsable</p>
            </div>
            <div class="text-center w-1/3">
                <div class="border-t border-gray-400 w-full mx-auto"></div>
                <p class="mt-2 text-black">Firma y Sello de Administración</p>
            </div>
        </div>

        <div class="mt-8 text-center text-sm text-black">
            <p>Este documento es una constancia de pago oficial del Cementerio General de Quetzaltenango.</p>
            <p>Conserve este recibo para cualquier trámite relacionado con el contrato.</p>
            <p class="mt-2 text-xs">Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="mt-4 text-center no-print">
        <button onclick="printDocument()" class="px-4 py-2 bg-primary-600 text-white rounded-lg shadow hover:bg-primary-700">
            Imprimir Boleta
        </button>
    </div>

    <style>
        /* Forzar colores en modo oscuro para el contenedor de la boleta */
        #printable-content {
            background-color: white !important;
            color: black !important;
        }

        #printable-content * {
            color: black !important;
        }

        #printable-content h1.text-red-600 {
            color: #dc2626 !important; /* Mantener rojo para el título */
        }

        /* Estado del pago */
        #printable-content .bg-green-100.text-green-800 {
            background-color: #dcfce7 !important;
            color: #166534 !important;
        }

        #printable-content .bg-red-100.text-red-800 {
            background-color: #fee2e2 !important;
            color: #991b1b !important;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            #printable-content, #printable-content * {
                visibility: visible;
                color: black !important;
                background-color: white !important;
                border-color: #000 !important;
            }
            #printable-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background-color: white !important;
            }
            .no-print {
                display: none;
            }
        }
    </style>

    <script>
        function printDocument() {
            window.print();
        }
    </script>
</x-filament-panels::page>
