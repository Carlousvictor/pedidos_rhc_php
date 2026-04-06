<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaFiscal extends Model
{
    protected $table = 'notas_fiscais';

    protected $keyType = 'string';

    public $incrementing = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'pedido_id',
        'numero',
        'serie',
        'chave_acesso',
        'data_emissao',
        'valor_total',
        'fornecedor_nome',
        'fornecedor_cnpj',
        'pdf_path',
        'xml_path',
        'status',
        'uploaded_by',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'valor_total' => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'uploaded_by');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(NotaFiscalItem::class, 'nota_fiscal_id');
    }
}
