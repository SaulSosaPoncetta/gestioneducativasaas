<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecretariaEstudiante extends Model
{
    use HasFactory;
    protected $table = 'secretaria_estudiante';

    public $timestamps = false;

    protected $fillable = [
        'secretaria_id',
        'estudiante_id'
    ];
}
