<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTramite extends Model
{
    protected $table = 'tipo_tramite';

    protected $primaryKey = 'id_tipo';

    protected $fillable = ['nombre'];

    public function tramites()
    {
        return $this->hasMany(Tramite::class, 'id_tipo');
    }
}