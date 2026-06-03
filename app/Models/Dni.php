<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dni extends Model
{
    use HasFactory;

    protected $table = 'dnis';

    public $incrementing = false;
    // public $timestamps = false;

    protected $primaryKey = null; // Clave primaria compuesta

    protected $fillable = [
        'dni',
        'id_usuario',
        'tipo_dni',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}