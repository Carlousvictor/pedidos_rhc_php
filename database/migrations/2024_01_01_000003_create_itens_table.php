<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo')->nullable();
            $table->string('referencia')->nullable();
            $table->string('nome');
            $table->string('tipo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens');
    }
};
