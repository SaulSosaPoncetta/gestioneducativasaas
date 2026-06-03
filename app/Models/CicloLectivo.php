<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CicloLectivo extends Model
{
    use HasFactory;

    protected $table = 'ciclos_lectivos'; 

    protected $primaryKey = 'id_ciclo';

    // public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'id_usuario_administracion', 
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    // Relaciones
    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'id_usuario_administracion', 'id_usuario');
    }

    public function mesasExamen()
    {
        return $this->hasMany(MesaExamen::class, 'id_ciclo', 'id_ciclo');
    }
}