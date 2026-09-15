<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Estudiante;
use App\Models\EstudianteCarrera;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\NotaCuatrimestre;
use App\Models\MesaExamen;
use App\Models\TipoMesaExamen;
use App\Models\Llamado;
use App\Models\CicloLectivo;
use App\Models\Administrador;
use Carbon\Carbon;

class EstudianteFinalesSeeder extends Seeder
{
    /**
     * Requiere que UsuarioAdminSeeder ya haya corrido antes (necesita el
     * usuario 'alu@a.com' con su fila en estudiantes, y un administrador
     * para crear la mesa de examen).
     *
     * Deja al alumno "alu" en condiciones de:
     *   - Verse listado con una carrera activa (tab "Carrera" / selector).
     *   - Tener UNA materia con la cursada REGULARIZADA (no promocionada),
     *     que es la condición mínima que pide
     *     EstudianteFinalesService::resolverFilaInscripcion() para poder
     *     rendir el final (situacionCursada === 'regular').
     *   - Tener una mesa HABILITADA y con fecha FUTURA para esa materia,
     *     así aparece un botón de fecha en /finalesInscripcion.
     *
     * OJO - supuestos sobre columnas que no tengo a la vista (ajustar si
     * no coinciden con tus migraciones reales):
     *   - estados_carrera: id 4 = 'Activa' (así lo asume el service).
     *   - materias.nota_aprobada / materias.nota_promocionable existen y
     *     son numéricas (confirmado por el service).
     *   - notas_cuatrimestre: id_usuario, id_materia, valor, fecha_carga.
     *   - mesas_examen: id_materia, fecha, habilitado, id_tipo_mesa,
     *     id_llamado, id_ciclo, id_usuario_administracion, aula (confirmado
     *     por MesaExamenController::store()).
     */
    public function run(): void
    {
        $usuarioAlu = Usuario::where('email', 'alu@a.com')->first();

        if (!$usuarioAlu) {
            $this->command->error('No se encontró el usuario alu@a.com. Corré UsuarioAdminSeeder antes.');
            return;
        }

        $estudiante = Estudiante::where('id_usuario', $usuarioAlu->id_usuario)->first();

        if (!$estudiante) {
            $this->command->error('El usuario alu@a.com no tiene fila en estudiantes. Corré UsuarioAdminSeeder antes.');
            return;
        }

        // -------------------------------------------------------------
        // CARRERA: reusa la primera que exista; si no hay ninguna, crea
        // una mínima.
        // -------------------------------------------------------------
        $carrera = Carrera::where('nombre', '!=', 'Sin asignar')->first();

        if (!$carrera) {
            $carrera = Carrera::create([
                'nombre' => 'Redes Informáticas',
            ]);
        }

        // Vincula al alumno con la carrera como ACTIVA (id_estado_carrera = 4,
        // igual que asume EstudianteFinalesService::carreraActiva()).
        EstudianteCarrera::updateOrCreate(
            [
                'id_usuario'  => $usuarioAlu->id_usuario,
                'id_carrera'  => $carrera->id_carrera,
            ],
            [
                'id_estado_carrera' => 4, // 'Activa'
            ]
        );

        // -------------------------------------------------------------
        // MATERIA: reusa una de 1° año de esa carrera (distinta de la que
        // ya tenía inscripta en el ejemplo, "Programación II", para no
        // pisar ese dato); si no hay ninguna, crea una nueva.
        // -------------------------------------------------------------
        $materia = Materia::where('id_carrera', $carrera->id_carrera)
            ->where('nombre', '!=', 'Programación II')
            ->orderBy('anio')
            ->first();

        if (!$materia) {
            $materia = Materia::create([
                'id_carrera'          => $carrera->id_carrera,
                'nombre'              => 'Programación I',
                'anio'                => 1,
                'nota_aprobada'       => 6,
                'nota_promocionable'  => 8,
            ]);
        }

        // -------------------------------------------------------------
        // CURSADA REGULARIZADA: valor entre nota_aprobada y
        // nota_promocionable (o simplemente >= nota_aprobada si no hay
        // promoción configurada), para que situacionCursada() devuelva
        // 'regular' y no 'promocionado' (que bloquearía el final).
        // -------------------------------------------------------------
        $notaAprobada = (float) ($materia->nota_aprobada ?? 6);
        $notaPromocionable = $materia->nota_promocionable !== null
            ? (float) $materia->nota_promocionable
            : null;

        $valorRegular = $notaPromocionable !== null
            ? max($notaAprobada, $notaPromocionable - 1) // justo por debajo de la promoción
            : $notaAprobada;

        NotaCuatrimestre::updateOrCreate(
            [
                'id_usuario' => $usuarioAlu->id_usuario,
                'id_materia' => $materia->id_materia,
            ],
            [
                'valor'       => $valorRegular,
                'fecha_carga' => Carbon::now()->subMonths(2),
            ]
        );

        // -------------------------------------------------------------
        // MESA DE EXAMEN habilitada y futura para esa materia, para que
        // aparezca un botón de fecha en /finalesInscripcion. Reusa
        // catálogos si ya existen; si no, crea los mínimos.
        // -------------------------------------------------------------
        $tipoMesa = TipoMesaExamen::first() ?? TipoMesaExamen::create([
            'descripcion' => 'Regular',
        ]);

        $llamado = Llamado::first() ?? Llamado::create([
            'descripcion' => 'Noviembre/Diciembre',
        ]);

        $ciclo = CicloLectivo::first() ?? CicloLectivo::create([
            'anio' => (int) date('Y'),
        ]);

        $administrador = Administrador::first();

        if (!$administrador) {
            $this->command->error('No hay ningún administrador cargado. Corré UsuarioAdminSeeder antes.');
            return;
        }

        MesaExamen::firstOrCreate(
            [
                'id_materia' => $materia->id_materia,
                'fecha'      => Carbon::now()->addDays(15)->setTime(9, 0),
            ],
            [
                'id_tipo_mesa'                => $tipoMesa->id_tipo_mesa,
                'id_llamado'                  => $llamado->id_llamado,
                'id_ciclo'                    => $ciclo->id_ciclo,
                'id_usuario_administracion'   => $administrador->id_usuario,
                'aula'                        => 'Aula 1',
                'habilitado'                  => true,
            ]
        );

        $this->command->info(
            "Alu vinculado a carrera '{$carrera->nombre}', materia '{$materia->nombre}' regularizada (nota {$valorRegular}), mesa futura habilitada creada."
        );
    }
}