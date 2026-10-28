<?php

namespace App\Http\Requests;

use App\Models\Comunicado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComunicadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requiere_confirmacion' => $this->boolean('requiere_confirmacion'),
            'enviar_email'          => $this->boolean('enviar_email'),
            'alcance_ids'           => $this->input('alcance') === 'institucion'
                ? []
                : array_values(array_map('intval', (array) $this->input('ids.' . $this->input('alcance'), []))),
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo'                => ['required', 'string', 'max:150'],
            'cuerpo'                => ['required', 'string', 'max:20000'],
            'alcance'               => ['required', Rule::in(array_keys(Comunicado::ALCANCES))],
            'alcance_ids'           => ['array', Rule::requiredIf($this->input('alcance') !== 'institucion')],
            'para'                  => ['required', 'array', 'min:1'],
            'para.*'                => [Rule::in(array_keys(Comunicado::PARA))],
            'requiere_confirmacion' => ['boolean'],
            'enviar_email'          => ['boolean'],
            'adjunto'               => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,odt,ods'],
            'quitar_adjunto'        => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'alcance_ids.required' => 'Elegí al menos un destino (nivel, curso o carrera).',
            'para.required'        => 'Elegí a quiénes va dirigido (familias, estudiantes o docentes).',
            'adjunto.max'          => 'El adjunto no puede superar los 10 MB.',
        ];
    }
}
