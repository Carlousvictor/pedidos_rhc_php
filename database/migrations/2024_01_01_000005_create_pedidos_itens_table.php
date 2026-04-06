<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_itens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_id');
            $table->uuid('item_id');
            $table->integer('quantidade')->default(0);
            $table->integer('quantidade_atendida')->default(0);
            $table->integer('quantidade_recebida')->default(0);
            $table->string('fornecedor')->nullable();
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->text('observacao')->nullable();
            $table->text('observacao_recebimento')->nullable();
            $table->uuid('item_recebido_id')->nullable();
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('itens');
            $table->foreign('item_recebido_id')->references('id')->on('itens')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_itens');
    }
};
