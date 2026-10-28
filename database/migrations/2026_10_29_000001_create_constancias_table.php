<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registro de constancias emitidas (numeradas por año y verificables por código).
        Schema::create('constancias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->string('tipo', 20)->comment('alumno_regular | examen');
            $table->unsignedSmallInteger('anio');
            $table->unsignedInteger('numero');
            $table->string('codigo', 16)->unique()->comment('Código de verificación');
            $table->foreignId('inscripcion_id')->nullable()->constrained('inscripciones')->nullOnDelete();
            $table->foreignId('mesa_inscripcion_id')->nullable()->constrained('mesa_inscripciones')->nullOnDelete();
            $table->string('presentar_ante', 150)->nullable();
            $table->json('datos')->comment('Copia de los datos al momento de emitir');
            $table->foreignId('emitida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origen', 10)->default('gestion')->comment('gestion | portal');
            $table->timestamp('anulada_at')->nullable();
            $table->string('motivo_anulacion', 200)->nullable();
            $table->timestamps();

            $table->unique(['institucion_id', 'anio', 'numero']);
            $table->index(['estudiante_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constancias');
    }
};
