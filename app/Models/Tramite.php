<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tramite extends Model
{
    use HasFactory;

    // Laravel por defecto buscaría "tramites" (que coincide), 
    // pero lo declaramos para evitar confusiones con el plural en inglés.
    protected $table = 'tramites';

  
    public $timestamps = false;

    // Convertimos 'fecha_solicitud' a un objeto Carbon (fecha)
    protected $casts = [
        'fecha_solicitud' => 'date',
    ];

    protected $fillable = [
        'tipo',
        'estado',
        'fecha_solicitud',
    ];
    public function secretariasAsignadas()
    {
        return $this->belongsToMany(Usuario::class, 'tramite_secretaria', 'tramite_id', 'usuario_id');
    }
    public function docentes()
    {
        return $this->belongsToMany(Usuario::class, 'tramite_docente', 'tramite_id', 'usuario_id');
    }
    public function estudiantes()
    {
        return $this->belongsToMany(Usuario::class, 'tramite_estudiante', 'tramite_id', 'usuario_id');
    }

}
