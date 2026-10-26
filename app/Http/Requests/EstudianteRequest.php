<?php

namespace App\Http\Requests;

use App\Models\Estudiante;
use App\Support\InstitucionActual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $documento = (string) $this->documento;

        $this->merge([
            'documento' => $this->tipo_documento === 'dni' ? preg_replace('/\D/', '', $documento) : strtoupper(trim($documento)),
            'cuil'      => $this->filled('cuil') ? preg_replace('/\D/', '', $this->cuil) : null,
            'email'     => $this->filled('email') ? mb_strtolower(trim($this->email)) : null,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('estudiante')?->id;

        return [
            'apellido'         => ['required', 'string', 'max:80'],
            'nombre'           => ['required', 'string', 'max:80'],
            'tipo_documento'   => ['required', Rule::in(array_keys(Estudiante::TIPOS_DOCUMENTO))],
            'documento'        => [
                'required', 'string', 'max:20',
                Rule::when($this->tipo_documento === 'dni', ['digits_between:7,8']),
                Rule::unique('estudiantes', 'documento')
                    ->where('institucion_id', InstitucionActual::id())
                    ->where('tipo_documento', $this->tipo_documento)
                    ->ignore($id),
            ],
            'cuil'             => ['nullable', 'digits:11'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'genero'           => ['nullable', Rule::in(array_keys(Estudiante::GENEROS))],
            'nacionalidad'     => ['required', 'string', 'max:60'],
            'lugar_nacimiento' => ['nullable', 'string', 'max:100'],
            'domicilio'        => ['nullable', 'string', 'max:150'],
            'localidad'        => ['nullable', 'string', 'max:100'],
            'telefono'         => ['nullable', 'string', 'max:40'],
            'email'            => ['nullable', 'email', 'max:150'],
            'fecha_ingreso'    => ['nullable', 'date'],
            'estado'           => ['required', Rule::in(array_keys(Estudiante::ESTADOS))],

            'grupo_sanguineo'     => ['nullable', Rule::in(Estudiante::GRUPOS_SANGUINEOS)],
            'obra_social'         => ['nullable', 'string', 'max:100'],
            'numero_afiliado'     => ['nullable', 'string', 'max:40'],
            'salud_observaciones' => ['nullable', 'string', 'max:2000'],
            'emergencia_contacto' => ['nullable', 'string', 'max:120'],
            'emergencia_telefono' => ['nullable', 'string', 'max:40'],
            'observaciones'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return ['documento.unique' => 'Ya hay un estudiante con ese documento en la institución.'];
    }

    public function attributes(): array
    {
        return [
            'tipo_documento'      => 'tipo de documento',
            'documento'           => 'número de documento',
            'cuil'                => 'CUIL',
            'fecha_nacimiento'    => 'fecha de nacimiento',
            'fecha_ingreso'       => 'fecha de ingreso',
            'salud_observaciones' => 'observaciones de salud',
        ];
    }
}
