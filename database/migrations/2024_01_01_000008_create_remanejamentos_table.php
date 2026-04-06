<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remanejamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_item_origem_id');
            $table->uuid('pedido_destino_id');
            $table->uuid('item_id');
            $table->integer('quantidade')->default(0);
            $table->integer('quantidade_recebida')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->foreign('pedido_item_origem_id')->references('id')->on('pedidos_itens')->cascadeOnDelete();
            $table->foreign('pedido_destino_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('itens');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remanejamentos');
    }
};
