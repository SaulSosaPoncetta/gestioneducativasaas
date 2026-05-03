<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nota_final', function (Blueprint $table) {
            $table->id();
            $table->decimal('nota');
            $table->date('fecha');
            $table->foreignId('estudiante_id')->constrained('estudiante');
            $table->foreignId('materia_id')->constrained('materias');
        });
    }
    public function down(): void {
        Schema::dropIfExists('nota_final');
    }
};