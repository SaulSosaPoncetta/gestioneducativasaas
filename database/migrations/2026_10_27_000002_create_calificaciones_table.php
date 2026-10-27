<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Calificaciones por período (llegan desde GestiónAula o se corrigen a mano)
 * y configuración de la escala de cada nivel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institucion_niveles', function (Blueprint $table) {
            $table->string('escala', 20)->nullable()->after('duracion')->comment('numerica | conceptual | informe (null = sugerida)');
            $table->decimal('nota_aprobacion', 4, 2)->nullable()->after('escala');
            $table->string('aprueba_desde', 5)->nullable()->after('nota_aprobacion')->comment('Escala conceptual: sigla mínima');
        });

        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->cascadeOnDelete();
            $table->foreignId('periodo_evaluacion_id')->constrained('periodos_evaluacion')->cascadeOnDelete();
            $table->foreignId('espacio_curricular_id')->constrained('espacios_curriculares')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->string('valor', 20)->nullable()->comment('Nota numérica o sigla conceptual');
            $table->decimal('valor_numerico', 4, 2)->nullable();
            $table->text('informe')->nullable()->comment('Informe cualitativo (nivel inicial)');
            $table->string('observacion', 255)->nullable();
            $table->string('origen', 20)->default('gestionaula')->comment('gestionaula | manual');
            $table->foreignId('modificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['estudiante_id', 'espacio_curricular_id', 'periodo_evaluacion_id'], 'calificacion_unica');
            $table->index(['curso_id', 'periodo_evaluacion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
        Schema::table('institucion_niveles', function (Blueprint $table) {
            $table->dropColumn(['escala', 'nota_aprobacion', 'aprueba_desde']);
        });
    }
};
