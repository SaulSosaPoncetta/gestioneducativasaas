<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nivel superior:
 * - cursadas: inscripción del estudiante a cada unidad curricular por ciclo,
 *   con su condición al cierre (regular, promocionado, libre...).
 * - mesas_examen / mesa_inscripciones: llamados a examen final, inscriptos y acta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('espacio_curricular_id')->constrained('espacios_curriculares')->cascadeOnDelete();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->cascadeOnDelete();
            $table->string('comision', 30)->default('Única');
            $table->date('fecha_inscripcion');
            $table->string('condicion', 20)->default('cursando')->comment('cursando | regular | promocionado | libre | abandono');
            $table->decimal('nota', 4, 2)->nullable()->comment('Nota de cursada / promoción');
            $table->date('fecha_condicion')->nullable();
            $table->date('vence')->nullable()->comment('Vencimiento de la regularidad');
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->unique(['estudiante_id', 'espacio_curricular_id', 'ciclo_lectivo_id'], 'cursada_unica');
            $table->index(['espacio_curricular_id', 'ciclo_lectivo_id', 'comision']);
        });

        Schema::create('mesas_examen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('espacio_curricular_id')->constrained('espacios_curriculares')->cascadeOnDelete();
            $table->string('llamado', 80)->comment('Ej: Diciembre 2026 - 1° llamado');
            $table->dateTime('fecha');
            $table->string('aula', 40)->nullable();
            $table->foreignId('presidente_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->string('vocales', 200)->nullable();
            $table->date('inscripcion_desde');
            $table->date('inscripcion_hasta');
            $table->boolean('admite_libres')->default(true);
            $table->string('estado', 20)->default('abierta')->comment('abierta | cerrada');
            $table->string('libro', 20)->nullable();
            $table->string('folio', 20)->nullable();
            $table->timestamp('cerrada_at')->nullable();
            $table->timestamps();

            $table->index(['espacio_curricular_id', 'fecha']);
        });

        Schema::create('mesa_inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('mesa_examen_id')->constrained('mesas_examen')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->string('condicion', 10)->comment('regular | libre');
            $table->decimal('nota', 4, 2)->nullable();
            $table->string('resultado', 15)->nullable()->comment('aprobado | desaprobado | ausente');
            $table->timestamps();

            $table->unique(['mesa_examen_id', 'estudiante_id']);
            $table->index(['estudiante_id', 'resultado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesa_inscripciones');
        Schema::dropIfExists('mesas_examen');
        Schema::dropIfExists('cursadas');
    }
};
