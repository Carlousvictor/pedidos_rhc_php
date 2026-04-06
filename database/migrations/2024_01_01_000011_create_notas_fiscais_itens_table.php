<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_fiscais_itens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('nota_fiscal_id');
            $table->uuid('pedido_item_id')->nullable();
            $table->string('codigo')->nullable();
            $table->string('descricao')->nullable();
            $table->decimal('quantidade', 12, 2)->default(0);
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->decimal('valor_total', 14, 2)->default(0);
            $table->string('confronto')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('nota_fiscal_id')->references('id')->on('notas_fiscais')->cascadeOnDelete();
            $table->foreign('pedido_item_id')->references('id')->on('pedidos_itens')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais_itens');
    }
};
