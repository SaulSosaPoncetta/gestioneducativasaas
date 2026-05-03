<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificacionComentario extends Model
{
    use HasFactory;

    protected $table = 'notificacion_comentario';
    
    public $timestamps = false;

    protected $fillable = [
        'notificacion_id',
        'comentario_id',
    ];

    public function notificacion()
    {
        return $this->belongsTo(Notificacion::class);
    }

    public function comentario()
    {
        return $this->belongsTo(Comentario::class);
    }
}
