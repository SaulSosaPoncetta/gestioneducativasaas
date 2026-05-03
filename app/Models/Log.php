<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    protected $table = 'log';

    public $timestamps = false;

    // Casting para que 'fecha_hora' se comporte como un objeto Carbon (fecha)
    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    protected $fillable = [
        'usuario_id',
        'descripcion',
        'fecha_hora',
    ];

    // Un Log PERTENECE a un Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
