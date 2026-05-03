<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('contenido');
            $table->date('fecha_envio');
            $table->foreignId('tipo_notificacion_id')->constrained('tipo_notificaciones');
        });
    }
    public function down(): void {
        Schema::dropIfExists('notificaciones');
    }
};