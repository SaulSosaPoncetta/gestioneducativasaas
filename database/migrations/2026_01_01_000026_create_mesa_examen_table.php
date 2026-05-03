<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mesa_examen', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('turno');
            $table->string('aula');
            $table->foreignId('materia_id')->constrained('materias');
            $table->foreignId('ciclo_lectivo_id')->constrained('ciclo_lectivo');
        });
    }
    public function down(): void {
        Schema::dropIfExists('mesa_examen');
    }

};
