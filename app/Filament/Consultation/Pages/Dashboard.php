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
use App\Models\CemeteryStreet;
use App\Models\CemeteryAvenue;
use Illuminate\Support\Facades\DB;

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
    public ?string $street_id = null;
    public ?string $avenue_id = null;
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

                Select::make('street_id')
                    ->label('Calle')
                    ->options(CemeteryStreet::all()->pluck('street_number', 'id'))
                    ->placeholder('Seleccione una calle')
                    ->searchable(),

                Select::make('avenue_id')
                    ->label('Avenida')
                    ->options(CemeteryAvenue::all()->pluck('avenue_number', 'id'))
                    ->placeholder('Seleccione una avenida')
                    ->searchable(),
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
            ]);
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
            ->statePath('') // Esta línea es importante - no usar un statePath anidado
            ->columns(1);
    }

    public function search(): void
    {
        $data = $this->searchForm;

        // Verificar si se ha proporcionado al menos un criterio de búsqueda válido
        $hasCodeCriteria = !empty($this->nicheCode);
        $hasNameCriteria = !empty($this->deceasedName);
        $hasLocationCriteria = (!empty($this->street_id) || !empty($this->avenue_id));

        if (!$hasCodeCriteria && !$hasNameCriteria && !$hasLocationCriteria) {
            Notification::make()
                ->warning()
                ->title('Debe ingresar al menos un criterio de búsqueda')
                ->send();
            return;
        }

        $query = Niche::query()
            ->with(['contracts.deceased.person', 'contracts.responsible', 'contracts.status', 'type', 'status', 'street.block.section', 'avenue.block.section']);

        // Búsqueda por código de nicho
        if ($hasCodeCriteria) {
            $query->where('code', 'like', '%' . $this->nicheCode . '%');
        }

        // Búsqueda por nombre de fallecido
        if ($hasNameCriteria) {
            $query->whereHas('contracts.deceased.person', function (Builder $subQuery) {
                $subQuery->where('first_name', 'like', '%' . $this->deceasedName . '%')
                    ->orWhere('last_name', 'like', '%' . $this->deceasedName . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $this->deceasedName . '%']);
            });
        }

        // Búsqueda por ubicación (calle y/o avenida)
        if (!empty($this->street_id)) {
            $query->where('street_id', $this->street_id);
        }

        if (!empty($this->avenue_id)) {
            $query->where('avenue_id', $this->avenue_id);
        }

        // Ejecutar la consulta y almacenar resultados
        $this->searchResults = $query->limit(10)->get()->toArray();
        $this->hasSearched = true;
    }

    public function requestPayment(): void
{
    $this->validate([
        'selectedContractId' => 'required|exists:contracts,id',
        'requestReason' => 'required|string|min:3|max:255',
    ]);

    // Verificar que el contrato pertenece al usuario actual
    $contract = Contract::find($this->selectedContractId);
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

    try {
        DB::beginTransaction();

        // Obtener el status "No Pagado"
        $unpaidStatus = PaymentStatus::where('name', 'No Pagado')->first();

        if (!$unpaidStatus) {
            throw new \Exception("No se encontró el estado 'No Pagado' para pagos.");
        }

        // Generar número de recibo
        $lastReceipt = Payment::orderBy('id', 'desc')->first();
        $receiptNumber = $lastReceipt
            ? 'REC-' . str_pad((intval(substr($lastReceipt->receipt_number, 4)) + 1), 6, '0', STR_PAD_LEFT)
            : 'REC-000001';

        // Crear el pago
        $payment = Payment::create([
            'contract_id' => $contract->id,
            'receipt_number' => $receiptNumber,
            'amount' => 600.00, // Monto predeterminado
            'issue_date' => now(),
            'payment_date' => null,
            'payment_status_id' => $unpaidStatus->id,
            'receipt_file_path' => null,
            'notes' => $this->requestReason,
            'user_id' => Auth::id(),
        ]);

        // Crear notificación para el administrador
        \App\Models\Notification::create([
            'contract_id' => $contract->id,
            'sent_at' => now(),
            'message' => "Solicitud de boleta de pago: {$this->requestReason}",
            'is_sent' => true,
            'read_at' => null,
        ]);

        // Registrar en el log de cambios
        DB::table('change_logs')->insert([
            'table_name' => 'payments',
            'record_id' => $payment->id,
            'changed_field' => 'creación',
            'old_value' => 'Ninguno',
            'new_value' => "Nuevo pago registrado por monto: Q600.00",
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::commit();

        Notification::make()
            ->success()
            ->title('Solicitud de boleta enviada correctamente')
            ->body('Un administrador procesará su solicitud y generará la boleta de pago.')
            ->send();

        // Limpiar el formulario
        $this->selectedContractId = null;
        $this->requestReason = null;

    } catch (\Exception $e) {
        DB::rollBack();

        Notification::make()
            ->danger()
            ->title('Error al solicitar la boleta')
            ->body('Ha ocurrido un error al procesar su solicitud. Por favor, intente nuevamente.')
            ->send();

        \Illuminate\Support\Facades\Log::error('Error al crear solicitud de pago: ' . $e->getMessage());
    }
}

public function requestExhumation(): void
{
    $this->validate([
        'exhumationContractId' => 'required|exists:contracts,id',
        'exhumationReason' => 'required|string|min:10|max:500',
        'requestorName' => 'required|string|min:3|max:100',
        'requestorPhone' => 'required|string|min:8|max:20',
        'contactEmail' => 'required|email|max:100',
    ]);

    // Verificar que el contrato pertenece al usuario actual
    $contract = Contract::find($this->exhumationContractId);
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

    try {
        DB::beginTransaction();

        // Obtener estado "Solicitada" para exhumaciones
        $requestedStatus = \App\Models\ExhumationStatus::where('name', 'Solicitada')->first();

        if (!$requestedStatus) {
            throw new \Exception("No se encontró el estado 'Solicitada' para exhumaciones.");
        }

        // Generar un nombre de archivo para el documento de acuerdo
        $agreementFileName = 'solicitud_' . $contract->id . '_' . now()->format('YmdHis') . '.pdf';

        // Crear la exhumación
        $exhumation = \App\Models\Exhumation::create([
            'contract_id' => $contract->id,
            'requester_cui' => $user->cui, // Usamos el CUI del usuario actual
            'request_date' => now(),
            'exhumation_date' => null, // Se asignará cuando sea aprobada
            'reason' => $this->exhumationReason,
            'agreement_file_path' => 'exhumation_agreements/' . $agreementFileName,
            'exhumation_status_id' => $requestedStatus->id,
            'user_id' => Auth::id(),
            'notes' => "Solicitante: {$this->requestorName}, Teléfono: {$this->requestorPhone}, Email: {$this->contactEmail}",
        ]);

        // Crear notificación para el administrador
        \App\Models\Notification::create([
            'contract_id' => $contract->id,
            'sent_at' => now(),
            'message' => "Solicitud de exhumación: {$this->exhumationReason} - Contacto: {$this->requestorName} ({$this->requestorPhone})",
            'is_sent' => true,
            'read_at' => null,
        ]);

        // Registrar en el log de cambios
        DB::table('change_logs')->insert([
            'table_name' => 'exhumations',
            'record_id' => $exhumation->id,
            'changed_field' => 'creación',
            'old_value' => 'Ninguno',
            'new_value' => 'Nueva solicitud de exhumación registrada',
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::commit();

        Notification::make()
            ->success()
            ->title('Solicitud de exhumación enviada correctamente')
            ->body('Un administrador revisará su solicitud y se pondrá en contacto con usted.')
            ->send();

        // Limpiar el formulario
        $this->exhumationContractId = null;
        $this->exhumationReason = null;
        $this->requestorName = null;
        $this->requestorPhone = null;
        $this->contactEmail = null;

    } catch (\Exception $e) {
        DB::rollBack();

        Notification::make()
            ->danger()
            ->title('Error al solicitar la exhumación')
            ->body('Ha ocurrido un error al procesar su solicitud. Por favor, intente nuevamente.')
            ->send();

        \Illuminate\Support\Facades\Log::error('Error al crear solicitud de exhumación: ' . $e->getMessage());
    }
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
