<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Docentes y sus asignaciones (designaciones) a espacios curriculares por
 * ciclo lectivo: en un curso (niveles escolares) o en una comisión (superior).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('apellido', 80);
            $table->string('nombre', 80);
            $table->string('documento', 20);
            $table->string('cuil', 13)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('domicilio', 150)->nullable();
            $table->string('localidad', 100)->nullable();
            $table->string('titulo', 200)->nullable()->comment('Título docente / habilitante');
            $table->string('legajo', 30)->nullable()->comment('Legajo / ficha censal');
            $table->date('fecha_ingreso')->nullable();
            $table->string('estado', 20)->default('activo')->comment('activo | licencia | baja');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['institucion_id', 'documento']);
        });

        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('docente_id')->constrained('docentes')->cascadeOnDelete();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->cascadeOnDelete();
            $table->foreignId('espacio_curricular_id')->constrained('espacios_curriculares')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->cascadeOnDelete()->comment('Niveles escolares');
            $table->string('comision', 30)->nullable()->comment('Nivel superior');
            $table->string('situacion', 20)->default('titular')->comment('titular | provisional | interino | suplente');
            $table->unsignedTinyInteger('horas')->nullable()->comment('Horas cátedra semanales');
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->string('estado', 20)->default('activa')->comment('activa | finalizada');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['ciclo_lectivo_id', 'curso_id', 'espacio_curricular_id'], 'asig_curso_espacio');
            $table->index(['docente_id', 'ciclo_lectivo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
        Schema::dropIfExists('docentes');
    }
};
