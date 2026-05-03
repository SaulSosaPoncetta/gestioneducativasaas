<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocenteMateria extends Model
{
    protected $table = 'docente_materia';
    
   
    public $timestamps = false;

    protected $fillable = [
        'id_docente',
        'id_materia'
    ];

    
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia');
    }
}

