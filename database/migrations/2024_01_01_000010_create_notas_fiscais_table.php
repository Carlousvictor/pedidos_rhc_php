<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_id');
            $table->string('numero')->nullable();
            $table->string('serie')->nullable();
            $table->string('chave_acesso')->nullable();
            $table->timestamp('data_emissao')->nullable();
            $table->decimal('valor_total', 14, 2)->default(0);
            $table->string('fornecedor_nome')->nullable();
            $table->string('fornecedor_cnpj')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('status')->default('pendente');
            $table->uuid('uploaded_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};
