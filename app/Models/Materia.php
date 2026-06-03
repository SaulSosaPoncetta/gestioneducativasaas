<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';

    protected $primaryKey = 'id_materia';

    // public $timestamps = false;

    protected $fillable = [
        'id_tipo_materia',
        'nota_promocionable',
        'nota_aprobada',
        'nombre',
        'anio',
        'id_usuario_administrador',
        'id_carrera',
    ];

    // Relaciones
    public function tipoMateria()
    {
        return $this->belongsTo(TipoMateria::class, 'id_tipo_materia', 'id_tipo_materia');
    }

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'id_usuario_administrador', 'id_usuario');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function correlativas()
    {
        return $this->hasMany(Correlativa::class, 'id_materia', 'id_materia');
    }

    public function correlativasDe()
    {
        return $this->hasMany(Correlativa::class, 'id_materia_correlativa', 'id_materia');
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'materia_docente', 'id_materia', 'id_usuario_docente')
                    ->withPivot('id_usuario', 'situacion_revista');
    }

  public function notas()
    {
        return $this->hasMany(NotaCuatrimestre::class, 'id_materia', 'id_materia'); 
    }

    public function notasFinales()
    {
        return $this->hasMany(NotaFinal::class, 'id_materia', 'id_materia');
    }

    public function mesasExamen()
    {
        return $this->hasMany(MesaExamen::class, 'id_materia', 'id_materia');
    }
    public function archivos()
    {  
        return $this->belongsToMany(TipoArchivo::class, 'archivo_materia', 'id_materia', 'id_tipo_archivo');
    }

}
