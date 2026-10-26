<?php

namespace App\Http\Requests;

use App\Models\Tutor;
use App\Support\InstitucionActual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos del tutor y, cuando se carga desde un estudiante, el vínculo
 * (parentesco, responsable principal, autorizado a retirar...).
 */
class TutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function conVinculo(): bool
    {
        return $this->route('estudiante') !== null;
    }

    /** Tutor que se edita o que ya existía (se encontró por DNI). */
    public function tutorExistente(): ?Tutor
    {
        if ($tutor = $this->route('tutor')) {
            return $tutor;
        }

        return $this->filled('tutor_id') ? Tutor::find($this->tutor_id) : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'documento' => preg_replace('/\D/', '', (string) $this->documento),
            'email'     => $this->filled('email') ? mb_strtolower(trim($this->email)) : null,
        ]);
    }

    public function rules(): array
    {
        $reglas = [
            'tutor_id'             => ['nullable', 'integer', Rule::exists('tutores', 'id')->where('institucion_id', InstitucionActual::id())],
            'apellido'             => ['required', 'string', 'max:80'],
            'nombre'               => ['required', 'string', 'max:80'],
            'documento'            => [
                'required', 'digits_between:7,8',
                Rule::unique('tutores', 'documento')
                    ->where('institucion_id', InstitucionActual::id())
                    ->ignore($this->tutorExistente()?->id),
            ],
            'telefono'             => ['nullable', 'string', 'max:40'],
            'telefono_alternativo' => ['nullable', 'string', 'max:40'],
            'email'                => ['nullable', 'email', 'max:150'],
            'ocupacion'            => ['nullable', 'string', 'max:100'],
            'domicilio'            => ['nullable', 'string', 'max:150'],
        ];

        if ($this->conVinculo()) {
            $reglas += [
                'vinculo'               => ['required', Rule::in(array_keys(Tutor::VINCULOS))],
                'es_responsable'        => ['nullable', 'boolean'],
                'autorizado_retirar'    => ['nullable', 'boolean'],
                'convive'               => ['nullable', 'boolean'],
                'recibe_notificaciones' => ['nullable', 'boolean'],
            ];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return ['documento.unique' => 'Ya hay un tutor con ese DNI: buscalo por DNI para vincularlo.'];
    }

    public function datosTutor(): array
    {
        return $this->only(['apellido', 'nombre', 'documento', 'telefono', 'telefono_alternativo', 'email', 'ocupacion', 'domicilio']);
    }

    public function datosVinculo(): array
    {
        return [
            'vinculo'               => $this->vinculo,
            'es_responsable'        => $this->boolean('es_responsable'),
            'autorizado_retirar'    => $this->boolean('autorizado_retirar'),
            'convive'               => $this->boolean('convive'),
            'recibe_notificaciones' => $this->boolean('recibe_notificaciones'),
        ];
    }

    public function attributes(): array
    {
        return [
            'documento'            => 'DNI',
            'telefono_alternativo' => 'teléfono alternativo',
            'vinculo'              => 'vínculo',
        ];
    }
}
