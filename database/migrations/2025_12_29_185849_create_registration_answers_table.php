<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_inscricao_respostas', function (Blueprint $table) {
            $table->id('res_id')->comment('Id da resposta da inscrição');

            $table->unsignedBigInteger('ins_id')->comment('Id da inscrição (tbl_inscricao.ins_id)');
            $table->unsignedBigInteger('fic_id')->comment('Id do campo da ficha (tbl_ficha.fic_id)');
            $table->unsignedBigInteger('eve_id')->comment('Id do evento (tbl_eventos.eve_id)');

            // Indexes úteis
            $table->index('ins_id');
            $table->index('fic_id');
            $table->index('eve_id');

            // FK do campo
            $table->foreign('fic_id')
                ->references('fic_id')->on('tbl_ficha')
                ->cascadeOnDelete();
            // FK da inscrição
            $table->foreign('ins_id')
                ->references('ins_id')->on('tbl_inscricao')
                ->cascadeOnDelete();

            $table->longText('res_valor_texto')->nullable()->comment('Valor em texto (inputs simples)');
            $table->json('res_valor_json')->nullable()->comment('Valor em JSON (múltiplas seleções/objetos)');

            // padrões Laravel (não renomear)
            $table->timestamps(); // created_at / updated_at

            $table->unique(['ins_id', 'fic_id'], 'uniq_inscricao_campo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_inscricao_respostas');
    }
};
