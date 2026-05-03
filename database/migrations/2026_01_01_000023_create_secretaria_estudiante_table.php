<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('secretaria_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secretaria_id')->constrained('secretaria');
            $table->foreignId('estudiante_id')->constrained('estudiante');
        });
    }
    public function down(): void {
        Schema::dropIfExists('secretaria_estudiante');
    }
};