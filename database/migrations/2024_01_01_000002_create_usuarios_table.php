<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->string('nome');
            $table->string('role')->default('solicitante');
            $table->uuid('unidade_id')->nullable();
            $table->json('permissoes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('unidade_id')->references('id')->on('unidades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
