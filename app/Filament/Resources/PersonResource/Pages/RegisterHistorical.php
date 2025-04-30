<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use App\Models\HistoricalFigure;
use App\Models\Person;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterHistorical extends Page
{
    protected static string $resource = PersonResource::class;

    protected static string $view = 'filament.resources.person-resource.pages.register-historical';

    public Person $record;

    public ?array $data = [];

    public function mount(Person $record): void
    {
        $this->record = $record;

        if ($record->historicalFigure) {
            Notification::make()
                ->warning()
                ->title('Esta persona ya está registrada como personaje histórico')
                ->send();

            $this->redirect(PersonResource::getUrl('view', ['record' => $record]));
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('declaration_date')
                    ->label('Fecha de Declaración como Personaje Histórico')
                    ->required()
                    ->default(now()),
                Textarea::make('historical_reason')
                    ->label('Razón o Motivo Histórico')
                    ->required()
                    ->maxLength(65535),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::beginTransaction();

        try {
            HistoricalFigure::create([
                'cui' => $this->record->cui,
                'historical_reason' => $data['historical_reason'],
                'declaration_date' => $data['declaration_date'],
            ]);

            // Registro en change_logs
            DB::table('change_logs')->insert([
                'table_name' => 'historical_figures',
                'record_id' => $this->record->cui,
                'changed_field' => 'creación',
                'old_value' => 'Ninguno',
                'new_value' => 'Nuevo personaje histórico registrado',
                'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Notification::make()
                ->success()
                ->title('Personaje histórico registrado correctamente')
                ->send();

            $this->redirect(PersonResource::getUrl('view', ['record' => $this->record]));
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->danger()
                ->title('Error al registrar el personaje histórico')
                ->body($e->getMessage())
                ->send();
        }
    }
}
