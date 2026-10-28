<?php

namespace App\Console\Commands;

use App\Models\Auditoria;
use Illuminate\Console\Command;

class DepurarAuditoria extends Command
{
    protected $signature = 'auditoria:depurar {--meses=24 : Conserva los registros de los últimos N meses}';

    protected $description = 'Borra registros de auditoría más viejos que el período indicado';

    public function handle(): int
    {
        $meses = max(1, (int) $this->option('meses'));
        $limite = now()->subMonths($meses);

        $total = 0;
        do {
            $borrados = Auditoria::sinInstitucion()->where('created_at', '<', $limite)->limit(5000)->delete();
            $total += $borrados;
        } while ($borrados > 0);

        $this->info("Se borraron {$total} registros anteriores al {$limite->format('d/m/Y')}.");

        return self::SUCCESS;
    }
}
