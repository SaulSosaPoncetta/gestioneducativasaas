<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carreras';

    protected $primaryKey = 'id_carrera';

    // public $timestamps = false;

    protected $fillable = [
        'nombre',
        'titulo',
    ];

    // Relaciones
    public function resoluciones()
    {
        return $this->belongsToMany(ResolucionCarrera::class, 'plan_resolucion', 'id_carrera', 'id_resolucion')
                ->withPivot('id_plan', 'id_tipo_archivo', 'total_horas', 'fecha');
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_carrera', 'id_carrera', 'id_usuario')
                    ->withPivot('cohorte', 'id_estado_carrera');
    }
}
