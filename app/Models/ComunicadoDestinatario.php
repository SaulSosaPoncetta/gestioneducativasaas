<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComunicadoDestinatario extends Model
{
    protected $table = 'comunicado_destinatarios';

    protected $fillable = ['comunicado_id', 'user_id', 'tipo', 'referencia', 'leido_at', 'confirmado_at', 'email_enviado_at'];

    protected function casts(): array
    {
        return [
            'leido_at'         => 'datetime',
            'confirmado_at'    => 'datetime',
            'email_enviado_at' => 'datetime',
        ];
    }

    public function comunicado(): BelongsTo
    {
        return $this->belongsTo(Comunicado::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
