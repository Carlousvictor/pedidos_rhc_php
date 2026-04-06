<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $table = 'itens';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'codigo',
        'referencia',
        'nome',
        'tipo',
    ];

    public function pedidoItens(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'item_id');
    }
}
