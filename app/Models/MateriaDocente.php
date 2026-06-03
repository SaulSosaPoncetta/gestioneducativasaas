<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MateriaDocente extends Model
{
    use HasFactory;

    protected $table = 'materia_docente'; 

    public $incrementing = false;
    // public $timestamps = false;

    protected $primaryKey = null; 

    protected $fillable = [
        'id_materia',
        'id_usuario_docente',
        'id_usuario',
        'situacion_revista',
    ];

    // Relaciones
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_usuario_docente', 'id_usuario');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}