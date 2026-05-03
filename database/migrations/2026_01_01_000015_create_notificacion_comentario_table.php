<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notificacion_comentario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notificacion_id')->constrained('notificaciones');
            $table->foreignId('comentario_id')->constrained('comentario');
        });
    }
    public function down(): void {
        Schema::dropIfExists('notificacion_comentario');
    }
};