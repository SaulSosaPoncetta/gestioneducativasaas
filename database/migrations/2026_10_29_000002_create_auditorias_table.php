<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registro de cambios y accesos. Solo se inserta: nunca se edita.
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('usuario_nombre', 150)->nullable()->comment('Copia: se conserva aunque se borre el usuario');
            $table->string('evento', 20)->comment('creado | actualizado | eliminado | ingreso | salida | ingreso_fallido');
            $table->string('modelo_tipo', 60)->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->string('descripcion', 255);
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->string('origen', 10)->default('web')->comment('web | api | consola');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['institucion_id', 'created_at']);
            $table->index(['modelo_tipo', 'modelo_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
