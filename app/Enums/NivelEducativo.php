<?php

namespace App\Enums;

/**
 * Niveles y modalidades educativas que puede ofrecer una institución.
 *
 * Cada nivel define cómo se organiza (salas, grados, años o carreras) y cómo
 * se evalúa. El resto del sistema consulta estos métodos en lugar de
 * preguntar "¿es primaria?", así agregar un nivel nuevo es tocar solo acá.
 */
enum NivelEducativo: string
{
    case Inicial           = 'inicial';
    case Primario          = 'primario';
    case Secundario        = 'secundario';
    case SecundarioTecnico = 'secundario_tecnico';
    case Terciario         = 'terciario';
    case Universitario     = 'universitario';

    public function label(): string
    {
        return match ($this) {
            self::Inicial           => 'Nivel Inicial (Jardín)',
            self::Primario          => 'Nivel Primario',
            self::Secundario        => 'Nivel Secundario',
            self::SecundarioTecnico => 'Secundaria Técnica',
            self::Terciario         => 'Nivel Superior (Terciario)',
            self::Universitario     => 'Universitario',
        };
    }

    public function labelCorto(): string
    {
        return match ($this) {
            self::Inicial           => 'Inicial',
            self::Primario          => 'Primaria',
            self::Secundario        => 'Secundaria',
            self::SecundarioTecnico => 'Técnica',
            self::Terciario         => 'Terciario',
            self::Universitario     => 'Universidad',
        };
    }

    /** Ejemplo de denominación oficial, para los formularios. */
    public function ejemploDenominacion(): string
    {
        return match ($this) {
            self::Inicial           => 'Jardín de Infantes N° 905',
            self::Primario          => 'EP N° 12 Gral. San Martín',
            self::Secundario        => 'ES N° 3',
            self::SecundarioTecnico => 'EEST N° 1',
            self::Terciario         => 'ISFD N° 81',
            self::Universitario     => 'Facultad de Ciencias Económicas',
        };
    }

    /** Cómo se llama cada tramo: "Sala de 4", "3° Grado", "2° Año". */
    public function unidadCurso(): string
    {
        return match ($this) {
            self::Inicial  => 'Sala',
            self::Primario => 'Grado',
            default        => 'Año',
        };
    }

    /** Cómo se llama cada grupo de un mismo tramo: "1° A", "Comisión 2". */
    public function unidadDivision(): string
    {
        return match ($this) {
            self::Inicial                          => 'Sección',
            self::Terciario, self::Universitario   => 'Comisión',
            default                                => 'División',
        };
    }

    /** Terciario y universitario se organizan por carreras con plan de estudio. */
    public function usaCarreras(): bool
    {
        return in_array($this, [self::Terciario, self::Universitario], true);
    }

    /** Técnica tiene especialidades; secundaria común tiene orientaciones. */
    public function nombreOrientacion(): ?string
    {
        return match ($this) {
            self::Secundario        => 'Orientación',
            self::SecundarioTecnico => 'Especialidad',
            self::Terciario, self::Universitario => 'Carrera',
            default                 => null,
        };
    }

    /** Mesas de examen final con inscripción (y correlatividades). */
    public function tieneFinales(): bool
    {
        return $this->usaCarreras();
    }

    /** El nivel inicial se evalúa con informes cualitativos, no con notas. */
    public function evaluaConNotas(): bool
    {
        return $this !== self::Inicial;
    }

    /** Los estudiantes son menores y necesitan tutores responsables. */
    public function requiereTutores(): bool
    {
        return ! $this->usaCarreras();
    }

    /** Cantidad de tramos habitual (editable por institución). */
    public function duracionSugerida(): int
    {
        return match ($this) {
            self::Inicial           => 3,
            self::Primario          => 6,
            self::Secundario        => 6,
            self::SecundarioTecnico => 7,
            self::Terciario         => 3,
            self::Universitario     => 5,
        };
    }

    /** Esquema de evaluación propuesto al crear un ciclo lectivo (editable). */
    public function esquemaSugerido(): EsquemaEvaluacion
    {
        return match ($this) {
            self::Inicial                          => EsquemaEvaluacion::Informes,
            self::Primario                         => EsquemaEvaluacion::Trimestral,
            self::Secundario, self::SecundarioTecnico,
            self::Terciario, self::Universitario   => EsquemaEvaluacion::Cuatrimestral,
        };
    }

    /** Períodos de intensificación / recuperación (diciembre y febrero). */
    public function tieneIntensificacion(): bool
    {
        return in_array($this, [self::Secundario, self::SecundarioTecnico], true);
    }

    /** Se organiza en cursos con divisiones por ciclo lectivo (no en carreras). */
    public function usaCursos(): bool
    {
        return ! $this->usaCarreras();
    }

    /**
     * Tipos de plan de estudio que admite el nivel.
     * - general: plan único (primaria, jardín) o ciclo básico común.
     * - orientacion / especialidad: ciclo superior de secundaria / técnica.
     * - carrera: terciario y universitario.
     *
     * @return array<string, string>
     */
    public function tiposPlan(): array
    {
        return match ($this) {
            self::Inicial, self::Primario => ['general' => 'Plan general'],
            self::Secundario => ['general' => 'Ciclo básico', 'orientacion' => 'Orientación (ciclo superior)'],
            self::SecundarioTecnico => ['general' => 'Ciclo básico', 'especialidad' => 'Especialidad (ciclo superior)'],
            self::Terciario, self::Universitario => ['carrera' => 'Carrera'],
        };
    }

    /** Campos de formación (estructura curricular de técnica y nivel superior). */
    public function usaCamposFormacion(): bool
    {
        return in_array($this, [self::SecundarioTecnico, self::Terciario, self::Universitario], true);
    }

    /** Cómo se llaman las unidades del plan: "Áreas", "Materias", "Unidades curriculares". */
    public function nombreEspacios(): string
    {
        return match ($this) {
            self::Inicial, self::Primario             => 'Áreas',
            self::Secundario, self::SecundarioTecnico => 'Materias',
            self::Terciario, self::Universitario      => 'Unidades curriculares',
        };
    }

    /**
     * Tramos posibles según la duración configurada.
     * Inicial: edades de sala (duración 3 => salas de 3, 4 y 5).
     *
     * @return int[]
     */
    public function grados(int $duracion): array
    {
        if ($this === self::Inicial) {
            return range(max(1, 6 - $duracion), 5);
        }

        return range(1, max(1, $duracion));
    }

    /** "Sala de 4", "3° Grado", "2° Año". */
    public function etiquetaGrado(int $grado): string
    {
        return match ($this) {
            self::Inicial  => "Sala de {$grado}",
            self::Primario => "{$grado}° Grado",
            default        => "{$grado}° Año",
        };
    }

    /** Versión corta para listados: "Sala 4", "3°", "2°". */
    public function etiquetaGradoCorta(int $grado): string
    {
        return $this === self::Inicial ? "Sala {$grado}" : "{$grado}°";
    }

    public function icono(): string
    {
        return match ($this) {
            self::Inicial           => 'bi-balloon',
            self::Primario          => 'bi-pencil',
            self::Secundario        => 'bi-journal-bookmark',
            self::SecundarioTecnico => 'bi-tools',
            self::Terciario         => 'bi-mortarboard',
            self::Universitario     => 'bi-bank',
        };
    }

    public function orden(): int
    {
        return array_search($this, self::cases(), true);
    }

    public static function opciones(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $n) => [$n->value => $n->label()])->all();
    }
}
