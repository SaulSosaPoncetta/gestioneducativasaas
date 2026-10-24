<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instituciones (clientes del SaaS) y los niveles que ofrece cada una.
 * Va antes que la de users porque users referencia a instituciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('nombre_corto', 60)->nullable();
            $table->string('cue', 12)->nullable()->comment('Clave Única de Establecimiento');
            $table->string('cuit', 13)->nullable();
            $table->enum('tipo_gestion', ['estatal', 'privada'])->default('privada');
            $table->string('provincia', 60)->default('Buenos Aires');
            $table->string('localidad', 100)->nullable();
            $table->string('domicilio', 150)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('institucion_niveles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->string('nivel', 30)->comment('App\Enums\NivelEducativo');
            $table->string('denominacion', 150)->nullable()->comment('Ej: EP N° 12 "Gral. San Martín"');
            $table->string('cue_anexo', 12)->nullable();
            $table->unsignedTinyInteger('duracion')->comment('Cantidad de salas / grados / años');
            $table->unsignedTinyInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['institucion_id', 'nivel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institucion_niveles');
        Schema::dropIfExists('instituciones');
    }
};
