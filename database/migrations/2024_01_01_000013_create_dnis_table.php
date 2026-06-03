<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dnis', function (Blueprint $table) {
            $table->string('dni');
            $table->unsignedInteger('id_usuario');
            $table->string('tipo_dni')->nullable();

            $table->primary(['dni', 'id_usuario']);
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dnis');
    }
};
