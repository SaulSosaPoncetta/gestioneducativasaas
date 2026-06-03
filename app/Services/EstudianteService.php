<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Dni;
use App\Models\Telefono;
use App\Models\Localidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class EstudianteService
{
    /**
     * Buscar estudiantes con filtros opcionales.
     * Filtros: nombre_apellido, dni, anio (cohorte), padron (id_usuario).
     */
    public function buscar(array $filtros): LengthAwarePaginator
    {
        $query = Estudiante::with([
            'usuario.localidad',
            'usuario.rol',
        ])
        ->join('usuarios', 'estudiantes.id_usuario', '=', 'usuarios.id_usuario')
        ->whereNull('usuarios.deleted_at')
        ->select('estudiantes.*');

        // Filtro por nombre o apellido
        if (!empty($filtros['nombre_apellido'])) {
            $busqueda = '%' . $filtros['nombre_apellido'] . '%';
            $query->where(function ($q) use ($busqueda) {
                $q->where('usuarios.nombre', 'like', $busqueda)
                  ->orWhere('usuarios.apellido', 'like', $busqueda)
                  ->orWhereRaw("(usuarios.nombre || ' ' || usuarios.apellido) like ?", [$busqueda]);
            });
        }

        // Filtro por DNI
        if (!empty($filtros['dni'])) {
            $query->join('dnis', 'estudiantes.id_usuario', '=', 'dnis.id_usuario')
                  ->where('dnis.dni', 'like', '%' . $filtros['dni'] . '%');
        }

        // Filtro por año (cohorte en estudiante_carrera)
        if (!empty($filtros['anio'])) {
            $query->join('estudiante_carrera', 'estudiantes.id_usuario', '=', 'estudiante_carrera.id_usuario')
                  ->whereYear('estudiante_carrera.cohorte', $filtros['anio']);
        }

        // Filtro por padrón (id_usuario)
        if (!empty($filtros['padron'])) {
            $query->where('estudiantes.id_usuario', $filtros['padron']);
        }

        return $query->orderBy('usuarios.apellido')->paginate(15);
    }

    /**
     * Obtener un estudiante por su id_usuario con todas sus relaciones.
     */
    public function getById(int $idUsuario): Estudiante
    {
        return Estudiante::with([
            'usuario.localidad.pais',
            'usuario.rol',
        ])
        ->where('id_usuario', $idUsuario)
        ->firstOrFail();
    }

    /**
     * Obtener DNI principal del estudiante (tipo_dni = 'DNI' o el primero disponible).
     */
    public function getDni(int $idUsuario): ?string
    {
        $registro = Dni::where('id_usuario', $idUsuario)
            ->orderByRaw("CASE WHEN tipo_dni = 'DNI' THEN 0 ELSE 1 END")
            ->first();

        return $registro?->dni;
    }

    /**
     * Obtener teléfonos del estudiante.
     * Retorna ['celular' => ..., 'secundario' => ...]
     */
    public function getTelefonos(int $idUsuario): array
    {
        $telefonos = Telefono::where('id_usuario', $idUsuario)->get();

        return [
            'celular'    => $telefonos->get(0)?->telefono,
            'secundario' => $telefonos->get(1)?->telefono,
        ];
    }

    /**
     * Obtener todas las localidades para los selects del formulario.
     */
    public function getLocalidades(): \Illuminate\Database\Eloquent\Collection
    {
        return Localidad::with('pais')->orderBy('nombre_localidad')->get();
    }

    /**
     * Actualizar los datos de un estudiante.
     *
     * @param array $data {
     *   nombre, apellido, email, nombre_usuario, password (opcional),
     *   id_localidad_domicilio, telefono_celular (opcional), telefono_secundario (opcional)
     * }
     */
    public function update(int $idUsuario, array $data): Estudiante
    {
        return DB::transaction(function () use ($idUsuario, $data) {

            $estudiante = Estudiante::where('id_usuario', $idUsuario)->firstOrFail();
            $usuario    = $estudiante->usuario;

            // Campos del usuario
            $camposUsuario = array_filter([
                'nombre'         => $data['nombre']         ?? null,
                'apellido'       => $data['apellido']       ?? null,
                'email'          => $data['email']          ?? null,
                'nombre_usuario' => $data['nombre_usuario'] ?? null,
                'id_localidad'   => $data['id_localidad_domicilio'] ?? null,
            ], fn($v) => !is_null($v));

            if (!empty($data['password'])) {
                $camposUsuario['password_hash'] = Hash::make($data['password']);
            }

            if (!empty($camposUsuario)) {
                $usuario->update($camposUsuario);
            }

            // Actualizar teléfono celular (primer registro)
            if (array_key_exists('telefono_celular', $data)) {
                $this->upsertTelefono($idUsuario, $data['telefono_celular'], 0);
            }

            // Actualizar teléfono secundario (segundo registro)
            if (array_key_exists('telefono_secundario', $data)) {
                $this->upsertTelefono($idUsuario, $data['telefono_secundario'], 1);
            }

            return $estudiante->load(['usuario.localidad.pais', 'usuario.rol']);
        });
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Insertar o actualizar un teléfono por posición (0 = celular, 1 = secundario).
     */
private function upsertTelefono(int $idUsuario, ?string $numero, int $posicion): void
{
    $telefonos = Telefono::where('id_usuario', $idUsuario)
        ->get()
        ->values(); // reindexar 0, 1, 2...

    if ($telefonos->has($posicion)) {
        $telefonoActual = $telefonos->get($posicion)->telefono;

        // Si el número no cambió, no hacer nada
        if ($numero === $telefonoActual) {
            return;
        }

        // Borramos el registro actual
        Telefono::where('id_usuario', $idUsuario)
            ->where('telefono', $telefonoActual)
            ->delete();

        // Si hay número nuevo, lo insertamos
        if ($numero) {
            Telefono::create([
                'id_usuario' => $idUsuario,
                'telefono'   => $numero,
            ]);
        }
    } elseif ($numero) {
        Telefono::create([
            'id_usuario' => $idUsuario,
            'telefono'   => $numero,
        ]);
    }
}
}
