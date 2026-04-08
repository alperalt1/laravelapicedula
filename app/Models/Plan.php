<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'limit_consultas',
        'duration_days',
        'information'
    ];

    protected $casts = [
        'information' => 'array',
        'price' => 'decimal:2'
    ];

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class);
    }
    public function compras(): HasMany
    {
        return $this->hasMany(CompraPlan::class);
    }
}
