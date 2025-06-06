<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            Registrar a {{ $record->first_name }} {{ $record->last_name }} como Personaje Histórico
        </h2>

        <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
            <p>Al registrar a esta persona como personaje histórico, los nichos asociados estarán protegidos contra exhumaciones.</p>
        </div>

        {{ $this->form }}

        <div class="flex justify-end mt-6">
            {{ $this->getAction('save') }}
        </div>
    </div>
</x-filament-panels::page>
