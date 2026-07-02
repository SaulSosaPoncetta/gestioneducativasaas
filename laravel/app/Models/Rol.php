<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Usuario; 

class Rol extends Model
{
    use HasFactory;

    // Indicamos la tabla si el plural en inglés no coincide (opcional en este caso)
    protected $table = 'roles';

    // Desactivamos los timestamps (created_at/updated_at) porque no están en tu migración
    // public $timestamps = false;

    // Habilitamos la asignación masiva para el campo descripción
    protected $fillable = [
        'descripcion',
    ];

public function usuarios()
{
    return $this->hasMany(Usuario::class, 'id_rol');
}

}
