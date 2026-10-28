<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Período en que los estudiantes de nivel superior pueden inscribirse
 * solos a cursadas desde su portal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ciclos_lectivos', function (Blueprint $table) {
            $table->date('inscripcion_cursadas_desde')->nullable()->after('fecha_fin');
            $table->date('inscripcion_cursadas_hasta')->nullable()->after('inscripcion_cursadas_desde');
        });
    }

    public function down(): void
    {
        Schema::table('ciclos_lectivos', function (Blueprint $table) {
            $table->dropColumn(['inscripcion_cursadas_desde', 'inscripcion_cursadas_hasta']);
        });
    }
};
