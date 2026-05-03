<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carreras';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'titulo',
    ];
   
    public function resolucion()
    {
        return $this->belongsTo(ResolucionCarrera::class, 'resolucion_carrera_id');
    }

}
