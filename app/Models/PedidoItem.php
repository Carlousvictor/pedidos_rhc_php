<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoItem extends Model
{
    protected $table = 'pedidos_itens';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'pedido_id',
        'item_id',
        'quantidade',
        'quantidade_atendida',
        'quantidade_recebida',
        'fornecedor',
        'valor_unitario',
        'observacao',
        'observacao_recebimento',
        'item_recebido_id',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'quantidade_atendida' => 'integer',
        'quantidade_recebida' => 'integer',
        'valor_unitario' => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function itemRecebido(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_recebido_id');
    }

    public function remanejamentos(): HasMany
    {
        return $this->hasMany(Remanejamento::class, 'pedido_item_origem_id');
    }
}
