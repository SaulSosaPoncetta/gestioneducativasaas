<?php

namespace App\Enums;

/**
 * Cómo se divide el ciclo lectivo para evaluar en un nivel.
 * Al crear el ciclo, cada nivel elige su esquema y se generan los períodos.
 */
enum EsquemaEvaluacion: string
{
    case Trimestral    = 'trimestral';
    case Cuatrimestral = 'cuatrimestral';
    case Bimestral     = 'bimestral';
    case Semestral     = 'semestral';
    case Informes      = 'informes';
    case Anual         = 'anual';

    public function label(): string
    {
        return match ($this) {
            self::Trimestral    => 'Trimestral (3 trimestres)',
            self::Cuatrimestral => 'Cuatrimestral (2 cuatrimestres)',
            self::Bimestral     => 'Bimestral (4 bimestres)',
            self::Semestral     => 'Semestral (2 semestres)',
            self::Informes      => 'Informes (mitad de año y final)',
            self::Anual         => 'Anual (un solo período)',
        };
    }

    /** @return string[] Nombres de los períodos, en orden. */
    public function periodos(): array
    {
        return match ($this) {
            self::Trimestral    => ['1° Trimestre', '2° Trimestre', '3° Trimestre'],
            self::Cuatrimestral => ['1° Cuatrimestre', '2° Cuatrimestre'],
            self::Bimestral     => ['1° Bimestre', '2° Bimestre', '3° Bimestre', '4° Bimestre'],
            self::Semestral     => ['1° Semestre', '2° Semestre'],
            self::Informes      => ['Informe de mitad de año', 'Informe final'],
            self::Anual         => ['Anual'],
        };
    }

    public static function opciones(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $e) => [$e->value => $e->label()])->all();
    }
}
