<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_alteracoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_id');
            $table->uuid('usuario_id')->nullable();
            $table->string('usuario_nome')->nullable();
            $table->string('tipo');
            $table->string('item_nome')->nullable();
            $table->string('item_codigo')->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_novo')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_alteracoes');
    }
};
