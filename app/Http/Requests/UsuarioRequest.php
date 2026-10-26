<?php

namespace App\Http\Requests;

use App\Enums\Rol;
use App\Support\InstitucionActual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta / edición de usuarios de la institución actual.
 * Debe tener email o DNI (con cualquiera de los dos se ingresa).
 * En el alta, si no se escribe contraseña se usa el DNI.
 */
class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $dni = preg_replace('/\D/', '', (string) $this->dni);

        $this->merge([
            'dni'   => $dni === '' ? null : $dni,
            'email' => $this->filled('email') ? mb_strtolower(trim($this->email)) : null,
        ]);
    }

    public function rules(): array
    {
        $id   = $this->route('usuario')?->id;
        $alta = $id === null;

        return [
            'nombre'   => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'email'    => [
                'nullable', 'email', 'max:150', 'required_without:dni',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'dni' => [
                'nullable', 'digits_between:7,8', 'required_without:email',
                Rule::unique('users', 'dni')
                    ->where('institucion_id', InstitucionActual::id())
                    ->ignore($id),
            ],
            'rol'      => ['required', Rule::enum(Rol::class)->only(Rol::asignables())],
            'activo'   => ['nullable', 'boolean'],
            'password' => array_filter([
                'nullable', 'string', 'min:8', 'confirmed',
                $alta ? 'required_without:dni' : null,
            ]),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without'    => 'Ingresá un email o un DNI: con cualquiera de los dos se inicia sesión.',
            'dni.required_without'      => 'Ingresá un email o un DNI: con cualquiera de los dos se inicia sesión.',
            'dni.unique'                => 'Ya hay un usuario con ese DNI en la institución.',
            'password.required_without' => 'Sin DNI hay que escribir una contraseña inicial.',
        ];
    }

    public function attributes(): array
    {
        return ['dni' => 'DNI', 'rol' => 'rol', 'password' => 'contraseña'];
    }
}
