<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiante');
            $table->foreignId('materia_id')->constrained('materias');
            $table->date('fecha_inscripcion');
        });
    }
    public function down(): void {
        Schema::dropIfExists('inscripciones');
    }
};