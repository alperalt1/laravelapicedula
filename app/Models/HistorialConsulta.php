<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialConsulta extends Model
{
    protected $table = 'historial_consultas';

    protected $fillable = [
        'user_id',
        'cedula_consultada',
        'resultado_json',
        'ip_address'
    ];

    protected $casts = [
        'resultado_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
