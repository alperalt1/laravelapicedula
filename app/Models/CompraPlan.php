<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraPlan extends Model
{
    use HasFactory;

    protected $table = 'compras_planes';

    protected $fillable = [
        'user_id',
        'plan_id',
        'monto',
        'metodo_pago',
        'referencia_pago',
        'estado',
        'consultas_adquiridas'
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el plan
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
