<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MesaExamen extends Model
{
    use HasFactory;

  
    protected $table = 'mesa_examen';

    public $timestamps = false;

    protected $casts = [
        'fecha' => 'date',
    ];

    protected $fillable = [
        'fecha',
        'turno',
        'materia_id',
        'ciclo_lectivo_id',
        'aula',
    ];

  
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

       public function cicloLectivo()
    {
        return $this->belongsTo(CicloLectivo::class, 'ciclo_lectivo_id');
    }
}
