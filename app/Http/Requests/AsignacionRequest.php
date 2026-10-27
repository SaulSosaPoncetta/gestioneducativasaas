<?php

namespace App\Http\Requests;

use App\Models\Asignacion;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\EspacioCurricular;
use App\Models\PlanEstudio;
use App\Support\InstitucionActual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta de asignaciones: un docente en uno o varios espacios curriculares
 * de un curso (escolar) o de una carrera en una comisión (superior).
 */
class AsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('curso_id')) {
            $this->merge(['comision' => null]);
        } else {
            $this->merge(['comision' => trim((string) $this->comision) ?: 'Única']);
        }
    }

    public function rules(): array
    {
        $inst = InstitucionActual::id();

        return [
            'ciclo_lectivo_id' => ['required', 'integer', Rule::exists('ciclos_lectivos', 'id')->where('institucion_id', $inst)],
            'docente_id'       => ['required', 'integer', Rule::exists('docentes', 'id')->where('institucion_id', $inst)],
            'curso_id'         => ['nullable', 'integer', Rule::exists('cursos', 'id')->where('institucion_id', $inst)],
            'plan_estudio_id'  => ['required_without:curso_id', 'nullable', 'integer', Rule::exists('planes_estudio', 'id')->where('institucion_id', $inst)],
            'comision'         => ['nullable', 'string', 'max:30'],
            'espacios'         => ['required', 'array', 'min:1'],
            'espacios.*'       => ['integer'],
            'situacion'        => ['required', Rule::in(array_keys(Asignacion::SITUACIONES))],
            'fecha_desde'      => ['required', 'date'],
            'fecha_hasta'      => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'observaciones'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if (CicloLectivo::find($this->ciclo_lectivo_id)->estaCerrado()) {
                    $validator->errors()->add('ciclo_lectivo_id', 'El ciclo lectivo está cerrado.');
                    return;
                }

                $curso = $this->curso();

                if ($curso && $curso->ciclo_lectivo_id !== (int) $this->ciclo_lectivo_id) {
                    $validator->errors()->add('curso_id', 'El curso no es de ese ciclo lectivo.');
                    return;
                }

                if ($curso && ! $curso->plan_estudio_id) {
                    $validator->errors()->add('curso_id', 'El curso no tiene plan de estudio: asignáselo desde Cursos y divisiones.');
                    return;
                }

                $planId = $curso?->plan_estudio_id ?? (int) $this->plan_estudio_id;
                $validos = EspacioCurricular::where('plan_estudio_id', $planId)
                    ->when($curso, fn ($q) => $q->where('grado', $curso->grado))
                    ->pluck('id');

                if (collect($this->espacios)->map(fn ($id) => (int) $id)->diff($validos)->isNotEmpty()) {
                    $validator->errors()->add('espacios', 'Hay espacios curriculares que no corresponden al curso o la carrera.');
                }
            },
        ];
    }

    public function curso(): ?Curso
    {
        return $this->filled('curso_id') ? Curso::find($this->curso_id) : null;
    }

    public function messages(): array
    {
        return ['espacios.required' => 'Elegí al menos un espacio curricular.'];
    }

    public function attributes(): array
    {
        return [
            'docente_id'  => 'docente',
            'situacion'   => 'situación de revista',
            'fecha_desde' => 'desde',
            'fecha_hasta' => 'hasta',
            'comision'    => 'comisión',
        ];
    }
}
