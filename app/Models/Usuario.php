<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // Usamos este para que funcione el login
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios'; // Obligatorio porque no se llama "users"
    
    public $timestamps = false;    // Obligatorio porque no están en tu migración

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'email',
        'password',
        'rol_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function role()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'usuario_id');
    }
    public function notificaciones()
    {
        return $this->belongsToMany(Notificacion::class, 'usuario_notificacion')
                ->withPivot('leido'); // Importante para acceder al campo "leido"
    }

    // Un Usuario puede haber escrito muchos comentarios
    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'usuario_id');
    }
    public function tramitesComoDocente()
    {
        return $this->belongsToMany(Tramite::class, 'tramite_docente', 'usuario_id', 'tramite_id');
    }
    public function tramitesGestionados()
    {
        return $this->belongsToMany(Tramite::class, 'tramite_secretaria', 'usuario_id', 'tramite_id');
    }
    public function tramitesComoEstudiante()
    {
        return $this->belongsToMany(Tramite::class, 'tramite_estudiante', 'usuario_id', 'tramite_id');
    }

}
