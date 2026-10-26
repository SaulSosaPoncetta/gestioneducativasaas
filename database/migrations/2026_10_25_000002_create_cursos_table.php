<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cursos de un ciclo lectivo: "Sala de 4 Roja", "3° Grado A", "2° Año B".
 * Para los niveles escolares (inicial, primario, secundario y técnica).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->cascadeOnDelete();
            $table->foreignId('institucion_nivel_id')->constrained('institucion_niveles')->cascadeOnDelete();
            $table->unsignedTinyInteger('grado')->comment('Inicial: edad de la sala. Resto: 1°, 2°...');
            $table->string('division', 20)->comment('A, B, Roja, Única...');
            $table->string('turno', 20);
            $table->foreignId('preceptor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aula', 40)->nullable();
            $table->unsignedSmallInteger('cupo')->nullable();
            $table->timestamps();

            $table->unique(['ciclo_lectivo_id', 'institucion_nivel_id', 'grado', 'division'], 'cursos_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
