<?php

namespace App\Http\Requests;

use App\Models\Curso;
use App\Models\InstitucionNivel;
use App\Models\PlanEstudio;
use App\Support\InstitucionActual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta (una o varias divisiones a la vez) y edición de cursos.
 * En la edición el ciclo y el nivel no cambian: se toman del curso.
 */
class CursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function esAlta(): bool
    {
        return $this->route('curso') === null;
    }

    protected function prepareForValidation(): void
    {
        if ($curso = $this->route('curso')) {
            $this->merge([
                'ciclo_lectivo_id'     => $curso->ciclo_lectivo_id,
                'institucion_nivel_id' => $curso->institucion_nivel_id,
            ]);
        }
    }

    public function rules(): array
    {
        $institucion = InstitucionActual::id();

        return [
            'ciclo_lectivo_id' => [
                'required', 'integer',
                Rule::exists('ciclos_lectivos', 'id')->where('institucion_id', $institucion),
            ],
            'institucion_nivel_id' => [
                'required', 'integer',
                Rule::exists('institucion_niveles', 'id')->where('institucion_id', $institucion)->where('activo', true),
            ],
            'grado'      => ['required', 'integer', 'between:1,10'],
            'divisiones' => [$this->esAlta() ? 'required' : 'exclude', 'string', 'max:150'],
            'division'   => [$this->esAlta() ? 'exclude' : 'required', 'string', 'max:20'],
            'turno'      => ['required', Rule::in(array_keys(Curso::TURNOS))],
            'preceptor_id' => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where('institucion_id', $institucion)->where('rol', 'preceptor'),
            ],
            'plan_estudio_id' => [
                'nullable', 'integer',
                Rule::exists('planes_estudio', 'id')->where('institucion_id', $institucion),
            ],
            'aula' => ['nullable', 'string', 'max:40'],
            'cupo' => ['nullable', 'integer', 'between:1,999'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $nivel = InstitucionNivel::find($this->institucion_nivel_id);

                if (! $nivel->nivel->usaCursos()) {
                    $validator->errors()->add('institucion_nivel_id', 'Este nivel se organiza por carreras, no por cursos.');
                    return;
                }

                if (! in_array((int) $this->grado, $nivel->nivel->grados($nivel->duracion), true)) {
                    $validator->errors()->add('grado', 'El ' . mb_strtolower($nivel->nivel->unidadCurso()) . ' elegido no corresponde al nivel.');
                }

                if ($this->filled('plan_estudio_id')) {
                    $plan = PlanEstudio::find($this->plan_estudio_id);

                    if ($plan->institucion_nivel_id !== $nivel->id
                        || $this->grado < $plan->grado_desde || $this->grado > $plan->grado_hasta) {
                        $validator->errors()->add('plan_estudio_id', 'El plan elegido no corresponde a ese nivel y año.');
                    }
                }

                $divisiones = $this->divisiones();

                if (! $divisiones) {
                    $validator->errors()->add($this->esAlta() ? 'divisiones' : 'division', 'Indicá al menos una división.');
                    return;
                }

                $repetidas = Curso::where('ciclo_lectivo_id', $this->ciclo_lectivo_id)
                    ->where('institucion_nivel_id', $nivel->id)
                    ->where('grado', $this->grado)
                    ->whereIn('division', $divisiones)
                    ->when($this->route('curso'), fn ($q, $curso) => $q->whereKeyNot($curso->id))
                    ->pluck('division');

                if ($repetidas->isNotEmpty()) {
                    $validator->errors()->add(
                        $this->esAlta() ? 'divisiones' : 'division',
                        'Ya existe: ' . $nivel->nivel->etiquetaGrado((int) $this->grado) . ' ' . $repetidas->join(', ') . '.'
                    );
                }
            },
        ];
    }

    /**
     * Divisiones a crear. En el alta se aceptan varias separadas por coma
     * ("A, B, C" o "Roja, Verde"); en la edición, una sola.
     *
     * @return string[]
     */
    public function divisiones(): array
    {
        $texto = $this->esAlta() ? (string) $this->divisiones : (string) $this->division;

        return collect(preg_split('/[,;]+/', $texto))
            ->map(fn ($d) => trim($d))
            ->filter(fn ($d) => $d !== '' && mb_strlen($d) <= 20)
            ->map(fn ($d) => mb_strlen($d) <= 2 ? mb_strtoupper($d) : mb_convert_case($d, MB_CASE_TITLE))
            ->unique()
            ->values()
            ->all();
    }

    public function datosCurso(): array
    {
        return $this->only(['ciclo_lectivo_id', 'institucion_nivel_id', 'plan_estudio_id', 'grado', 'turno', 'preceptor_id', 'aula', 'cupo']);
    }

    public function attributes(): array
    {
        return [
            'institucion_nivel_id' => 'nivel',
            'ciclo_lectivo_id'     => 'ciclo lectivo',
            'grado'                => 'año / grado / sala',
            'preceptor_id'         => 'preceptor',
            'plan_estudio_id'      => 'plan de estudio',
            'divisiones'           => 'divisiones',
            'division'             => 'división',
        ];
    }
}
