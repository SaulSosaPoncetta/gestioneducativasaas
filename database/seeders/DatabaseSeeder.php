<?php

namespace Database\Seeders;

use App\Enums\NivelEducativo;
use App\Enums\Rol;
use App\Models\Asignacion;
use App\Models\Asistencia;
use App\Models\AsistenciaEspacio;
use App\Models\AsistenciaToma;
use App\Models\Calificacion;
use App\Models\PeriodoEvaluacion;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Cursada;
use App\Models\Docente;
use App\Models\EspacioCurricular;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\MesaExamen;
use App\Models\MesaInscripcion;
use App\Models\Institucion;
use App\Models\InstitucionNivel;
use App\Models\PlanEstudio;
use App\Models\Tutor;
use App\Models\User;
use App\Services\CicloLectivoService;
use App\Support\InstitucionActual;
use App\Models\Auditoria;
use App\Models\Comunicado;
use App\Services\ComunicadoService;
use App\Services\ConstanciaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Datos iniciales:
 * - Superadmin de MiGestión.
 * - Colegio San Martín (inicial, primaria, secundaria y técnica) con
 *   ciclo lectivo en curso, períodos, cursos y una preceptora.
 * - Instituto Superior Belgrano (terciario y universitario) con su ciclo.
 * - Planes de estudio de ejemplo para cada nivel (simplificados, para probar:
 *   no son los diseños curriculares oficiales completos).
 * - Estudiantes de ejemplo con sus familias e inscripciones.
 * - Docentes con sus asignaciones (quedan algunos espacios sin cubrir a
 *   propósito). Docente con usuario: Marcela Ríos, DNI 27111333.
 * - Asistencia diaria de las últimas dos semanas en primaria y secundaria,
 *   y algunas clases de 1° Grado A como si llegaran desde GestiónAula.
 * - Calificaciones del primer período (y parte del segundo) e informes de
 *   jardín, como si llegaran desde GestiónAula.
 * - ISB: ciclo anterior cerrado con cursadas y una mesa con acta, cursadas
 *   del ciclo actual y una mesa abierta para inscribir.
 * - Accesos de ejemplo a los portales (contraseña: password):
 *   familia: la madre/padre de Sofía Romero (1° Grado A) -> ingresa con su DNI;
 *   estudiante superior: Pablo González, DNI 40000000.
 *
 * Contraseña de todos los usuarios de demo: password
 * Cambiala en producción.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Los datos de demostración no se auditan.
        Auditoria::$activa = false;

        User::updateOrCreate(
            ['email' => 'admin@migestion.com.ar'],
            [
                'nombre'   => 'Administrador',
                'apellido' => 'MiGestión',
                'password' => 'password',
                'rol'      => Rol::SuperAdmin,
                'activo'   => true,
            ]
        );

        $colegio = $this->institucion(
            datos: [
                'nombre'       => 'Colegio San Martín',
                'nombre_corto' => 'San Martín',
                'cue'          => '060000100',
                'tipo_gestion' => 'privada',
                'localidad'    => 'La Plata',
                'domicilio'    => 'Calle 7 N° 1234',
                'email'        => 'contacto@colegiosanmartin.edu.ar',
            ],
            niveles: [
                NivelEducativo::Inicial,
                NivelEducativo::Primario,
                NivelEducativo::Secundario,
                NivelEducativo::SecundarioTecnico,
            ],
            admin: ['email' => 'admin@colegiosanmartin.edu.ar', 'dni' => '20111222']
        );

        $isb = $this->institucion(
            datos: [
                'nombre'       => 'Instituto Superior Belgrano',
                'nombre_corto' => 'ISB',
                'cue'          => '060000200',
                'tipo_gestion' => 'privada',
                'localidad'    => 'Mar del Plata',
                'domicilio'    => 'Av. Colón 2500',
                'email'        => 'secretaria@isbelgrano.edu.ar',
            ],
            niveles: [
                NivelEducativo::Terciario,
                NivelEducativo::Universitario,
            ],
            admin: ['email' => 'admin@isbelgrano.edu.ar', 'dni' => '20333444']
        );

        $this->planesColegio($colegio);
        $this->planesInstituto($isb);

        $this->cicloDemo($colegio, conCursos: true);
        $this->cicloDemo($isb, conCursos: false);

        $this->estudiantesColegio($colegio);
        $this->estudiantesInstituto($isb);

        $this->docentesColegio($colegio);
        $this->docentesInstituto($isb);

        $this->asistenciaColegio($colegio);
        $this->calificacionesColegio($colegio);
        $this->trayectoriaInstituto($isb);

        $this->accesosPortales($colegio, $isb);
        $this->comunicados($colegio, $isb);
        $this->constancias($colegio, $isb);

        Auditoria::$activa = true;

        InstitucionActual::establecer(null);
    }

    protected function institucion(array $datos, array $niveles, array $admin): Institucion
    {
        $institucion = Institucion::updateOrCreate(['nombre' => $datos['nombre']], $datos + ['activo' => true]);

        foreach ($niveles as $nivel) {
            InstitucionNivel::sinInstitucion()->updateOrCreate(
                ['institucion_id' => $institucion->id, 'nivel' => $nivel->value],
                ['activo' => true]
            );
        }

        User::updateOrCreate(
            ['email' => $admin['email']],
            [
                'institucion_id' => $institucion->id,
                'nombre'         => 'Administración',
                'apellido'       => $institucion->nombre_corto,
                'dni'            => $admin['dni'],
                'password'       => 'password',
                'rol'            => Rol::Admin,
                'activo'         => true,
            ]
        );

        return $institucion;
    }

    /** Ciclo del año actual en curso, con períodos sugeridos y (opcional) cursos. */
    protected function cicloDemo(Institucion $institucion, bool $conCursos): void
    {
        InstitucionActual::establecer($institucion);

        $anio = (int) date('Y');

        if (CicloLectivo::where('anio', $anio)->exists()) {
            return;
        }

        $niveles = InstitucionNivel::where('activo', true)->get();

        $ciclo = app(CicloLectivoService::class)->crear(
            [
                'anio'         => $anio,
                'nombre'       => "Ciclo lectivo {$anio}",
                'fecha_inicio' => "{$anio}-03-02",
                'fecha_fin'    => "{$anio}-12-18",
            ],
            $niveles->mapWithKeys(fn ($n) => [$n->id => [
                'esquema'         => $n->nivel->esquemaSugerido()->value,
                'intensificacion' => $n->nivel->tieneIntensificacion(),
            ]])->all()
        );

        $ciclo->update(['estado' => CicloLectivo::EN_CURSO]);

        if (! $conCursos) {
            return;
        }

        $preceptora = User::updateOrCreate(
            ['email' => 'preceptoria@colegiosanmartin.edu.ar'],
            [
                'institucion_id' => $institucion->id,
                'nombre'         => 'Laura',
                'apellido'       => 'Fernández',
                'dni'            => '28999000',
                'password'       => 'password',
                'rol'            => Rol::Preceptor,
                'activo'         => true,
            ]
        );

        foreach ($niveles as $n) {
            foreach ($n->nivel->grados($n->duracion) as $grado) {
                $divisiones = $n->nivel === NivelEducativo::Inicial ? ['Roja', 'Verde'] : ['A', 'B'];

                $plan = PlanEstudio::where('institucion_nivel_id', $n->id)
                    ->where('grado_desde', '<=', $grado)->where('grado_hasta', '>=', $grado)
                    ->orderBy('id')->first();

                foreach ($divisiones as $i => $division) {
                    Curso::create([
                        'ciclo_lectivo_id'     => $ciclo->id,
                        'institucion_nivel_id' => $n->id,
                        'plan_estudio_id'      => $plan?->id,
                        'grado'                => $grado,
                        'division'             => $division,
                        'turno'                => $i === 0 ? 'manana' : 'tarde',
                        'preceptor_id'         => in_array($n->nivel, [NivelEducativo::Secundario, NivelEducativo::SecundarioTecnico], true)
                            ? $preceptora->id : null,
                        'cupo'                 => $n->nivel === NivelEducativo::Inicial ? 25 : 30,
                    ]);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Planes de estudio de ejemplo
    // -------------------------------------------------------------------------

    protected function planesColegio(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (PlanEstudio::exists()) {
            return;
        }

        $nivel = fn (NivelEducativo $n) => InstitucionNivel::where('nivel', $n->value)->first();

        // Jardín: mismas áreas en las tres salas.
        $areasInicial = ['Prácticas del Lenguaje', 'Matemática', 'Ambiente Natural y Social', 'Educación Física', 'Educación Artística'];
        $this->plan($nivel(NivelEducativo::Inicial), 'general', 'Diseño curricular Nivel Inicial', 3, 5,
            collect([3, 4, 5])->mapWithKeys(fn ($g) => [$g => array_map(fn ($a) => [$a, 'area', null], $areasInicial)])->all());

        // Primaria: áreas de 1° a 6°.
        $areasPrimaria = [
            ['Prácticas del Lenguaje', 'area', 6], ['Matemática', 'area', 6], ['Ciencias Sociales', 'area', 3],
            ['Ciencias Naturales', 'area', 3], ['Educación Física', 'area', 2], ['Educación Artística', 'area', 2], ['Inglés', 'area', 2],
        ];
        $this->plan($nivel(NivelEducativo::Primario), 'general', 'Diseño curricular Nivel Primario', 1, 6,
            collect(range(1, 6))->mapWithKeys(fn ($g) => [$g => $areasPrimaria])->all(), titulo: null);

        // Secundaria: ciclo básico 1° a 3° + una orientación 4° a 6°.
        $this->plan($nivel(NivelEducativo::Secundario), 'general', 'Ciclo Básico', 1, 3, [
            1 => [['Ciencias Naturales', 'materia', 4], ['Ciencias Sociales', 'materia', 4], ['Construcción de Ciudadanía', 'materia', 3],
                  ['Educación Artística', 'materia', 2], ['Educación Física', 'materia', 2], ['Inglés', 'materia', 2],
                  ['Matemática', 'materia', 4], ['Prácticas del Lenguaje', 'materia', 4]],
            2 => [['Biología', 'materia', 2], ['Fisicoquímica', 'materia', 2], ['Geografía', 'materia', 2], ['Historia', 'materia', 2],
                  ['Construcción de Ciudadanía', 'materia', 2], ['Educación Física', 'materia', 2], ['Inglés', 'materia', 2],
                  ['Matemática', 'materia', 4], ['Prácticas del Lenguaje', 'materia', 4]],
            3 => [['Biología', 'materia', 2], ['Fisicoquímica', 'materia', 2], ['Geografía', 'materia', 2], ['Historia', 'materia', 2],
                  ['Construcción de Ciudadanía', 'materia', 2], ['Educación Física', 'materia', 2], ['Inglés', 'materia', 2],
                  ['Matemática', 'materia', 4], ['Prácticas del Lenguaje', 'materia', 4]],
        ]);
        $this->plan($nivel(NivelEducativo::Secundario), 'orientacion', 'Bachiller en Ciencias Sociales', 4, 6, [
            4 => [['Historia', 'materia', 2], ['Geografía', 'materia', 2], ['Psicología', 'materia', 2], ['Matemática', 'materia', 3],
                  ['Literatura', 'materia', 3], ['Inglés', 'materia', 2], ['Educación Física', 'materia', 2]],
            5 => [['Historia', 'materia', 2], ['Geografía', 'materia', 2], ['Sociología', 'materia', 3], ['Economía Política', 'materia', 3],
                  ['Matemática', 'materia', 3], ['Literatura', 'materia', 3], ['Inglés', 'materia', 2]],
            6 => [['Historia', 'materia', 2], ['Filosofía', 'materia', 3], ['Proyecto de Investigación en Ciencias Sociales', 'proyecto', 4],
                  ['Trabajo y Ciudadanía', 'materia', 3], ['Matemática', 'materia', 3], ['Literatura', 'materia', 3]],
        ], titulo: 'Bachiller en Ciencias Sociales');

        // Técnica: ciclo básico 1° a 3° + especialidad 4° a 7°.
        $this->plan($nivel(NivelEducativo::SecundarioTecnico), 'general', 'Ciclo Básico Técnico', 1, 3, [
            1 => [['Matemática', 'materia', 4, 'cientifico_tecnologica'], ['Prácticas del Lenguaje', 'materia', 4, 'general'],
                  ['Ciencias Naturales', 'materia', 4, 'cientifico_tecnologica'], ['Procedimientos Técnicos', 'taller', 6, 'tecnica_especifica'],
                  ['Lenguajes Tecnológicos', 'taller', 4, 'tecnica_especifica']],
            2 => [['Matemática', 'materia', 4, 'cientifico_tecnologica'], ['Fisicoquímica', 'materia', 3, 'cientifico_tecnologica'],
                  ['Procedimientos Técnicos', 'taller', 6, 'tecnica_especifica'], ['Sistemas Tecnológicos', 'taller', 4, 'tecnica_especifica']],
            3 => [['Matemática', 'materia', 4, 'cientifico_tecnologica'], ['Física', 'materia', 3, 'cientifico_tecnologica'],
                  ['Procedimientos Técnicos', 'taller', 6, 'tecnica_especifica'], ['Sistemas Tecnológicos', 'taller', 4, 'tecnica_especifica']],
        ]);
        $this->plan($nivel(NivelEducativo::SecundarioTecnico), 'especialidad', 'Técnico en Electromecánica', 4, 7, [
            4 => [['Matemática', 'materia', 3, 'cientifico_tecnologica'], ['Física', 'materia', 3, 'cientifico_tecnologica'],
                  ['Dibujo Técnico', 'materia', 3, 'tecnica_especifica'], ['Taller de Electricidad', 'taller', 6, 'tecnica_especifica']],
            5 => [['Análisis Matemático', 'materia', 3, 'cientifico_tecnologica'], ['Electrotecnia', 'materia', 4, 'tecnica_especifica'],
                  ['Mecánica y Mecanismos', 'materia', 4, 'tecnica_especifica'], ['Taller de Máquinas Herramienta', 'taller', 6, 'tecnica_especifica']],
            6 => [['Máquinas Eléctricas', 'materia', 4, 'tecnica_especifica'], ['Termodinámica', 'materia', 3, 'tecnica_especifica'],
                  ['Instalaciones Industriales', 'taller', 6, 'tecnica_especifica']],
            7 => [['Automatización Industrial', 'materia', 4, 'tecnica_especifica'], ['Mantenimiento Electromecánico', 'taller', 6, 'tecnica_especifica'],
                  ['Prácticas Profesionalizantes', 'practica', 6, 'practica_profesional']],
        ], titulo: 'Técnico en Electromecánica');
    }

    protected function planesInstituto(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (PlanEstudio::exists()) {
            return;
        }

        $terciario = InstitucionNivel::where('nivel', NivelEducativo::Terciario->value)->first();

        $plan = PlanEstudio::create([
            'institucion_nivel_id' => $terciario->id,
            'tipo'                 => 'carrera',
            'nombre'               => 'Profesorado de Educación Primaria',
            'titulo'               => 'Profesor/a de Educación Primaria',
            'resolucion'           => '1234/18',
            'modalidad'            => 'presencial',
            'grado_desde'          => 1,
            'grado_hasta'          => 4,
            'activo'               => true,
        ]);

        // [código, nombre, año, régimen, formato, campo, horas]
        $materias = [
            ['PEP01', 'Pedagogía', 1, 'anual', 'materia', 'general', 3],
            ['PEP02', 'Didáctica General', 1, 'anual', 'materia', 'general', 3],
            ['PEP03', 'Matemática y su Didáctica I', 1, 'anual', 'materia', 'especifica', 4],
            ['PEP04', 'Prácticas del Lenguaje y su Didáctica I', 1, 'anual', 'materia', 'especifica', 4],
            ['PEP05', 'Psicología Educacional', 1, 'primer_cuatrimestre', 'materia', 'general', 3],
            ['PEP06', 'Práctica Docente I', 1, 'anual', 'practica', 'practica_profesional', 4],
            ['PEP07', 'Matemática y su Didáctica II', 2, 'anual', 'materia', 'especifica', 4],
            ['PEP08', 'Prácticas del Lenguaje y su Didáctica II', 2, 'anual', 'materia', 'especifica', 4],
            ['PEP09', 'Ciencias Naturales y su Didáctica', 2, 'anual', 'materia', 'especifica', 3],
            ['PEP10', 'Práctica Docente II', 2, 'anual', 'practica', 'practica_profesional', 4],
            ['PEP11', 'Ciencias Sociales y su Didáctica', 3, 'anual', 'materia', 'especifica', 3],
            ['PEP12', 'Práctica Docente III', 3, 'anual', 'practica', 'practica_profesional', 6],
            ['PEP13', 'Política Educativa', 3, 'segundo_cuatrimestre', 'seminario', 'general', 3],
            ['PEP14', 'Práctica Docente IV (Residencia)', 4, 'anual', 'practica', 'practica_profesional', 8],
            ['PEP15', 'Investigación Educativa', 4, 'primer_cuatrimestre', 'seminario', 'general', 3],
        ];

        $id = [];
        foreach ($materias as $i => [$codigo, $nombre, $anio, $regimen, $formato, $campo, $horas]) {
            $id[$codigo] = EspacioCurricular::create([
                'plan_estudio_id' => $plan->id,
                'codigo'          => $codigo,
                'nombre'          => $nombre,
                'grado'           => $anio,
                'regimen'         => $regimen,
                'formato'         => $formato,
                'campo'           => $campo,
                'horas_semanales' => $horas,
                'orden'           => $i,
            ])->id;
        }

        // [espacio, requiere, tipo]
        $correlativas = [
            ['PEP07', 'PEP03', 'regular_para_cursar'], ['PEP07', 'PEP03', 'aprobada_para_rendir'],
            ['PEP08', 'PEP04', 'regular_para_cursar'], ['PEP08', 'PEP04', 'aprobada_para_rendir'],
            ['PEP10', 'PEP06', 'aprobada_para_cursar'], ['PEP10', 'PEP02', 'regular_para_cursar'],
            ['PEP12', 'PEP10', 'aprobada_para_cursar'],
            ['PEP14', 'PEP12', 'aprobada_para_cursar'], ['PEP14', 'PEP07', 'aprobada_para_cursar'], ['PEP14', 'PEP08', 'aprobada_para_cursar'],
        ];

        foreach ($correlativas as [$espacio, $requiere, $tipo]) {
            DB::table('correlatividades')->insert([
                'espacio_curricular_id' => $id[$espacio],
                'requiere_id'           => $id[$requiere],
                'tipo'                  => $tipo,
            ]);
        }
    }

    /**
     * Crea un plan con sus espacios por año.
     * Cada espacio: [nombre, formato, horas semanales, campo (opcional)].
     */
    protected function plan(
        InstitucionNivel $nivel,
        string $tipo,
        string $nombre,
        int $desde,
        int $hasta,
        array $espaciosPorGrado,
        ?string $titulo = null
    ): PlanEstudio {
        $plan = PlanEstudio::create([
            'institucion_nivel_id' => $nivel->id,
            'tipo'                 => $tipo,
            'nombre'               => $nombre,
            'titulo'               => $titulo,
            'grado_desde'          => $desde,
            'grado_hasta'          => $hasta,
            'activo'               => true,
        ]);

        foreach ($espaciosPorGrado as $grado => $espacios) {
            foreach ($espacios as $i => $e) {
                EspacioCurricular::create([
                    'plan_estudio_id' => $plan->id,
                    'nombre'          => $e[0],
                    'grado'           => $grado,
                    'regimen'         => 'anual',
                    'formato'         => $e[1],
                    'horas_semanales' => $e[2] ?? null,
                    'campo'           => $e[3] ?? null,
                    'orden'           => $i,
                ]);
            }
        }

        return $plan;
    }

    // -------------------------------------------------------------------------
    // Estudiantes de ejemplo
    // -------------------------------------------------------------------------

    protected const APELLIDOS = ['González', 'Rodríguez', 'Gómez', 'Fernández', 'López', 'Díaz', 'Martínez', 'Pérez',
        'García', 'Sánchez', 'Romero', 'Sosa', 'Álvarez', 'Torres', 'Ruiz', 'Ramírez', 'Flores', 'Acosta', 'Benítez', 'Medina'];
    protected const NOMBRES_F = ['Sofía', 'Martina', 'Valentina', 'Isabella', 'Catalina', 'Emma', 'Mía', 'Olivia', 'Delfina', 'Julieta'];
    protected const NOMBRES_M = ['Mateo', 'Benjamín', 'Thiago', 'Bautista', 'Joaquín', 'Santiago', 'Lautaro', 'Felipe', 'Tomás', 'Bruno'];
    protected const NOMBRES_ADULTOS_F = ['Carolina', 'Paula', 'Laura', 'Natalia', 'Silvina', 'Andrea', 'Mariana', 'Verónica'];
    protected const NOMBRES_ADULTOS_M = ['Pablo', 'Diego', 'Martín', 'Sergio', 'Gustavo', 'Hernán', 'Leandro', 'Fernando'];

    /** 3 estudiantes por curso del ciclo en curso, con madre o padre. Algunos hermanos comparten tutor. */
    protected function estudiantesColegio(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (Estudiante::exists()) {
            return;
        }

        $ciclo  = CicloLectivo::enCurso()->first();
        $cursos = Curso::with('nivel')->where('ciclo_lectivo_id', $ciclo->id)->where('division', '!=', 'B')
            ->where('division', '!=', 'Verde')->orderBy('institucion_nivel_id')->orderBy('grado')->get();

        $n = 0;
        $tutorAnterior = null;

        foreach ($cursos as $curso) {
            for ($i = 0; $i < 3; $i++, $n++) {
                $femenino = $n % 2 === 0;
                $edad = match ($curso->nivel->nivel) {
                    NivelEducativo::Inicial => $curso->grado,
                    NivelEducativo::Primario => $curso->grado + 5,
                    default => $curso->grado + 11,
                };

                // Cada 7 estudiantes, uno es hermano del anterior (mismo apellido y tutor).
                $hermano = $tutorAnterior && $n % 7 === 0;
                $apellido = $hermano ? $tutorAnterior->apellido : self::APELLIDOS[$n % count(self::APELLIDOS)];

                $estudiante = Estudiante::create([
                    'apellido'         => $apellido,
                    'nombre'           => ($femenino ? self::NOMBRES_F : self::NOMBRES_M)[($n * 7 + intdiv($n, 20)) % 10],
                    'tipo_documento'   => 'dni',
                    'documento'        => (string) (60000000 - $edad * 1000000 + $n * 137),
                    'fecha_nacimiento' => now()->subYears($edad)->subDays(($n * 37) % 300)->toDateString(),
                    'genero'           => $femenino ? 'femenino' : 'masculino',
                    'nacionalidad'     => 'Argentina',
                    'domicilio'        => 'Calle ' . (1 + $n % 30) . ' N° ' . (100 + $n * 7),
                    'localidad'        => 'La Plata',
                    'obra_social'      => $n % 3 ? 'IOMA' : 'OSDE',
                    'grupo_sanguineo'  => ['0+', 'A+', 'B+', '0-'][$n % 4],
                    'salud_observaciones' => $n % 11 === 0 ? 'Alergia a la penicilina.' : null,
                    'fecha_ingreso'    => $ciclo->fecha_inicio->toDateString(),
                    'estado'           => 'activo',
                ]);

                $tutor = $hermano ? $tutorAnterior : Tutor::create([
                    'apellido'  => $apellido,
                    'nombre'    => ($n % 3 ? self::NOMBRES_ADULTOS_F : self::NOMBRES_ADULTOS_M)[$n % 8],
                    'documento' => (string) (25000000 + $n * 1013),
                    'telefono'  => '221-15' . str_pad((string) (400000 + $n * 91), 6, '0', STR_PAD_LEFT),
                    'email'     => 'familia' . $n . '@ejemplo.com',
                    'domicilio' => $estudiante->domicilio,
                ]);

                $estudiante->tutores()->attach($tutor->id, [
                    'vinculo'               => $n % 3 ? 'madre' : 'padre',
                    'es_responsable'        => true,
                    'autorizado_retirar'    => true,
                    'convive'               => true,
                    'recibe_notificaciones' => true,
                ]);

                $tutorAnterior = $tutor;

                Inscripcion::create([
                    'estudiante_id'        => $estudiante->id,
                    'ciclo_lectivo_id'     => $ciclo->id,
                    'institucion_nivel_id' => $curso->institucion_nivel_id,
                    'curso_id'             => $curso->id,
                    'plan_estudio_id'      => $curso->plan_estudio_id,
                    'fecha'                => $ciclo->fecha_inicio->copy()->subDays(20)->toDateString(),
                    'condicion'            => $n % 13 === 0 ? 'repitente' : 'regular',
                ]);
            }
        }
    }

    /** 8 estudiantes del Profesorado (mayores de edad, sin tutores). */
    protected function estudiantesInstituto(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (Estudiante::exists()) {
            return;
        }

        $ciclo = CicloLectivo::enCurso()->first();
        $plan  = PlanEstudio::where('tipo', 'carrera')->first();

        for ($n = 0; $n < 8; $n++) {
            $femenino = $n % 4 !== 0;

            $estudiante = Estudiante::create([
                'apellido'         => self::APELLIDOS[($n * 3) % count(self::APELLIDOS)],
                'nombre'           => ($femenino ? self::NOMBRES_ADULTOS_F : self::NOMBRES_ADULTOS_M)[$n],
                'tipo_documento'   => 'dni',
                'documento'        => (string) (40000000 + $n * 2711),
                'fecha_nacimiento' => now()->subYears(19 + $n)->toDateString(),
                'genero'           => $femenino ? 'femenino' : 'masculino',
                'nacionalidad'     => 'Argentina',
                'localidad'        => 'Mar del Plata',
                'email'            => 'estudiante' . $n . '@ejemplo.com',
                'telefono'         => '223-15' . str_pad((string) (500000 + $n * 77), 6, '0', STR_PAD_LEFT),
                'fecha_ingreso'    => $ciclo->fecha_inicio->toDateString(),
                'estado'           => 'activo',
            ]);

            Inscripcion::create([
                'estudiante_id'        => $estudiante->id,
                'ciclo_lectivo_id'     => $ciclo->id,
                'institucion_nivel_id' => $plan->institucion_nivel_id,
                'plan_estudio_id'      => $plan->id,
                'fecha'                => $ciclo->fecha_inicio->copy()->subDays(30)->toDateString(),
                'condicion'            => $n < 4 ? 'ingresante' : 'regular',
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Docentes de ejemplo
    // -------------------------------------------------------------------------

    protected function docente(string $apellido, string $nombre, string $dni, ?string $titulo = null, bool $conUsuario = false): Docente
    {
        $docente = Docente::create([
            'apellido'      => $apellido,
            'nombre'        => $nombre,
            'documento'     => $dni,
            'titulo'        => $titulo,
            'telefono'      => '221-15' . substr($dni, -6),
            'email'         => mb_strtolower(Str::ascii($nombre . '.' . $apellido)) . '@docentes.ejemplo.com',
            'fecha_ingreso' => now()->subYears(3)->startOfYear()->toDateString(),
            'estado'        => 'activo',
        ]);

        if ($conUsuario) {
            $usuario = User::create([
                'institucion_id' => $docente->institucion_id,
                'nombre'         => $nombre,
                'apellido'       => $apellido,
                'dni'            => $dni,
                'email'          => $docente->email,
                'password'       => 'password',
                'rol'            => Rol::Docente,
                'activo'         => true,
            ]);
            $docente->update(['user_id' => $usuario->id]);
        }

        return $docente;
    }

    /** Asigna al docente los espacios del curso cuyo nombre está (o no está) en la lista. */
    protected function asignar(Docente $docente, Curso $curso, CicloLectivo $ciclo, ?array $solo = null, array $excepto = [], string $situacion = 'titular'): void
    {
        $espacios = EspacioCurricular::where('plan_estudio_id', $curso->plan_estudio_id)->where('grado', $curso->grado)->get()
            ->filter(fn ($e) => ($solo === null || in_array($e->nombre, $solo, true)) && ! in_array($e->nombre, $excepto, true));

        foreach ($espacios as $e) {
            Asignacion::create([
                'docente_id'            => $docente->id,
                'ciclo_lectivo_id'      => $ciclo->id,
                'espacio_curricular_id' => $e->id,
                'curso_id'              => $curso->id,
                'situacion'             => $situacion,
                'horas'                 => $e->horas_semanales,
                'fecha_desde'           => $ciclo->fecha_inicio->toDateString(),
            ]);
        }
    }

    protected function docentesColegio(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (Docente::exists()) {
            return;
        }

        $ciclo  = CicloLectivo::enCurso()->first();
        $cursos = Curso::with('nivel')->where('ciclo_lectivo_id', $ciclo->id)->orderBy('grado')->orderBy('division')->get()
            ->groupBy(fn ($c) => $c->nivel->nivel->value);

        $especiales = ['Educación Física', 'Educación Artística', 'Inglés'];
        $edFisica = $this->docente('Castro', 'Rodrigo', '26444555', 'Profesor de Educación Física');
        $artistica = $this->docente('Molina', 'Lucía', '29555666', 'Profesora de Artes Visuales');
        $ingles = $this->docente('Herrera', 'Claudia', '24666777', 'Profesora de Inglés');

        // Jardín: una maestra por sala (Ed. Física con el profesor especial).
        $jardineras = [['Paz', 'Florencia'], ['Vega', 'Romina'], ['Luna', 'Agustina'], ['Ríos', 'Belén'], ['Suárez', 'Daiana'], ['Ortiz', 'Carla']];
        foreach (($cursos['inicial'] ?? collect())->values() as $i => $curso) {
            [$ap, $no] = $jardineras[$i % count($jardineras)];
            $maestra = $this->docente($ap, $no, (string) (30100000 + $i * 111), 'Profesora de Educación Inicial');
            $this->asignar($maestra, $curso, $ciclo, excepto: ['Educación Física', 'Educación Artística']);
            $this->asignar($edFisica, $curso, $ciclo, solo: ['Educación Física']);
            $this->asignar($artistica, $curso, $ciclo, solo: ['Educación Artística']);
        }

        // Primaria: maestra de grado + especiales.
        $maestras = [['Ríos', 'Marcela'], ['Navarro', 'Silvia'], ['Cabrera', 'Andrea'], ['Giménez', 'Paola'], ['Ramos', 'Natalia'], ['Ojeda', 'Verónica'],
                     ['Peralta', 'Gabriela'], ['Quiroga', 'Mónica'], ['Aguirre', 'Liliana'], ['Correa', 'Viviana'], ['Ledesma', 'Sandra'], ['Domínguez', 'Elena']];
        foreach (($cursos['primario'] ?? collect())->values() as $i => $curso) {
            [$ap, $no] = $maestras[$i % count($maestras)];
            $dni = $i === 0 ? '27111333' : (string) (31200000 + $i * 111);
            $maestra = $this->docente($ap, $no, $dni, 'Profesora de Educación Primaria', conUsuario: $i === 0);
            $this->asignar($maestra, $curso, $ciclo, excepto: $especiales);
            $this->asignar($edFisica, $curso, $ciclo, solo: ['Educación Física']);
            $this->asignar($artistica, $curso, $ciclo, solo: ['Educación Artística']);
            $this->asignar($ingles, $curso, $ciclo, solo: ['Inglés']);
        }

        // Secundaria y técnica: profesores por materia (algunas quedan sin cubrir).
        $profesores = [
            'Matemática'             => $this->docente('Benítez', 'Javier', '25777888', 'Profesor de Matemática'),
            'Prácticas del Lenguaje' => $this->docente('Sosa', 'Mariana', '28888999', 'Profesora de Lengua y Literatura'),
            'Literatura'             => null,
            'Historia'               => $this->docente('Méndez', 'Ricardo', '23999111', 'Profesor de Historia'),
            'Geografía'              => $this->docente('Rojas', 'Patricia', '27222444', 'Profesora de Geografía'),
            'Biología'               => $this->docente('Figueroa', 'Laura', '30333555', 'Profesora de Biología'),
            'Ciencias Naturales'     => null,
            'Taller de Electricidad' => $this->docente('Arias', 'Oscar', '22444666', 'Técnico Electricista'),
            'Procedimientos Técnicos' => null,
        ];
        $profesores['Literatura'] = $profesores['Prácticas del Lenguaje'];
        $profesores['Ciencias Naturales'] = $profesores['Biología'];
        $profesores['Procedimientos Técnicos'] = $profesores['Taller de Electricidad'];

        foreach (['secundario', 'secundario_tecnico'] as $nivel) {
            foreach (($cursos[$nivel] ?? collect()) as $curso) {
                foreach ($profesores as $materia => $profe) {
                    $this->asignar($profe, $curso, $ciclo, solo: [$materia]);
                }
                $this->asignar($edFisica, $curso, $ciclo, solo: ['Educación Física']);
                $this->asignar($ingles, $curso, $ciclo, solo: ['Inglés']);
            }
        }

        // Un suplente de ejemplo en Matemática de 1° Año A (secundaria).
        $suplente = $this->docente('Villalba', 'Ezequiel', '33555777', 'Profesor de Matemática');
        if ($curso = ($cursos['secundario'] ?? collect())->first()) {
            $this->asignar($suplente, $curso, $ciclo, solo: ['Matemática'], situacion: 'suplente');
        }

        // Una docente sin asignaciones todavía.
        $this->docente('Pereyra', 'Carolina', '34666888', 'Profesora de Química');
    }

    protected function docentesInstituto(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (Docente::exists()) {
            return;
        }

        $ciclo = CicloLectivo::enCurso()->first();
        $plan  = PlanEstudio::where('tipo', 'carrera')->with('espacios')->first();

        $porCampo = [
            'general'              => $this->docente('Lombardi', 'Ana', '21333444', 'Lic. en Ciencias de la Educación'),
            'especifica'           => $this->docente('Ferreyra', 'Mario', '22555666', 'Profesor de Matemática'),
            'practica_profesional' => $this->docente('Bianchi', 'Susana', '23777888', 'Profesora de Educación Primaria'),
        ];

        foreach ($plan->espacios as $i => $e) {
            if ($i % 5 === 4) {
                continue; // algunos sin cubrir
            }

            Asignacion::create([
                'docente_id'            => $porCampo[$e->campo]->id,
                'ciclo_lectivo_id'      => $ciclo->id,
                'espacio_curricular_id' => $e->id,
                'comision'              => 'Única',
                'situacion'             => 'titular',
                'horas'                 => $e->horas_semanales,
                'fecha_desde'           => $ciclo->fecha_inicio->toDateString(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Asistencia de ejemplo
    // -------------------------------------------------------------------------

    protected function asistenciaColegio(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (AsistenciaToma::exists()) {
            return;
        }

        $ciclo = CicloLectivo::enCurso()->first();
        $dias = collect(range(14, 1))->map(fn ($d) => today()->subDays($d))
            ->filter(fn ($d) => $d->isWeekday() && $d->between($ciclo->fecha_inicio, $ciclo->fecha_fin))
            ->values();

        $preceptora = User::where('rol', Rol::Preceptor->value)->first();
        $cursos = Curso::with('nivel')->where('ciclo_lectivo_id', $ciclo->id)->get()
            ->filter(fn ($c) => in_array($c->nivel->nivel, [NivelEducativo::Primario, NivelEducativo::Secundario], true));

        foreach ($cursos as $curso) {
            $estudiantes = Inscripcion::where('curso_id', $curso->id)->where('estado', Inscripcion::ACTIVA)->pluck('estudiante_id');

            foreach ($dias as $d => $dia) {
                $toma = AsistenciaToma::create([
                    'ciclo_lectivo_id' => $ciclo->id,
                    'curso_id'         => $curso->id,
                    'fecha'            => $dia->toDateString(),
                    'user_id'          => $preceptora?->id,
                ]);

                foreach ($estudiantes as $i => $estudianteId) {
                    $semilla = ($estudianteId * 7 + $d * 3) % 23;
                    $estado = match (true) {
                        $semilla === 0, $semilla === 11 => 'ausente',
                        $semilla === 5                  => 'tarde',
                        $semilla === 17                 => 'retiro',
                        default                         => 'presente',
                    };

                    Asistencia::create([
                        'asistencia_toma_id' => $toma->id,
                        'estudiante_id'      => $estudianteId,
                        'fecha'              => $dia->toDateString(),
                        'estado'             => $estado,
                        'justificada'        => $estado === 'ausente' && $semilla === 11,
                        'motivo'             => $estado === 'ausente' && $semilla === 11 ? 'Certificado médico' : null,
                    ]);
                }
            }
        }

        // Clases de Matemática de 1° Grado A recibidas "desde GestiónAula".
        $asignacion = Asignacion::with('espacio')->whereHas('espacio', fn ($q) => $q->where('nombre', 'Matemática'))
            ->whereHas('docente', fn ($q) => $q->where('documento', '27111333'))->first();

        if ($asignacion) {
            $estudiantes = Inscripcion::where('curso_id', $asignacion->curso_id)->where('estado', Inscripcion::ACTIVA)->pluck('estudiante_id');

            foreach ($dias->take(6) as $d => $dia) {
                foreach ($estudiantes as $i => $estudianteId) {
                    AsistenciaEspacio::create([
                        'ciclo_lectivo_id'      => $ciclo->id,
                        'asignacion_id'         => $asignacion->id,
                        'espacio_curricular_id' => $asignacion->espacio_curricular_id,
                        'curso_id'              => $asignacion->curso_id,
                        'docente_id'            => $asignacion->docente_id,
                        'estudiante_id'         => $estudianteId,
                        'fecha'                 => $dia->toDateString(),
                        'estado'                => ($i + $d) % 5 === 0 ? 'ausente' : 'presente',
                        'origen'                => 'gestionaula',
                        'referencia'            => 'demo-' . $dia->format('Ymd'),
                    ]);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Calificaciones de ejemplo (como si llegaran desde GestiónAula)
    // -------------------------------------------------------------------------

    protected function calificacionesColegio(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (Calificacion::exists()) {
            return;
        }

        $ciclo  = CicloLectivo::enCurso()->first();
        $cursos = Curso::with(['nivel', 'plan'])->where('ciclo_lectivo_id', $ciclo->id)->whereNotNull('plan_estudio_id')->get();

        foreach ($cursos as $curso) {
            $periodos = PeriodoEvaluacion::where('ciclo_lectivo_id', $ciclo->id)
                ->where('institucion_nivel_id', $curso->institucion_nivel_id)
                ->where('tipo', PeriodoEvaluacion::REGULAR)->orderBy('orden')->get();
            $espacios = EspacioCurricular::where('plan_estudio_id', $curso->plan_estudio_id)->where('grado', $curso->grado)->get();
            $alumnos  = Inscripcion::where('curso_id', $curso->id)->where('estado', Inscripcion::ACTIVA)->pluck('estudiante_id');
            $docentes = Asignacion::where('curso_id', $curso->id)->pluck('docente_id', 'espacio_curricular_id');
            $inicial  = $curso->nivel->nivel === NivelEducativo::Inicial;

            // Período 1 completo; período 2 solo la mitad de los espacios.
            foreach ($periodos->take(2) as $p => $periodo) {
                foreach ($espacios as $k => $espacio) {
                    if ($p === 1 && $k % 2 === 1) {
                        continue;
                    }

                    foreach ($alumnos as $i => $estudianteId) {
                        $nota = 4 + (($estudianteId * 3 + $k * 5 + $p * 7) % 7); // 4 a 10

                        Calificacion::create([
                            'ciclo_lectivo_id'      => $ciclo->id,
                            'periodo_evaluacion_id' => $periodo->id,
                            'espacio_curricular_id' => $espacio->id,
                            'curso_id'              => $curso->id,
                            'estudiante_id'         => $estudianteId,
                            'docente_id'            => $docentes[$espacio->id] ?? null,
                            'valor'                 => $inicial ? null : (string) $nota,
                            'valor_numerico'        => $inicial ? null : $nota,
                            'informe'               => $inicial
                                ? 'Participa con entusiasmo de las propuestas de ' . mb_strtolower($espacio->nombre) . '. Se vincula bien con sus pares y avanza en su autonomía.'
                                : null,
                            'observacion'           => ! $inicial && $nota < 6 ? 'Debe reforzar los contenidos del período.' : null,
                            'origen'                => 'gestionaula',
                        ]);
                    }
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Nivel superior: trayectoria de ejemplo (ISB)
    // -------------------------------------------------------------------------

    protected function trayectoriaInstituto(Institucion $institucion): void
    {
        InstitucionActual::establecer($institucion);

        if (Cursada::exists()) {
            return;
        }

        $actual = CicloLectivo::enCurso()->first();
        $plan   = PlanEstudio::where('tipo', 'carrera')->with('espacios')->first();
        $nivel  = InstitucionNivel::find($plan->institucion_nivel_id);
        $anio   = $actual->anio - 1;

        // Ciclo anterior, cerrado.
        $anterior = app(CicloLectivoService::class)->crear(
            ['anio' => $anio, 'nombre' => "Ciclo lectivo {$anio}", 'fecha_inicio' => "{$anio}-03-02", 'fecha_fin' => "{$anio}-12-18"],
            [$nivel->id => ['esquema' => 'cuatrimestral']]
        );
        $anterior->update(['estado' => CicloLectivo::CERRADO]);

        $primero = $plan->espacios->where('grado', 1)->values();
        $segundo = $plan->espacios->where('grado', 2)->values();
        $estudiantes = Inscripcion::where('ciclo_lectivo_id', $actual->id)->where('plan_estudio_id', $plan->id)
            ->orderBy('estudiante_id')->get();
        $pedagogia = $primero->firstWhere('codigo', 'PEP01');

        foreach ($estudiantes as $n => $insc) {
            if ($n >= 4) {
                // Segundo año: 1° año cursado el ciclo anterior.
                Inscripcion::create([
                    'estudiante_id' => $insc->estudiante_id, 'ciclo_lectivo_id' => $anterior->id,
                    'institucion_nivel_id' => $plan->institucion_nivel_id, 'plan_estudio_id' => $plan->id,
                    'fecha' => "{$anio}-02-15", 'condicion' => 'ingresante', 'estado' => Inscripcion::FINALIZADA,
                    'fecha_baja' => "{$anio}-12-18",
                ]);

                foreach ($primero as $k => $e) {
                    $regular = $e->id === $pedagogia->id || ($k + $n) % 4 === 0;
                    Cursada::create([
                        'estudiante_id' => $insc->estudiante_id, 'espacio_curricular_id' => $e->id, 'ciclo_lectivo_id' => $anterior->id,
                        'comision' => 'Única', 'fecha_inscripcion' => "{$anio}-03-01",
                        'condicion' => $regular ? 'regular' : 'promocionado',
                        'nota' => $regular ? 5 : 7 + ($k + $n) % 3,
                        'fecha_condicion' => "{$anio}-12-10",
                        'vence' => $regular ? ($anio + 2) . '-12-18' : null,
                    ]);
                }

                // 2° año en el ciclo actual.
                foreach ($segundo as $e) {
                    Cursada::create([
                        'estudiante_id' => $insc->estudiante_id, 'espacio_curricular_id' => $e->id, 'ciclo_lectivo_id' => $actual->id,
                        'comision' => 'Única', 'fecha_inscripcion' => $actual->fecha_inicio, 'condicion' => 'cursando',
                    ]);
                }
                $insc->update(['condicion' => 'regular']);
            } else {
                // Ingresantes: 1° año en el ciclo actual; la materia del 1° cuatrimestre ya cerrada.
                foreach ($primero as $k => $e) {
                    $cerrada = $e->regimen === 'primer_cuatrimestre';
                    Cursada::create([
                        'estudiante_id' => $insc->estudiante_id, 'espacio_curricular_id' => $e->id, 'ciclo_lectivo_id' => $actual->id,
                        'comision' => 'Única', 'fecha_inscripcion' => $actual->fecha_inicio,
                        'condicion' => $cerrada ? ($n % 2 ? 'promocionado' : 'regular') : 'cursando',
                        'nota' => $cerrada ? ($n % 2 ? 8 : 5) : null,
                        'fecha_condicion' => $cerrada ? "{$actual->anio}-07-20" : null,
                        'vence' => $cerrada && $n % 2 === 0 ? ($actual->anio + 2) . '-12-18' : null,
                    ]);
                }
            }
        }

        // Mesa de diciembre del año anterior (acta cerrada) de Pedagogía.
        $mesa = MesaExamen::create([
            'espacio_curricular_id' => $pedagogia->id, 'llamado' => "Diciembre {$anio} - 1° llamado",
            'fecha' => "{$anio}-12-15 18:00", 'aula' => '12', 'presidente_id' => Docente::first()?->id, 'vocales' => 'Ferreyra, Bianchi',
            'inscripcion_desde' => "{$anio}-12-01", 'inscripcion_hasta' => "{$anio}-12-12", 'admite_libres' => true,
            'estado' => 'cerrada', 'libro' => '3', 'folio' => '47', 'cerrada_at' => "{$anio}-12-15 21:00",
        ]);
        foreach ($estudiantes->slice(4)->values() as $k => $insc) {
            $nota = [8, 3, 6, null][$k] ?? null;
            MesaInscripcion::create([
                'mesa_examen_id' => $mesa->id, 'estudiante_id' => $insc->estudiante_id, 'condicion' => 'regular',
                'nota' => $nota, 'resultado' => $nota === null ? 'ausente' : ($nota >= 4 ? 'aprobado' : 'desaprobado'),
            ]);
        }

        // Mesa abierta del ciclo actual (Psicología Educacional) con inscripción vigente.
        $psico = $primero->firstWhere('codigo', 'PEP05');
        MesaExamen::create([
            'espacio_curricular_id' => $psico->id, 'llamado' => "Noviembre {$actual->anio} - 1° llamado",
            'fecha' => today()->addWeeks(3)->setTime(18, 0), 'aula' => '8', 'presidente_id' => Docente::first()?->id,
            'inscripcion_desde' => today()->subDays(3), 'inscripcion_hasta' => today()->addWeeks(2), 'admite_libres' => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // Accesos de ejemplo a los portales
    // -------------------------------------------------------------------------

    protected function accesosPortales(Institucion $colegio, Institucion $isb): void
    {
        // Familia de una estudiante de 1° Grado A.
        InstitucionActual::establecer($colegio);
        $curso = Curso::whereHas('nivel', fn ($q) => $q->where('nivel', 'primario'))->where('grado', 1)->where('division', 'A')->first();
        $estudiante = Inscripcion::with('estudiante.tutores')->where('curso_id', $curso?->id)->get()
            ->pluck('estudiante')->sortBy('apellido')->first();

        if ($tutor = $estudiante?->tutores->first()) {
            $usuario = User::firstOrCreate(
                ['institucion_id' => $colegio->id, 'dni' => $tutor->documento],
                ['nombre' => $tutor->nombre, 'apellido' => $tutor->apellido, 'password' => 'password', 'rol' => Rol::Tutor, 'activo' => true]
            );
            $tutor->update(['user_id' => $usuario->id]);
        }

        // Estudiante de nivel superior con inscripción a cursadas abierta.
        InstitucionActual::establecer($isb);
        CicloLectivo::enCurso()->first()?->update([
            'inscripcion_cursadas_desde' => today()->subDays(5),
            'inscripcion_cursadas_hasta' => today()->addDays(10),
        ]);

        if ($alumno = Estudiante::where('documento', '40000000')->first()) {
            $usuario = User::firstOrCreate(
                ['institucion_id' => $isb->id, 'dni' => $alumno->documento],
                ['nombre' => $alumno->nombre, 'apellido' => $alumno->apellido, 'password' => 'password', 'rol' => Rol::Estudiante, 'activo' => true]
            );
            $alumno->update(['user_id' => $usuario->id]);
        }

        InstitucionActual::establecer(null);
    }
    protected function comunicados(Institucion $colegio, Institucion $isb): void
    {
        $servicio = app(ComunicadoService::class);

        InstitucionActual::establecer($colegio);
        $autor = User::where('institucion_id', $colegio->id)->where('rol', Rol::Admin)->first();
        $primaria = InstitucionNivel::where('nivel', 'primario')->first();

        $reunion = Comunicado::create([
            'autor_id'              => $autor?->id,
            'titulo'                => 'Reunión de familias de Primaria',
            'cuerpo'                => "Estimadas familias:\n\nLas invitamos a la reunión informativa del segundo cuatrimestre, el próximo jueves a las 18 h en el SUM.\n\nVamos a conversar sobre el cierre del ciclo, actos y salidas educativas.\n\nLa Dirección",
            'alcance'               => 'niveles',
            'alcance_ids'           => [$primaria?->id],
            'para'                  => ['familias', 'docentes'],
            'requiere_confirmacion' => true,
            'enviar_email'          => false,
        ]);
        $servicio->publicar($reunion);

        Comunicado::create([
            'autor_id'    => $autor?->id,
            'titulo'      => 'Jornada institucional (borrador)',
            'cuerpo'      => "El viernes no habrá clases por jornada institucional.",
            'alcance'     => 'institucion',
            'alcance_ids' => [],
            'para'        => ['familias', 'estudiantes'],
        ]);

        InstitucionActual::establecer($isb);
        $autor = User::where('institucion_id', $isb->id)->where('rol', Rol::Admin)->first();

        $mesas = Comunicado::create([
            'autor_id'     => $autor?->id,
            'titulo'       => 'Inscripción a mesas de diciembre',
            'cuerpo'       => "Recordamos que la inscripción a mesas de examen se hace desde el portal, en \"Mesas de examen\".\n\nConsultas: https://www.example.com/bedelia",
            'alcance'      => 'carreras',
            'alcance_ids'  => PlanEstudio::pluck('id')->all(),
            'para'         => ['estudiantes'],
            'enviar_email' => false,
        ]);
        $servicio->publicar($mesas);

        InstitucionActual::establecer(null);
    }
    protected function constancias(Institucion $colegio, Institucion $isb): void
    {
        $servicio = app(ConstanciaService::class);

        InstitucionActual::establecer($colegio);
        $secretario = User::where('institucion_id', $colegio->id)->where('rol', Rol::Admin)->first();

        Estudiante::whereHas('inscripciones', fn ($q) => $q->where('estado', 'activa'))
            ->orderBy('apellido')->limit(3)->get()
            ->each(fn ($e) => $servicio->emitirAlumnoRegular($e, 'ANSES', $secretario?->id));

        InstitucionActual::establecer($isb);
        $secretario = User::where('institucion_id', $isb->id)->where('rol', Rol::Admin)->first();

        if ($examen = MesaInscripcion::where('resultado', 'aprobado')->first()) {
            $servicio->emitirExamen($examen, 'Empleador', true, $secretario?->id);
        }

        InstitucionActual::establecer(null);
    }
}
