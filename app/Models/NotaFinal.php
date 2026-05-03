<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotaFinal extends Model
{
    use HasFactory;

  
    protected $table = 'nota_final';

    public $timestamps = false;
    
    protected $casts = [
        'fecha' => 'date',
        'nota' => 'float',
    ];

    protected $fillable = [
        'nota',
        'fecha',
        'estudiante_id',
        'materia_id',
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
