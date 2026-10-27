<?php

namespace App\Http\Requests;

use App\Models\Docente;
use App\Support\InstitucionActual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'documento' => preg_replace('/\D/', '', (string) $this->documento),
            'cuil'      => $this->filled('cuil') ? preg_replace('/\D/', '', $this->cuil) : null,
            'email'     => $this->filled('email') ? mb_strtolower(trim($this->email)) : null,
        ]);
    }

    public function rules(): array
    {
        $docente = $this->route('docente');

        return [
            'apellido'         => ['required', 'string', 'max:80'],
            'nombre'           => ['required', 'string', 'max:80'],
            'documento'        => [
                'required', 'digits_between:7,8',
                Rule::unique('docentes', 'documento')->where('institucion_id', InstitucionActual::id())->ignore($docente?->id),
            ],
            'cuil'             => ['nullable', 'digits:11'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'telefono'         => ['nullable', 'string', 'max:40'],
            'email'            => [
                'nullable', 'email', 'max:150',
                // Si se crea la cuenta, el email no puede estar usado por otro usuario.
                Rule::when($this->boolean('crear_usuario'), [
                    Rule::unique('users', 'email')->ignore($docente?->user_id),
                ]),
            ],
            'domicilio'        => ['nullable', 'string', 'max:150'],
            'localidad'        => ['nullable', 'string', 'max:100'],
            'titulo'           => ['nullable', 'string', 'max:200'],
            'legajo'           => ['nullable', 'string', 'max:30'],
            'fecha_ingreso'    => ['nullable', 'date'],
            'estado'           => ['required', Rule::in(array_keys(Docente::ESTADOS))],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
            'crear_usuario'    => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento.unique' => 'Ya hay un docente con ese DNI en la institución.',
            'email.unique'     => 'Ese email ya lo usa otro usuario del sistema.',
        ];
    }

    public function attributes(): array
    {
        return [
            'documento'        => 'DNI',
            'cuil'             => 'CUIL',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'fecha_ingreso'    => 'fecha de ingreso',
            'titulo'           => 'título',
        ];
    }

    public function datosDocente(): array
    {
        return collect($this->validated())->except('crear_usuario')->all();
    }
}
