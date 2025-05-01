<?php

namespace App\Filament\Consultation\Pages;

use App\Models\Contract;
use App\Models\Niche;
use App\Models\Payment;
use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Models\PaymentStatus;
use App\Models\ContractStatus;

class Dashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.consultation.pages.dashboard';

    protected static ?string $navigationLabel = 'Inicio';

    protected static ?string $title = 'Sistema de Consulta de Nichos';

    protected static ?int $navigationSort = 1;

    // Propiedades para el formulario de búsqueda de nichos
    public ?string $nicheCode = null;
    public ?string $deceasedName = null;
    public ?array $searchResults = [];
    public bool $hasSearched = false;

    // Propiedades para el formulario de solicitud de boleta
    public ?string $selectedContractId = null;
    public ?string $requestReason = null;

    // Propiedades para el formulario de solicitud de exhumación
    public ?string $exhumationContractId = null;
    public ?string $exhumationReason = null;
    public ?string $requestorName = null;
    public ?string $requestorPhone = null;
    public ?string $contactEmail = null;

    public function mount(): void
    {
        $this->searchForm->fill();
        $this->paymentRequestForm->fill();
        $this->exhumationRequestForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'searchForm',
            'paymentRequestForm',
            'exhumationRequestForm',
        ];
    }

    public function searchForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nicheCode')
                    ->label('Código de Nicho')
                    ->placeholder('Ejemplo: N-001')
                    ->helperText('Ingrese el código exacto del nicho que desea consultar'),

                TextInput::make('deceasedName')
                    ->label('Nombre del Fallecido')
                    ->placeholder('Ejemplo: Juan Pérez')
                    ->helperText('Ingrese el nombre o apellido del fallecido'),
            ])
            ->statePath('searchForm')
            ->columns(2);
    }

    public function paymentRequestForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedContractId')
                    ->label('Contrato a Renovar')
                    ->options(function () {
                        $user = Auth::user();
                        if (!$user) {
                            return [];
                        }

                        return Contract::whereHas('responsible', function (Builder $query) use ($user) {
                                $query->where('cui', $user->cui);
                            })
                            ->with(['niche', 'deceased.person'])
                            ->get()
                            ->mapWithKeys(function ($contract) {
                                $niche = $contract->niche;
                                $deceased = $contract->deceased->person;
                                return [
                                    $contract->id => "Contrato #{$contract->id} - Nicho: {$niche->code} - {$deceased->first_name} {$deceased->last_name}"
                                ];
                            });
                    })
                    ->helperText('Seleccione el contrato para el cual desea solicitar una boleta de pago')
                    ->required(),

                TextInput::make('requestReason')
                    ->label('Motivo de la Solicitud')
                    ->placeholder('Ejemplo: Renovación de contrato')
                    ->helperText('Indique brevemente el motivo de la solicitud de boleta')
                    ->required(),
            ])
            ->statePath('paymentRequestForm');
    }

    public function exhumationRequestForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('exhumationContractId')
                    ->label('Contrato para Exhumación')
                    ->options(function () {
                        $user = Auth::user();
                        if (!$user) {
                            return [];
                        }

                        return Contract::whereHas('responsible', function (Builder $query) use ($user) {
                                $query->where('cui', $user->cui);
                            })
                            ->whereDoesntHave('niche', function (Builder $query) {
                                $query->whereNotNull('historical_figure_id');
                            })
                            ->with(['niche', 'deceased.person'])
                            ->get()
                            ->mapWithKeys(function ($contract) {
                                $niche = $contract->niche;
                                $deceased = $contract->deceased->person;
                                return [
                                    $contract->id => "Contrato #{$contract->id} - Nicho: {$niche->code} - {$deceased->first_name} {$deceased->last_name}"
                                ];
                            });
                    })
                    ->helperText('Seleccione el contrato para el cual desea solicitar una exhumación')
                    ->required(),

                TextInput::make('exhumationReason')
                    ->label('Motivo de la Exhumación')
                    ->placeholder('Ejemplo: Traslado a otro cementerio')
                    ->helperText('Indique detalladamente el motivo de la solicitud de exhumación')
                    ->required(),

                TextInput::make('requestorName')
                    ->label('Nombre del Solicitante')
                    ->placeholder('Ejemplo: María López')
                    ->helperText('Ingrese su nombre completo como solicitante')
                    ->required(),

                TextInput::make('requestorPhone')
                    ->label('Teléfono de Contacto')
                    ->tel()
                    ->placeholder('Ejemplo: 55123456')
                    ->helperText('Número de teléfono donde se le puede contactar')
                    ->required(),

                TextInput::make('contactEmail')
                    ->label('Correo Electrónico')
                    ->email()
                    ->placeholder('Ejemplo: usuario@correo.com')
                    ->helperText('Correo electrónico para notificaciones sobre su solicitud')
                    ->required(),
            ])
            ->statePath('exhumationRequestForm')
            ->columns(1);
    }

    public function search(): void
    {
        $this->validate([
            'nicheCode' => 'nullable|string',
            'deceasedName' => 'nullable|string',
        ]);

        // Al menos uno de los campos debe tener valor
        if (empty($this->nicheCode) && empty($this->deceasedName)) {
            Notification::make()
                ->warning()
                ->title('Debe ingresar al menos un criterio de búsqueda')
                ->send();
            return;
        }

        $query = Niche::query()
            ->with(['contracts.deceased.person', 'contracts.responsible', 'contracts.status', 'type', 'status', 'street.block.section', 'avenue.block.section']);

        // Búsqueda por código de nicho
        if (!empty($this->nicheCode)) {
            $query->where('code', 'like', '%' . $this->nicheCode . '%');
        }

        // Búsqueda por nombre de fallecido
        if (!empty($this->deceasedName)) {
            $query->whereHas('contracts.deceased.person', function (Builder $subQuery) {
                $subQuery->where('first_name', 'like', '%' . $this->deceasedName . '%')
                    ->orWhere('last_name', 'like', '%' . $this->deceasedName . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $this->deceasedName . '%']);
            });
        }

        // Ejecutar la consulta y almacenar resultados
        $this->searchResults = $query->limit(10)->get()->toArray();
        $this->hasSearched = true;
    }

    public function requestPayment(): void
    {
        $data = $this->paymentRequestForm->getState();

        $this->validate([
            'selectedContractId' => 'required|exists:contracts,id',
            'requestReason' => 'required|string|min:3|max:255',
        ]);

        // Verificar que el contrato pertenece al usuario actual
        $contract = Contract::find($data['selectedContractId']);
        $user = Auth::user();

        if (!$contract || $contract->responsible_cui !== $user->cui) {
            Notification::make()
                ->warning()
                ->title('No tiene permiso para solicitar boletas para este contrato')
                ->send();
            return;
        }

        // Verificar que no haya una boleta pendiente
        $pendingPayment = Payment::where('contract_id', $contract->id)
            ->whereHas('status', function($query) {
                $query->where('name', 'No Pagado');
            })
            ->exists();

        if ($pendingPayment) {
            Notification::make()
                ->warning()
                ->title('Ya existe una boleta de pago pendiente para este contrato')
                ->send();
            return;
        }

        // Crear notificación para el administrador
        \App\Models\Notification::create([
            'contract_id' => $contract->id,
            'sent_at' => now(),
            'message' => "Solicitud de boleta de pago: {$data['requestReason']}",
            'is_sent' => true,
            'read_at' => null,
        ]);

        Notification::make()
            ->success()
            ->title('Solicitud de boleta enviada correctamente')
            ->body('Un administrador procesará su solicitud y generará la boleta de pago.')
            ->send();

        // Limpiar el formulario
        $this->paymentRequestForm->fill();
    }

    public function requestExhumation(): void
    {
        $data = $this->exhumationRequestForm->getState();

        $this->validate([
            'exhumationContractId' => 'required|exists:contracts,id',
            'exhumationReason' => 'required|string|min:10|max:500',
            'requestorName' => 'required|string|min:3|max:100',
            'requestorPhone' => 'required|string|min:8|max:20',
            'contactEmail' => 'required|email|max:100',
        ]);

        // Verificar que el contrato pertenece al usuario actual
        $contract = Contract::find($data['exhumationContractId']);
        $user = Auth::user();

        if (!$contract || $contract->responsible_cui !== $user->cui) {
            Notification::make()
                ->warning()
                ->title('No tiene permiso para solicitar exhumaciones para este contrato')
                ->send();
            return;
        }

        // Verificar que el nicho no pertenezca a un personaje histórico
        if ($contract->niche->historical_figure_id) {
            Notification::make()
                ->danger()
                ->title('No es posible solicitar exhumación para personajes históricos')
                ->body('Este nicho contiene restos de una persona catalogada como personaje histórico y no puede ser exhumado.')
                ->send();
            return;
        }

        // Crear notificación para el administrador
        \App\Models\Notification::create([
            'contract_id' => $contract->id,
            'sent_at' => now(),
            'message' => "Solicitud de exhumación: {$data['exhumationReason']} - Contacto: {$data['requestorName']} ({$data['requestorPhone']})",
            'is_sent' => true,
            'read_at' => null,
        ]);

        Notification::make()
            ->success()
            ->title('Solicitud de exhumación enviada correctamente')
            ->body('Un administrador revisará su solicitud y se pondrá en contacto con usted.')
            ->send();

        // Limpiar el formulario
        $this->exhumationRequestForm->fill();
    }

    public function getUserContracts()
    {
        $user = Auth::user();

        if (!$user) {
            return collect();
        }

        return Contract::where('responsible_cui', $user->cui)
            ->with(['niche', 'deceased.person', 'status', 'payments' => function($query) {
                $query->orderBy('issue_date', 'desc');
            }])
            ->get();
    }
}
