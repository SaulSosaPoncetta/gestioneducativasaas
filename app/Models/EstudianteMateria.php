<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudianteMateria extends Model
{
    protected $table = 'estudiante_materia';
    
   
    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'id_materia'
    ];

    
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia');
    }
}
