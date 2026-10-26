<?php

namespace App\Http\Requests;

use App\Enums\NivelEducativo;
use App\Models\Institucion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Datos de institución + niveles.
 * - Alta (superadmin): además pide el administrador inicial.
 * - Estado activo: solo lo cambia el superadmin.
 */
class InstitucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function esAlta(): bool
    {
        return $this->routeIs('instituciones.store');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cue'  => $this->soloDigitos($this->cue),
            'cuit' => $this->soloDigitos($this->cuit),
        ]);

        if ($this->esAlta()) {
            $admin = (array) $this->input('admin', []);
            $admin['dni']   = $this->soloDigitos($admin['dni'] ?? null);
            $admin['email'] = isset($admin['email']) ? mb_strtolower(trim($admin['email'])) : null;
            $this->merge(['admin' => $admin]);
        }
    }

    public function rules(): array
    {
        $reglas = [
            'nombre'       => ['required', 'string', 'max:150'],
            'nombre_corto' => ['nullable', 'string', 'max:60'],
            'cue'          => ['nullable', 'digits_between:7,9'],
            'cuit'         => ['nullable', 'digits:11'],
            'tipo_gestion' => ['required', Rule::in(['estatal', 'privada'])],
            'provincia'    => ['required', Rule::in(Institucion::PROVINCIAS)],
            'localidad'    => ['nullable', 'string', 'max:100'],
            'domicilio'    => ['nullable', 'string', 'max:150'],
            'telefono'     => ['nullable', 'string', 'max:40'],
            'email'        => ['nullable', 'email', 'max:150'],
            'logo'         => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'quitar_logo'  => ['nullable', 'boolean'],

            'niveles'                => ['required', 'array'],
            'niveles.*.activo'       => ['nullable', 'boolean'],
            'niveles.*.denominacion' => ['nullable', 'string', 'max:150'],
            'niveles.*.cue_anexo'    => ['nullable', 'digits_between:7,9'],
            'niveles.*.duracion'     => ['nullable', 'integer', 'between:1,10'],
        ];

        if ($this->user()->esSuperAdmin()) {
            $reglas['activo'] = ['nullable', 'boolean'];
        }

        if ($this->esAlta()) {
            $reglas += [
                'admin.nombre'   => ['required', 'string', 'max:80'],
                'admin.apellido' => ['required', 'string', 'max:80'],
                'admin.email'    => ['required', 'email', 'max:150', 'unique:users,email'],
                'admin.dni'      => ['nullable', 'digits_between:7,8'],
                'admin.password' => ['required', 'string', 'min:8'],
            ];
        }

        return $reglas;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $niveles = (array) $this->input('niveles', []);
                $validos = array_column(NivelEducativo::cases(), 'value');

                if (array_diff(array_keys($niveles), $validos)) {
                    $validator->errors()->add('niveles', 'Se envió un nivel que no existe.');
                }

                $activos = array_filter($niveles, fn ($n) => ! empty($n['activo']));

                if (! $activos) {
                    $validator->errors()->add('niveles', 'Elegí al menos un nivel educativo.');
                }
            },
        ];
    }

    /** Datos de la institución listos para guardar. */
    public function datosInstitucion(): array
    {
        $campos = ['nombre', 'nombre_corto', 'cue', 'cuit', 'tipo_gestion', 'provincia',
                   'localidad', 'domicilio', 'telefono', 'email'];

        $datos = $this->only($campos);

        if ($this->user()->esSuperAdmin()) {
            $datos['activo'] = $this->boolean('activo', $this->esAlta());
        }

        return $datos;
    }

    public function attributes(): array
    {
        return [
            'nombre_corto'           => 'nombre corto',
            'cue'                    => 'CUE',
            'cuit'                   => 'CUIT',
            'tipo_gestion'           => 'tipo de gestión',
            'niveles.*.denominacion' => 'denominación del nivel',
            'niveles.*.cue_anexo'    => 'CUE anexo',
            'niveles.*.duracion'     => 'duración',
            'admin.nombre'           => 'nombre del administrador',
            'admin.apellido'         => 'apellido del administrador',
            'admin.email'            => 'email del administrador',
            'admin.dni'              => 'DNI del administrador',
            'admin.password'         => 'contraseña del administrador',
        ];
    }

    protected function soloDigitos(?string $valor): ?string
    {
        $valor = preg_replace('/\D/', '', (string) $valor);

        return $valor === '' ? null : $valor;
    }
}
