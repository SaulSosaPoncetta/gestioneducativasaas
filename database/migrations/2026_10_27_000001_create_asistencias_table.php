<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asistencia:
 * - asistencia_tomas / asistencias: la diaria por curso que toma preceptoría
 *   (es la oficial para el cómputo de inasistencias).
 * - asistencias_espacio: la de cada materia, que llega desde GestiónAula
 *   por la API (solo lectura en este sistema).
 * - instituciones.api_token: token de la integración (se guarda el hash).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_tomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->date('fecha');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Quién la tomó');
            $table->timestamps();

            $table->unique(['curso_id', 'fecha']);
        });

        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('asistencia_toma_id')->constrained('asistencia_tomas')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('estado', 10)->comment('presente | ausente | tarde | retiro');
            $table->boolean('justificada')->default(false);
            $table->string('motivo', 200)->nullable();
            $table->timestamps();

            $table->unique(['asistencia_toma_id', 'estudiante_id']);
            $table->index(['estudiante_id', 'fecha']);
        });

        Schema::create('asistencias_espacio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->cascadeOnDelete();
            $table->foreignId('asignacion_id')->nullable()->constrained('asignaciones')->nullOnDelete();
            $table->foreignId('espacio_curricular_id')->constrained('espacios_curriculares')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('estado', 10)->comment('presente | ausente | tarde | retiro');
            $table->string('observacion', 200)->nullable();
            $table->string('origen', 20)->default('gestionaula');
            $table->string('referencia', 64)->default('')->comment('Id de la clase en el sistema de origen');
            $table->timestamps();

            $table->unique(['espacio_curricular_id', 'estudiante_id', 'fecha', 'referencia'], 'asist_espacio_unica');
            $table->index(['estudiante_id', 'ciclo_lectivo_id']);
        });

        Schema::table('instituciones', function (Blueprint $table) {
            $table->string('api_token', 64)->nullable()->unique()->after('logo_path')->comment('SHA-256 del token de integración');
            $table->timestamp('api_token_creado_at')->nullable()->after('api_token');
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropUnique(['api_token']);
            $table->dropColumn(['api_token', 'api_token_creado_at']);
        });
        Schema::dropIfExists('asistencias_espacio');
        Schema::dropIfExists('asistencias');
        Schema::dropIfExists('asistencia_tomas');
    }
};
