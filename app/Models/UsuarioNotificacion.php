<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsuarioNotificacion extends Model
{
    use HasFactory;

    protected $table = 'usuario_notificacion';
    
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'notificacion_id',
        'leido',
    ];

    // Casting para que el campo sea booleano automáticamente
    protected $casts = [
        'leido' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function notificacion()
    {
        return $this->belongsTo(Notificacion::class);
    }
}
