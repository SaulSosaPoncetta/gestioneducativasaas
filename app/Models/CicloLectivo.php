<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CicloLectivo extends Model
{
    use HasFactory;

  
    protected $table = 'ciclo_lectivo';

    public $timestamps = false;

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    protected $fillable = [
        'anio',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
    ];
    public function mesasExamen()
    {
        return $this->hasMany(MesaExamen::class, 'ciclo_lectivo_id');
    }

}
