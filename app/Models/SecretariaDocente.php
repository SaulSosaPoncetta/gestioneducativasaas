<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecretariaDocente extends Model
{
    use HasFactory;
    protected $table = 'secretaria_docente';
    
    public $timestamps = false;

    protected $fillable = [
        'secretaria_id',
        'docente_id'
    ];
}
