<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('dni');
            $table->string('email');
            $table->string('password');

            $table->foreignId('rol_id')->constrained('roles');
        });
    }
    public function down(): void {
        Schema::dropIfExists('usuarios');
    }
};