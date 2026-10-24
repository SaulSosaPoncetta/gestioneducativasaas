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
