<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_categorias', function (Blueprint $table) {
            $table->id('cat_id')->comment('Id da categoria');

            $table->unsignedBigInteger('eve_id')->index()->comment('Id do evento (tbl_eventos.eve_id)');
            $table->foreign('eve_id')
                ->references('eve_id')->on('tbl_eventos')
                ->cascadeOnDelete();

            $table->string('cat_nome')->comment('Nome da categoria');
            $table->text('cat_descricao')->nullable()->comment('Descrição que fica descrita no quadrado da categoria abaixo dela');

            // liberação/ocultação por data e hora
            $table->dateTime('cat_date_start')->nullable()->comment('Define quando a categoria deve aparecer no inscrição');
            $table->dateTime('cat_date_end')->nullable()->comment('Define quando a categoria deve sumir no inscrição');

            // banner por categoria
            $table->string('cat_banner_path')->nullable()->comment('Caminho onde se armazena o banner dessa categoria');

            $table->boolean('cat_ativo')->default(true)->comment('Ativa (1) / Inativa (0)');
            $table->boolean('cat_aprova')->default(false)->comment('Exige aprovar/reprovar (1) / Não exige (0)');
            $table->json('cat_settings')->nullable()->comment('Config JSON da categoria');

            // padrões Laravel (não renomear)
            $table->timestamps(); // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_categorias');
    }
};
