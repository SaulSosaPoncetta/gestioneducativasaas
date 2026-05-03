<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comentario extends Model
{
    use HasFactory;


    protected $table = 'comentario';

    public $timestamps = false;

    protected $fillable = [
        'contenido',
        'usuario_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
    public function notificaciones()
    {
        return $this->belongsToMany(Notificacion::class, 'notificacion_comentario');
    }

}
