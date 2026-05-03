<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('docente_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docente');
            $table->foreignId('estudiante_id')->constrained('estudiante');
        });
    }
    public function down(): void {
        Schema::dropIfExists('docente_estudiante');
    }
};