<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MesaExamen extends Model
{
    use HasFactory;

    protected $table = 'mesas_examen'; 

    protected $primaryKey = 'id_mesa';

    // public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_tipo_mesa',          
        'habilitado',            
        'id_materia',          
        'aula',
        'id_usuario_administracion', 
        'id_ciclo',             
    ];

    protected $casts = [
        'fecha'      => 'datetime', 
        'habilitado' => 'boolean',
    ];

    // Relaciones
    public function tipoMesa()
    {
        return $this->belongsTo(TipoMesaExamen::class, 'id_tipo_mesa', 'id_tipo_mesa');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'id_usuario_administracion', 'id_usuario');
    }

    public function cicloLectivo()
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo', 'id_ciclo');
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'mesa_examen_docente', 'id_mesa', 'id_docente');
    }
}