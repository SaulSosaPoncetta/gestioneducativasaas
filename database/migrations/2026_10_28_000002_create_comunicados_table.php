<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comunicados de la institución y sus destinatarios (con lectura y
 * confirmación). Los destinatarios se calculan al publicar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo', 150);
            $table->text('cuerpo');
            $table->string('alcance', 20)->default('institucion')->comment('institucion | niveles | cursos | carreras');
            $table->json('alcance_ids')->nullable();
            $table->json('para')->comment('familias, estudiantes, docentes');
            $table->boolean('requiere_confirmacion')->default(false);
            $table->boolean('enviar_email')->default(true);
            $table->string('adjunto_path')->nullable();
            $table->string('adjunto_nombre')->nullable();
            $table->timestamp('publicado_at')->nullable()->comment('NULL = borrador');
            $table->timestamps();

            $table->index(['institucion_id', 'publicado_at']);
        });

        Schema::create('comunicado_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comunicado_id')->constrained('comunicados')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 15)->comment('familia | estudiante | docente');
            $table->string('referencia', 200)->nullable()->comment('Ej: "Familia de Sofía Romero (1° Grado A)"');
            $table->timestamp('leido_at')->nullable();
            $table->timestamp('confirmado_at')->nullable();
            $table->timestamp('email_enviado_at')->nullable();
            $table->timestamps();

            $table->unique(['comunicado_id', 'user_id']);
            $table->index(['user_id', 'leido_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicado_destinatarios');
        Schema::dropIfExists('comunicados');
    }
};
