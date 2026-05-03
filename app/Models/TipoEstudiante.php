<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEstudiante extends Model
{
    use HasFactory;

    protected $table = 'tipo_estudiante';
  
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
    ];

 
    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'tipo_estudiante_id');
    }
}
