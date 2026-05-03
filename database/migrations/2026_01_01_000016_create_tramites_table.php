<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tramites', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('estado');
            $table->date('fecha_solicitud');
        });
    }
    public function down(): void {
        Schema::dropIfExists('tramites');
    }
};