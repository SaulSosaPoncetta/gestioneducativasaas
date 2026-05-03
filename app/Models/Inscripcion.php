<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inscripcion extends Model
{
    use HasFactory;

    
    protected $table = 'inscripciones';

    public $timestamps = false;

    protected $casts = [
        'fecha_inscripcion' => 'date',
    ];

    protected $fillable = [
        'estudiante_id',
        'materia_id',
        'fecha_inscripcion',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
}
