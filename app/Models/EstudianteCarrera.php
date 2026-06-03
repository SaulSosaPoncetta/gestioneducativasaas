<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstudianteCarrera extends Model
{
    use HasFactory;

    protected $table = 'estudiante_carrera';

    public $incrementing = false;
    // public $timestamps = false;

    protected $primaryKey = null; 

    protected $fillable = [
        'id_usuario',
        'id_carrera',
        'cohorte',
        'id_estado_carrera',
    ];

    protected $casts = [
        'cohorte' => 'date',
    ];

    // Relaciones
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_usuario', 'id_usuario');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function estadoCarrera()
    {
        return $this->belongsTo(EstadoCarrera::class, 'id_estado_carrera', 'id_estado_carrera');
    }
}