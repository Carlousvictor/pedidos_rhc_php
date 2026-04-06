<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    protected $table = 'unidades';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'nome',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'unidade_id');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'unidade_id');
    }
}
