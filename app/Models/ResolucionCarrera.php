<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResolucionCarrera extends Model
{
    use HasFactory;

    protected $table = 'resolucion_carrera';

    public $timestamps = false;

    protected $fillable = ['descripcion'];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function planes()
    {
        return $this->belongsToMany(PlanEstudio::class, 'plan_resolucion', 'id_resolucion', 'id_plan');
    }
}
