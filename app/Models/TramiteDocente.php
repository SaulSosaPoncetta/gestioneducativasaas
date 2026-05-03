<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TramiteDocente extends Model
{
    use HasFactory;
    protected $table = 'tramite_docente';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'tramite_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'tramite_id');
    }
}
