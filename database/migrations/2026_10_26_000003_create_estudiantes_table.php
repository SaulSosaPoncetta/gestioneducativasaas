<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estudiantes (legajo), tutores / responsables, vínculo entre ambos
 * e inscripciones por ciclo lectivo (a un curso o a una carrera).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('legajo');
            $table->string('apellido', 80);
            $table->string('nombre', 80);
            $table->string('tipo_documento', 20)->default('dni');
            $table->string('documento', 20);
            $table->string('cuil', 13)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('nacionalidad', 60)->default('Argentina');
            $table->string('lugar_nacimiento', 100)->nullable();
            $table->string('domicilio', 150)->nullable();
            $table->string('localidad', 100)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->string('estado', 20)->default('activo')->comment('activo | egresado | baja');

            // Salud y emergencias
            $table->string('grupo_sanguineo', 5)->nullable();
            $table->string('obra_social', 100)->nullable();
            $table->string('numero_afiliado', 40)->nullable();
            $table->text('salud_observaciones')->nullable()->comment('Alergias, medicación, condiciones a tener en cuenta');
            $table->string('emergencia_contacto', 120)->nullable();
            $table->string('emergencia_telefono', 40)->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['institucion_id', 'legajo']);
            $table->unique(['institucion_id', 'tipo_documento', 'documento'], 'estudiante_documento_unico');
            $table->index(['institucion_id', 'apellido', 'nombre']);
        });

        Schema::create('tutores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('apellido', 80);
            $table->string('nombre', 80);
            $table->string('documento', 20);
            $table->string('telefono', 40)->nullable();
            $table->string('telefono_alternativo', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('ocupacion', 100)->nullable();
            $table->string('domicilio', 150)->nullable();
            $table->timestamps();

            $table->unique(['institucion_id', 'documento']);
        });

        Schema::create('estudiante_tutor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('tutores')->cascadeOnDelete();
            $table->string('vinculo', 20);
            $table->boolean('es_responsable')->default(false)->comment('Responsable principal');
            $table->boolean('autorizado_retirar')->default(true);
            $table->boolean('convive')->default(true);
            $table->boolean('recibe_notificaciones')->default(true);
            $table->timestamps();

            $table->unique(['estudiante_id', 'tutor_id']);
        });

        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclos_lectivos')->cascadeOnDelete();
            $table->foreignId('institucion_nivel_id')->constrained('institucion_niveles')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete()->comment('Niveles escolares');
            $table->foreignId('plan_estudio_id')->nullable()->constrained('planes_estudio')->nullOnDelete()->comment('Carrera / plan');
            $table->date('fecha');
            $table->string('condicion', 20)->default('regular')->comment('regular | repitente | ingresante | oyente');
            $table->string('estado', 20)->default('activa')->comment('activa | baja | pase | finalizada');
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja', 200)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['ciclo_lectivo_id', 'estado']);
            $table->index(['curso_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
        Schema::dropIfExists('estudiante_tutor');
        Schema::dropIfExists('tutores');
        Schema::dropIfExists('estudiantes');
    }
};
