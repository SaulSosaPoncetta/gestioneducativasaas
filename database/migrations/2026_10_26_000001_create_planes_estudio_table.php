<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Planes de estudio, espacios curriculares y correlatividades.
 *
 * Un plan pertenece a un nivel de la institución y cubre un rango de
 * años/grados: el plan de primaria (1° a 6°), el ciclo básico de secundaria
 * (1° a 3°), una orientación (4° a 6°) o una carrera de nivel superior.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_estudio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('institucion_nivel_id')->constrained('institucion_niveles')->cascadeOnDelete();
            $table->string('tipo', 20)->comment('general | orientacion | especialidad | carrera');
            $table->string('nombre', 150);
            $table->string('titulo', 200)->nullable()->comment('Título que otorga');
            $table->string('resolucion', 100)->nullable()->comment('Norma de aprobación');
            $table->string('modalidad', 20)->nullable()->comment('presencial | semipresencial | distancia');
            $table->unsignedTinyInteger('grado_desde');
            $table->unsignedTinyInteger('grado_hasta');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('espacios_curriculares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('plan_estudio_id')->constrained('planes_estudio')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('codigo', 20)->nullable();
            $table->unsignedTinyInteger('grado')->comment('Año / grado / sala en que se dicta');
            $table->string('regimen', 20)->default('anual')->comment('anual | primer_cuatrimestre | segundo_cuatrimestre');
            $table->string('formato', 20)->default('materia');
            $table->string('campo', 30)->nullable()->comment('Campo de formación');
            $table->unsignedTinyInteger('horas_semanales')->nullable()->comment('Horas cátedra semanales');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['plan_estudio_id', 'grado']);
        });

        Schema::create('correlatividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('espacio_curricular_id')->constrained('espacios_curriculares')->cascadeOnDelete();
            $table->foreignId('requiere_id')->constrained('espacios_curriculares')->cascadeOnDelete();
            $table->string('tipo', 30)->comment('regular_para_cursar | aprobada_para_cursar | aprobada_para_rendir');

            $table->unique(['espacio_curricular_id', 'requiere_id', 'tipo'], 'correlatividad_unica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlatividades');
        Schema::dropIfExists('espacios_curriculares');
        Schema::dropIfExists('planes_estudio');
    }
};
