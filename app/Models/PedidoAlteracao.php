<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoAlteracao extends Model
{
    protected $table = 'pedido_alteracoes';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'pedido_id',
        'usuario_id',
        'usuario_nome',
        'tipo',
        'item_nome',
        'item_codigo',
        'valor_anterior',
        'valor_novo',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
