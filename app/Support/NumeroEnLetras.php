<?php

namespace App\Support;

/**
 * Números en letras para constancias: días del mes, años y notas.
 * Cubre de 0 a 9999, que es lo que se usa en estos documentos.
 */
class NumeroEnLetras
{
    private const UNIDADES = [
        'cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez',
        'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve',
        'veinte', 'veintiuno', 'veintidós', 'veintitrés', 'veinticuatro', 'veinticinco', 'veintiséis',
        'veintisiete', 'veintiocho', 'veintinueve',
    ];

    private const DECENAS = [3 => 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];

    private const CENTENAS = [
        1 => 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos',
        'seiscientos', 'setecientos', 'ochocientos', 'novecientos',
    ];

    public const MESES = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    public static function entero(int $n): string
    {
        if ($n < 30) {
            return self::UNIDADES[$n];
        }

        if ($n < 100) {
            $d = intdiv($n, 10);
            $u = $n % 10;

            return self::DECENAS[$d] . ($u ? ' y ' . self::UNIDADES[$u] : '');
        }

        if ($n < 1000) {
            if ($n === 100) {
                return 'cien';
            }
            $resto = $n % 100;

            return self::CENTENAS[intdiv($n, 100)] . ($resto ? ' ' . self::entero($resto) : '');
        }

        $miles = intdiv($n, 1000);
        $resto = $n % 1000;

        return ($miles === 1 ? 'mil' : self::entero($miles) . ' mil') . ($resto ? ' ' . self::entero($resto) : '');
    }

    /** 7 → "siete"; 7.5 → "siete con cincuenta". */
    public static function nota(float $nota): string
    {
        $entero = (int) floor($nota);
        $decimales = (int) round(($nota - $entero) * 100);

        return self::entero($entero) . ($decimales ? ' con ' . self::entero($decimales) : '');
    }
}
