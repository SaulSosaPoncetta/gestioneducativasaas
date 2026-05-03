<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanEstudio extends Model
{
    use HasFactory;
    protected $table = 'plan_estudio';
    protected $primaryKey = 'id_plan';

    public $timestamps = false;

    protected $fillable = ['descripcion'];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function materias()
    {
        return $this->hasMany(Materia::class, 'id_plan');
    }

    public function resoluciones()
    {
        return $this->belongsToMany(ResolucionCarrera::class, 'plan_resolucion', 'id_plan', 'id_resolucion');
    }
}
