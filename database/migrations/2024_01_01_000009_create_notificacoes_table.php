<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('usuario_id');
            $table->uuid('pedido_id')->nullable();
            $table->string('tipo');
            $table->text('mensagem');
            $table->boolean('lida')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('pedido_id')->references('id')->on('pedidos')->nullOnDelete();

            $table->index(['usuario_id', 'lida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
