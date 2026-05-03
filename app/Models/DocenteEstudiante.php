<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocenteEstudiante extends Model
{
    use HasFactory;
    protected $table = 'docente_estudiante';
    
    public $timestamps = false;

    protected $fillable = [
        'docente_id',
        'estudiante_id'
    ];
}
