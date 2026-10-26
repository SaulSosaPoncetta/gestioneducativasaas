<?php

namespace App\Http\Requests;

use App\Models\EspacioCurricular;
use App\Models\PlanEstudio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta / edición de un espacio curricular y sus correlatividades.
 * Correlatividades solo en carreras: la correlativa tiene que dictarse antes.
 */
class EspacioCurricularRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function plan(): PlanEstudio
    {
        return $this->route('plan') ?? $this->route('espacio')->plan;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->plan()->esCarrera()) {
            $this->merge(['regimen' => 'anual', 'correlativas' => []]);
        }

        if (! $this->plan()->nivel->nivel->usaCamposFormacion()) {
            $this->merge(['campo' => null]);
        }
    }

    public function rules(): array
    {
        $plan = $this->plan();

        return [
            'nombre'          => ['required', 'string', 'max:150'],
            'codigo'          => ['nullable', 'string', 'max:20'],
            'grado'           => ['required', 'integer', Rule::in($plan->grados())],
            'regimen'         => ['required', Rule::in(array_keys(EspacioCurricular::REGIMENES))],
            'formato'         => ['required', Rule::in(array_keys(EspacioCurricular::FORMATOS))],
            'campo'           => ['nullable', Rule::in(array_keys(EspacioCurricular::CAMPOS))],
            'horas_semanales' => ['nullable', 'integer', 'between:1,40'],
            'orden'           => ['nullable', 'integer', 'between:0,999'],
            'correlativas'    => ['nullable', 'array'],
            'correlativas.*'  => ['array'],
            'correlativas.*.*' => ['integer'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $tipos = array_keys((array) $this->input('correlativas', []));

                if (array_diff($tipos, array_keys(EspacioCurricular::CORRELATIVIDADES))) {
                    $validator->errors()->add('correlativas', 'Tipo de correlatividad inválido.');
                    return;
                }

                // Espacio "virtual" con los datos nuevos para comparar el orden de dictado.
                $actual = new EspacioCurricular([
                    'plan_estudio_id' => $this->plan()->id,
                    'grado'           => (int) $this->grado,
                    'regimen'         => $this->regimen,
                ]);
                $actual->id = $this->route('espacio')?->id;

                $ids = collect($this->correlativas())->flatten()->unique();
                $requeridos = EspacioCurricular::where('plan_estudio_id', $this->plan()->id)->whereIn('id', $ids)->get();

                if ($requeridos->count() !== $ids->count()) {
                    $validator->errors()->add('correlativas', 'Hay correlativas que no son de este plan.');
                    return;
                }

                $invalidas = $requeridos->reject(fn ($r) => $r->esAnteriorA($actual));

                if ($invalidas->isNotEmpty()) {
                    $validator->errors()->add(
                        'correlativas',
                        'Una correlativa tiene que dictarse antes: ' . $invalidas->pluck('nombre')->join(', ') . '.'
                    );
                }
            },
        ];
    }

    /** @return array<string, int[]> */
    public function correlativas(): array
    {
        return collect((array) $this->input('correlativas', []))
            ->map(fn ($ids) => array_values(array_unique(array_map('intval', (array) $ids))))
            ->filter()
            ->all();
    }

    public function datosEspacio(): array
    {
        return $this->only(['nombre', 'codigo', 'grado', 'regimen', 'formato', 'campo', 'horas_semanales'])
            + ['orden' => (int) $this->orden];
    }

    public function attributes(): array
    {
        return [
            'grado'           => 'año',
            'regimen'         => 'régimen',
            'campo'           => 'campo de formación',
            'horas_semanales' => 'horas semanales',
            'codigo'          => 'código',
        ];
    }
}
