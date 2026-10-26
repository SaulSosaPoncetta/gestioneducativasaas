<?php

namespace App\Http\Requests;

use App\Models\InstitucionNivel;
use App\Models\PlanEstudio;
use App\Support\InstitucionActual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta / edición de planes de estudio.
 * - Niveles escolares: rango de años/grados (ej. ciclo superior 4° a 6°).
 * - Carreras: duración en años (de 1° a N°).
 * En la edición el nivel no cambia.
 */
class PlanEstudioRequest extends FormRequest
{
    protected ?InstitucionNivel $nivelElegido = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($plan = $this->route('plan')) {
            $this->merge(['institucion_nivel_id' => $plan->institucion_nivel_id]);
        }

        $nivel = $this->nivel();

        if ($nivel?->nivel->usaCarreras()) {
            $this->merge([
                'tipo'        => 'carrera',
                'grado_desde' => 1,
                'grado_hasta' => (int) $this->duracion,
            ]);
        } else {
            $this->merge(['modalidad' => null]);
        }
    }

    public function nivel(): ?InstitucionNivel
    {
        return $this->nivelElegido ??= InstitucionNivel::find($this->institucion_nivel_id);
    }

    public function rules(): array
    {
        $nivel = $this->nivel();

        $reglas = [
            'institucion_nivel_id' => [
                'required', 'integer',
                Rule::exists('institucion_niveles', 'id')->where('institucion_id', InstitucionActual::id()),
            ],
            'tipo'        => ['required', Rule::in(array_keys($nivel?->nivel->tiposPlan() ?? []))],
            'nombre'      => ['required', 'string', 'max:150'],
            'titulo'      => ['nullable', 'string', 'max:200'],
            'resolucion'  => ['nullable', 'string', 'max:100'],
            'modalidad'   => ['nullable', Rule::in(array_keys(PlanEstudio::MODALIDADES))],
            'activo'      => ['nullable', 'boolean'],
            'grado_desde' => ['required', 'integer', 'min:1'],
            'grado_hasta' => ['required', 'integer', 'gte:grado_desde'],
        ];

        if ($nivel?->nivel->usaCarreras()) {
            $reglas['duracion'] = ['required', 'integer', 'between:1,8'];
        } elseif ($nivel) {
            $grados = $nivel->nivel->grados($nivel->duracion);
            $reglas['grado_desde'][] = Rule::in($grados);
            $reglas['grado_hasta'][] = Rule::in($grados);
        }

        return $reglas;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $plan = $this->route('plan');

                if (! $plan || $validator->errors()->isNotEmpty()) {
                    return;
                }

                // No achicar el plan dejando espacios curriculares afuera.
                $fuera = $plan->espacios()
                    ->where(fn ($q) => $q->where('grado', '<', $this->grado_desde)->orWhere('grado', '>', $this->grado_hasta))
                    ->count();

                if ($fuera) {
                    $validator->errors()->add(
                        $plan->esCarrera() ? 'duracion' : 'grado_hasta',
                        "Hay {$fuera} espacios curriculares fuera del nuevo rango. Movelos o eliminalos primero."
                    );
                }
            },
        ];
    }

    public function datosPlan(): array
    {
        return $this->only(['institucion_nivel_id', 'tipo', 'nombre', 'titulo', 'resolucion', 'modalidad', 'grado_desde', 'grado_hasta'])
            + ['activo' => $this->boolean('activo')];
    }

    public function attributes(): array
    {
        return [
            'institucion_nivel_id' => 'nivel',
            'grado_desde'          => 'desde',
            'grado_hasta'          => 'hasta',
            'titulo'               => 'título que otorga',
            'resolucion'           => 'resolución',
            'duracion'             => 'duración',
        ];
    }
}
