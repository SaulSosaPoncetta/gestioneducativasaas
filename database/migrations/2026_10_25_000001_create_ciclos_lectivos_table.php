<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ciclos lectivos y sus períodos de evaluación.
 * Cada nivel de la institución tiene sus propios períodos dentro del ciclo
 * (ej. primaria en trimestres y secundaria en cuatrimestres).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciclos_lectivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->string('nombre', 80);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 20)->default('planificacion')->comment('planificacion | en_curso | cerrado');
            $table->timestamps();

            $table->unique(['institucion_id', 'anio']);
        });

        Schema::create('periodos_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->cascadeOnDelete();
            $table->foreignId('institucion_nivel_id')->constrained('institucion_niveles')->cascadeOnDelete();
            $table->string('nombre', 60);
            $table->string('tipo', 20)->default('regular')->comment('regular | intensificacion');
            $table->unsignedTinyInteger('orden')->default(1);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('cerrado')->default(false)->comment('Cerrado: no se cargan ni modifican notas');
            $table->timestamps();

            $table->index(['ciclo_lectivo_id', 'institucion_nivel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_evaluacion');
        Schema::dropIfExists('ciclos_lectivos');
    }
};
