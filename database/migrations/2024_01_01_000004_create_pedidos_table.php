<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_pedido')->unique();
            $table->string('status')->default('Aguardando Aprovação');
            $table->uuid('unidade_id');
            $table->uuid('usuario_id');
            $table->string('fornecedor')->nullable();
            $table->uuid('atendido_por')->nullable();
            $table->timestamps();

            $table->foreign('unidade_id')->references('id')->on('unidades');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('atendido_por')->references('id')->on('usuarios')->nullOnDelete();

            $table->index('status');
            $table->index('unidade_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
