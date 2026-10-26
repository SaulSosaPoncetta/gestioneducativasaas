<?php

namespace App\Http\Requests;

use App\Enums\EsquemaEvaluacion;
use App\Models\InstitucionNivel;
use App\Support\InstitucionActual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta / edición de ciclo lectivo.
 * En el alta también llega la configuración de cada nivel (esquema de
 * evaluación, intensificación y si se copian los cursos del ciclo anterior).
 */
class CicloLectivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function esAlta(): bool
    {
        return $this->routeIs('ciclos.store');
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('nombre') && $this->filled('anio')) {
            $this->merge(['nombre' => 'Ciclo lectivo ' . $this->anio]);
        }
    }

    public function rules(): array
    {
        $reglas = [
            'anio' => [
                'required', 'integer', 'between:2000,2100',
                Rule::unique('ciclos_lectivos', 'anio')
                    ->where('institucion_id', InstitucionActual::id())
                    ->ignore($this->route('ciclo')?->id),
            ],
            'nombre'       => ['required', 'string', 'max:80'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after:fecha_inicio'],
        ];

        if ($this->esAlta()) {
            $reglas += [
                'niveles'                   => ['required', 'array'],
                'niveles.*.esquema'         => ['required', Rule::enum(EsquemaEvaluacion::class)],
                'niveles.*.intensificacion' => ['nullable', 'boolean'],
                'niveles.*.copiar_cursos'   => ['nullable', 'boolean'],
            ];
        }

        return $reglas;
    }

    public function after(): array
    {
        if (! $this->esAlta()) {
            return [];
        }

        return [
            function (Validator $validator) {
                $validos = InstitucionNivel::where('activo', true)->pluck('id')->all();
                $enviados = array_map('intval', array_keys((array) $this->input('niveles', [])));

                if (array_diff($enviados, $validos)) {
                    $validator->errors()->add('niveles', 'Se envió un nivel que no pertenece a la institución.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'anio.unique'    => 'Ya existe un ciclo lectivo para ese año.',
            'fecha_fin.after' => 'La fecha de fin tiene que ser posterior a la de inicio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'anio'              => 'año',
            'fecha_inicio'      => 'inicio de clases',
            'fecha_fin'         => 'fin de clases',
            'niveles.*.esquema' => 'esquema de evaluación',
        ];
    }

    public function datosCiclo(): array
    {
        return $this->only(['anio', 'nombre', 'fecha_inicio', 'fecha_fin']);
    }
}
