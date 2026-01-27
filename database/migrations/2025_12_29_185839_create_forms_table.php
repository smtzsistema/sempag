<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_formularios', function (Blueprint $table) {
            $table->id('form_id')->comment('Id do formulário');

            $table->unsignedBigInteger('eve_id')->comment('Id do evento (tbl_eventos.eve_id)');
            $table->foreign('eve_id')
                ->references('eve_id')->on('tbl_eventos')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('cat_id')->comment('Id da categoria (tbl_categorias.cat_id)');
            $table->foreign('cat_id')
                ->references('cat_id')->on('tbl_categorias')
                ->cascadeOnDelete();

            $table->string('form_nome')->comment('Nome do formulário');
            $table->unsignedInteger('form_versao')->default(1)->comment('Versão do formulário');
            $table->boolean('form_ativo')->default(true)->comment('Ativa (1) / Inativa (0)');

            $table->timestamps();

            $table->unique(['cat_id', 'form_versao']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_formularios');
    }
};
