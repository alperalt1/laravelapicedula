<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suscripcion extends Model
{
    protected $table = 'suscripcion';
    protected $fillable = [
        'user_id',
        'plan_id',
        'consultas_disponibles',
        'fecha_inicio',
        'fecha_vencimiento',
        'is_active'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

}
