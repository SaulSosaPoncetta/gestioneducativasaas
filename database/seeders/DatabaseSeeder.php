<?php

namespace Database\Seeders;

use App\Enums\NivelEducativo;
use App\Enums\Rol;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\EspacioCurricular;
use App\Models\Institucion;
use App\Models\InstitucionNivel;
use App\Models\PlanEstudio;
use App\Models\User;
use App\Services\CicloLectivoService;
use App\Support\InstitucionActual;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos iniciales:
 * - Superadmin de MiGestión.
 * - Colegio San Martín (inicial, primaria, secundaria y técnica) con
 *   ciclo lectivo en curso, períodos, cursos y una preceptora.
 * - Instituto Superior Belgrano (terciario y universitario) con su ciclo.
 * - Planes de estudio de ejemplo para cada nivel (simplificados, para probar:
 *   no son los diseños curriculares oficiales completos).
 *
 * Contraseña de todos los usuarios de demo: password
 * Cambiala en producción.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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

        $this->cicloDemo($colegio, conCursos: true);
        $this->cicloDemo($isb, conCursos: false);

        $this->planesColegio($colegio);
        $this->planesInstituto($isb);

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

                foreach ($divisiones as $i => $division) {
                    Curso::create([
                        'ciclo_lectivo_id'     => $ciclo->id,
                        'institucion_nivel_id' => $n->id,
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
}
