<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateContractStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-contract-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza automáticamente los estados de los contratos según las fechas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de estados de contratos...');

        $activeStatus = ContractStatus::where('name', 'Vigente')->first();
        $graceStatus = ContractStatus::where('name', 'En Gracia')->first();
        $expiredStatus = ContractStatus::where('name', 'Vencido')->first();

        if (!$activeStatus || !$graceStatus || !$expiredStatus) {
            $this->error('No se encontraron todos los estados de contrato necesarios.');
            return 1;
        }

        $today = Carbon::today();

        // 1. Actualizar contratos que han expirado (pasó fecha_fin pero aún están dentro del período de gracia)
        $contractsToGrace = Contract::where('contract_status_id', $activeStatus->id)
            ->where('end_date', '<', $today)
            ->where('grace_date', '>=', $today)
            ->get();

        foreach ($contractsToGrace as $contract) {
            $contract->update([
                'contract_status_id' => $graceStatus->id,
                'updated_at' => now(),
            ]);

            // Registro en change_logs
            DB::table('change_logs')->insert([
                'table_name' => 'contracts',
                'record_id' => $contract->id,
                'changed_field' => 'estado_del_contrato',
                'old_value' => $activeStatus->id,
                'new_value' => $graceStatus->id,
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info("Se actualizaron {$contractsToGrace->count()} contratos a estado 'En Gracia'.");

        // 2. Actualizar contratos que han pasado el período de gracia
        $contractsToExpired = Contract::where('contract_status_id', $graceStatus->id)
            ->where('grace_date', '<', $today)
            ->get();

        foreach ($contractsToExpired as $contract) {
            $contract->update([
                'contract_status_id' => $expiredStatus->id,
                'updated_at' => now(),
            ]);

            // Registro en change_logs
            DB::table('change_logs')->insert([
                'table_name' => 'contracts',
                'record_id' => $contract->id,
                'changed_field' => 'estado_del_contrato',
                'old_value' => $graceStatus->id,
                'new_value' => $expiredStatus->id,
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info("Se actualizaron {$contractsToExpired->count()} contratos a estado 'Vencido'.");

        $this->info('Actualización de estados de contratos completada con éxito.');

        return 0;
    }
}
