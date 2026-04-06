<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Remanejamento extends Model
{
    protected $table = 'remanejamentos';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'pedido_item_origem_id',
        'pedido_destino_id',
        'item_id',
        'quantidade',
        'quantidade_recebida',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'quantidade_recebida' => 'integer',
    ];

    public function pedidoItemOrigem(): BelongsTo
    {
        return $this->belongsTo(PedidoItem::class, 'pedido_item_origem_id');
    }

    public function pedidoDestino(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_destino_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
