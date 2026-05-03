<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estudiante extends Model
{
    use HasFactory;


    protected $table = 'estudiante';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'carrera_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function tipo()
    {
        return $this->belongsTo(TipoEstudiante::class, 'tipo_estudiante_id');
    }
    public function notas()
    {
        return $this->hasMany(NotaCursada::class, 'estudiante_id');
    }
    public function notasFinales()
    {
        return $this->hasMany(NotaFinal::class, 'estudiante_id');
    }
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'inscripciones', 'estudiante_id', 'materia_id')
                ->withPivot('fecha_inscripcion');
    }

}
