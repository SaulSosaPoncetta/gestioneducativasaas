<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanResolucion extends Model
{
    use HasFactory;
    protected $table = 'plan_resolucion';
    public $timestamps = false;

    protected $fillable = [
        'plan_estudio_id',
        'resolucion_carrera_id'
    ];


    public function plan() {
        return $this->belongsTo(PlanEstudio::class, 'plan_estudio_id');
    }

    public function resolucion() {
        return $this->belongsTo(ResolucionCarrera::class, 'resolucion_carrera_id');
    }
}
