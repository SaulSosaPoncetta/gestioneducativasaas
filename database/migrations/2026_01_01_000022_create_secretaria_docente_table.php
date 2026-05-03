<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('secretaria_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secretaria_id')->constrained('secretaria');
            $table->foreignId('docente_id')->constrained('docente');
        });
    }
    public function down(): void {
        Schema::dropIfExists('secretaria_docente');
    }
};