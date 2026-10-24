<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reemplaza la migración de users que trae Laravel.
 * Agrega institución, rol, DNI y estado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->nullable()
                ->comment('NULL solo para superadmin')
                ->constrained('instituciones')->restrictOnDelete();
            $table->string('nombre', 80);
            $table->string('apellido', 80);
            $table->string('dni', 15)->nullable();
            $table->string('email', 150)->nullable()->unique();
            $table->string('password');
            $table->string('rol', 20)->comment('App\Enums\Rol');
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['institucion_id', 'dni']);
            $table->index('dni');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
