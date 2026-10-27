<?php

namespace App\Enums;

use Illuminate\Support\Str;

/**
 * Escala de calificación de un nivel.
 * - numerica: 1 a 10 (admite un decimal: 7,5).
 * - conceptual: Excelente, Muy bueno, Bueno, Regular, Insuficiente.
 * - informe: texto cualitativo (nivel inicial).
 */
enum EscalaCalificacion: string
{
    case Numerica   = 'numerica';
    case Conceptual = 'conceptual';
    case Informe    = 'informe';

    /** Escala conceptual, de mayor a menor. */
    public const CONCEPTOS = [
        'E'  => 'Excelente',
        'MB' => 'Muy bueno',
        'B'  => 'Bueno',
        'R'  => 'Regular',
        'I'  => 'Insuficiente',
    ];

    public function label(): string
    {
        return match ($this) {
            self::Numerica   => 'Numérica (1 a 10)',
            self::Conceptual => 'Conceptual (E, MB, B, R, I)',
            self::Informe    => 'Informe escrito',
        };
    }

    public static function sugerida(NivelEducativo $nivel): self
    {
        return $nivel === NivelEducativo::Inicial ? self::Informe : self::Numerica;
    }

    /** Nota mínima de aprobación sugerida (escala numérica). */
    public static function aprobacionSugerida(NivelEducativo $nivel): float
    {
        return match ($nivel) {
            NivelEducativo::Primario                                  => 6.0,
            NivelEducativo::Secundario, NivelEducativo::SecundarioTecnico => 7.0,
            NivelEducativo::Terciario, NivelEducativo::Universitario  => 4.0,
            default                                                   => 6.0,
        };
    }

    /**
     * Normaliza lo que llega ("7", "7,5", "mb", "Muy Bueno") a [valor, numérico].
     * Devuelve null si no es válido para la escala.
     *
     * @return array{0:string, 1:?float}|null
     */
    public function normalizar(mixed $entrada): ?array
    {
        $texto = trim((string) $entrada);

        if ($texto === '') {
            return null;
        }

        if ($this === self::Numerica) {
            $numero = str_replace(',', '.', $texto);

            if (! is_numeric($numero)) {
                return null;
            }

            $numero = round((float) $numero, 1);

            if ($numero < 1 || $numero > 10) {
                return null;
            }

            $valor = fmod($numero, 1.0) == 0.0 ? (string) (int) $numero : str_replace('.', ',', (string) $numero);

            return [$valor, $numero];
        }

        if ($this === self::Conceptual) {
            $buscado = mb_strtolower(Str::ascii($texto));

            foreach (self::CONCEPTOS as $sigla => $nombre) {
                if ($buscado === mb_strtolower($sigla) || $buscado === mb_strtolower(Str::ascii($nombre))) {
                    return [$sigla, null];
                }
            }

            return null;
        }

        return null; // informe: no lleva valor
    }

    /** Etiqueta para mostrar el valor guardado ("7,5", "Muy bueno"). */
    public function mostrar(?string $valor): string
    {
        if ($valor === null) {
            return '';
        }

        return $this === self::Conceptual ? (self::CONCEPTOS[$valor] ?? $valor) : $valor;
    }

    public static function opciones(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $e) => [$e->value => $e->label()])->all();
    }
}
