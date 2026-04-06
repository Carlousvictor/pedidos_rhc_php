<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'numero_pedido',
        'status',
        'unidade_id',
        'usuario_id',
        'fornecedor',
        'atendido_por',
        'espelho_path',
    ];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class, 'unidade_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'atendido_por');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    public function aprovacoes(): HasMany
    {
        return $this->hasMany(Aprovacao::class, 'pedido_id');
    }

    public function alteracoes(): HasMany
    {
        return $this->hasMany(PedidoAlteracao::class, 'pedido_id');
    }

    public function notasFiscais(): HasMany
    {
        return $this->hasMany(NotaFiscal::class, 'pedido_id');
    }

    public function remanejamentosDestino(): HasMany
    {
        return $this->hasMany(Remanejamento::class, 'pedido_destino_id');
    }
}
