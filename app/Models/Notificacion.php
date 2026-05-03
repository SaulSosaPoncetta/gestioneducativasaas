<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    public $timestamps = false;

    protected $casts = [
        'fecha_envio' => 'date',
    ];

    protected $fillable = [
        'titulo',
        'contenido',
        'fecha_envio',
        'tipo_notificacion_id',
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoNotificacion::class, 'tipo_notificacion_id');
    }
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_notificacion')
                ->withPivot('leido');
    }
    public function comentarios()
    {
        return $this->belongsToMany(Comentario::class, 'notificacion_comentario');
    }

}
