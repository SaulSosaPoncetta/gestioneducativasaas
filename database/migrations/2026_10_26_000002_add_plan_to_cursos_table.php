<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada curso sigue un plan de estudio (ej. 4° A -> Bachiller en Ciencias Sociales).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->foreignId('plan_estudio_id')->nullable()->after('institucion_nivel_id')
                ->constrained('planes_estudio')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_estudio_id');
        });
    }
};
