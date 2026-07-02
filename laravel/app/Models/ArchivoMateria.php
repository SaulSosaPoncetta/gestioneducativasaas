<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArchivoMateria extends Model
{

    
    use HasFactory;
    protected $table = 'archivo_materia';
    public $incrementing = false;
    // public $timestamps = false;
    protected $primaryKey = null;
    protected $fillable = [
        'id_tipo_archivo',
        'id_materia',
    ];
    
}