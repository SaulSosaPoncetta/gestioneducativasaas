<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';
    protected $primaryKey = 'id_materia';

    public $timestamps = false;

    protected $fillable = ['nombre', 'anio', 'id_plan','correlativa'];

    public function plan()
    {
        return $this->belongsTo(PlanEstudio::class, 'id_plan');
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'docente_materia', 'id_materia', 'id_docente');
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_materia', 'id_materia', 'id_estudiante');
    }
    public function notas()
    {
        return $this->hasMany(NotaCursada::class, 'materia_id');
    }
    public function notasFinales()
    {
        return $this->hasMany(NotaFinal::class, 'materia_id');
    }
    public function mesasExamen()
    {
        return $this->hasMany(MesaExamen::class, 'materia_id');
    }
    public function estudiantesInscritos()
    {
        return $this->belongsToMany(Estudiante::class, 'inscripciones', 'materia_id', 'estudiante_id')
                ->withPivot('fecha_inscripcion');
    }


}
