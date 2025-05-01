<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use App\Models\DeathCause;
use App\Models\Deceased;
use App\Models\Person;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterDeceased extends Page
{
    protected static string $resource = PersonResource::class;

    protected static string $view = 'filament.resources.person-resource.pages.register-deceased';

    public Person $record;

    public ?array $data = [];

    public function mount(Person $record): void
    {
        $this->record = $record;

        if ($record->deceased) {
            Notification::make()
                ->warning()
                ->title('Esta persona ya está registrada como fallecida')
                ->send();

            $this->redirect(PersonResource::getUrl('view', ['record' => $record]));
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('death_date')
                    ->label('Fecha de Fallecimiento')
                    ->required()
                    ->default(now()),
                Select::make('death_cause_id')
                    ->label('Causa de Muerte')
                    ->options(DeathCause::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('origin')
                    ->label('Procedencia')
                    ->maxLength(100),
                Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(65535),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::beginTransaction();

        try {
            Deceased::create([
                'cui' => $this->record->cui,
                'death_date' => $data['death_date'],
                'death_cause_id' => $data['death_cause_id'],
                'origin' => $data['origin'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Registro en change_logs
            DB::table('change_logs')->insert([
                'table_name' => 'deceased',
                'record_id' => $this->record->cui,
                'changed_field' => 'creación',
                'old_value' => 'Ninguno',
                'new_value' => 'Nuevo fallecido registrado',
                'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Notification::make()
                ->success()
                ->title('Fallecimiento registrado correctamente')
                ->send();

            $this->redirect(PersonResource::getUrl('view', ['record' => $this->record]));

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->danger()
                ->title('Error al registrar el fallecimiento')
                ->body($e->getMessage())
                ->send();
        }
    }
}
