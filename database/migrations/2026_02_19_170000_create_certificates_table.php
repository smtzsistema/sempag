<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_certificado', function (Blueprint $table) {
            $table->bigIncrements('cer_id');

            // evento
            $table->unsignedBigInteger('eve_id')->index();

            // titulo/descricao interna
            $table->string('cer_nome', 255);

            // tipo (A4 / etc)
            $table->string('cer_tipo', 20)->default('A4')->index();

            // categorias (salva múltiplas em JSON: [1,2,3])
            $table->json('cat_id')->nullable();

            // background (imagem A4 em pé)
            $table->string('cer_fundo', 255)->nullable();

            // espelhar (tudo da esquerda replica na direita)
            $table->string('cer_espelhar', 1)->default('N')->index();

            // layout/configuração (JSON)
            $table->json('cer_config')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_certificado');
    }
};
