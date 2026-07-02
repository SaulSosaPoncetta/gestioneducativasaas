<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia_docente', function (Blueprint $table) {
            $table->unsignedInteger('id_materia');
            $table->unsignedInteger('id_usuario_docente');
            $table->unsignedInteger('id_usuario')->nullable(); // usuario general relacionado
            $table->string('situacion_revista')->nullable();

            $table->primary(['id_materia', 'id_usuario_docente']);
            $table->foreign('id_materia')->references('id_materia')->on('materias');
            $table->foreign('id_usuario_docente')->references('id_usuario')->on('docentes');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia_docente');
    }
};
