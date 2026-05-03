<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plan_resolucion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_estudio_id')->constrained('plan_estudio');
            $table->foreignId('resolucion_carrera_id')->constrained('resolucion_carrera');
        });
    }
    public function down(): void {
        Schema::dropIfExists('plan_resolucion');
    }
};