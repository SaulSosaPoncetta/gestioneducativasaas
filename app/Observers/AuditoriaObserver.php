<?php

namespace App\Observers;

use App\Models\Auditoria;
use App\Models\Institucion;
use App\Models\User;
use App\Support\Auditoria\Etiqueta;
use Illuminate\Database\Eloquent\Model;

/**
 * Registra altas, modificaciones y bajas de los modelos de Auditoria::MODELOS.
 * Guarda solo los campos que cambiaron; nunca contraseñas ni tokens.
 */
class AuditoriaObserver
{
    /** Campos que no se guardan (ruido o datos sensibles). */
    private const IGNORADOS = ['created_at', 'updated_at', 'remember_token', 'ultimo_acceso_at', 'api_token_creado_at'];

    /** Campos que se registran como "cambió" sin mostrar el valor. */
    private const OCULTOS = ['password', 'api_token'];

    public function created(Model $modelo): void
    {
        $this->registrar('creado', $modelo, null, $this->limpiar($modelo->getAttributes()));
    }

    public function updated(Model $modelo): void
    {
        $cambios = $this->limpiar($modelo->getChanges());

        if (! $cambios) {
            return;
        }

        $antes = [];
        foreach (array_keys($cambios) as $campo) {
            $antes[$campo] = in_array($campo, self::OCULTOS, true) ? '(oculto)' : $modelo->getRawOriginal($campo);
        }

        $this->registrar('actualizado', $modelo, $antes, $cambios);
    }

    public function deleted(Model $modelo): void
    {
        $this->registrar('eliminado', $modelo, $this->limpiar($modelo->getAttributes()), null);
    }

    private function registrar(string $evento, Model $modelo, ?array $antes, ?array $despues): void
    {
        [$etiqueta, $eventos] = Auditoria::MODELOS[$modelo::class] ?? [class_basename($modelo), []];

        if (! Auditoria::$activa || ! in_array($evento, $eventos, true)) {
            return;
        }

        $institucionId = $modelo instanceof Institucion ? $modelo->id : ($modelo->getAttribute('institucion_id') ?? null);

        Auditoria::registrar($evento, $etiqueta . ': ' . Etiqueta::de($modelo), [
            'institucion_id' => $institucionId,
            'modelo_tipo'    => $modelo::class,
            'modelo_id'      => $modelo->getKey(),
            'antes'          => $antes,
            'despues'        => $despues,
        ]);
    }

    private function limpiar(array $datos): array
    {
        $datos = array_diff_key($datos, array_flip(self::IGNORADOS));

        foreach (self::OCULTOS as $campo) {
            if (array_key_exists($campo, $datos)) {
                $datos[$campo] = '(oculto)';
            }
        }

        // Los JSON se guardan decodificados para mostrarlos legibles.
        foreach ($datos as $k => $v) {
            if (is_string($v) && strlen($v) > 1 && in_array($v[0], ['{', '['], true)) {
                $json = json_decode($v, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $datos[$k] = $json;
                }
            }
            if (is_string($v) && mb_strlen($v) > 2000) {
                $datos[$k] = mb_substr($v, 0, 2000) . '…';
            }
        }

        return $datos;
    }
}
