<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('anio');
            $table->foreignId('carrera_id')->constrained('carreras');
            $table->string('correlativa');
        });
    }
    public function down(): void {
        Schema::dropIfExists('materias');
    }
};