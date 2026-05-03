<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoNotificacion extends Model
{
    use HasFactory;

    
    protected $table = 'tipo_notificaciones';

    
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
    ];
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'tipo_notificacion_id');
    }
}