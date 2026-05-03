<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('resolucion_carrera', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
        });
    }
    public function down(): void {
        Schema::dropIfExists('resolucion_carrera');
    }
};
