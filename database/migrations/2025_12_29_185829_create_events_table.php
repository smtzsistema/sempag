<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_eventos', function (Blueprint $table) {
            $table->id('eve_id')->comment('Id do evento');

            $table->unsignedBigInteger('org_id')->index()->comment('Id da Organizadora (tbl_organizadoras.org_id)');
            $table->foreign('org_id')
                ->references('org_id')->on('tbl_organizadoras')
                ->cascadeOnDelete();

            $table->string('eve_nome')->comment('Nome do evento');
            $table->string('eve_slug')->nullable()->comment('Slug do evento (não está em uso)');
            $table->string('eve_descricao')->nullable()->comment('Descrição que fica abaixo do nome do evento');

            $table->string('eve_token')->unique()->comment('Token que passa na URL de cada evento (recomendado não editar quando criado)');

            $table->dateTime('eve_data_inicio')->nullable()->comment('Primeiro dia de realização do evento');
            $table->dateTime('eve_data_fim')->nullable()->comment('Último dia de realização do evento');

            $table->string('eve_local')->nullable()->comment('Local do evento');

            $table->string('eve_banner')->nullable()->comment('Caminho onde armazena o banner desse evento');
            $table->string('eve_fundo')->nullable()->comment('Caminho onde armazena o fundo desse evento (não está em uso)');

            $table->json('eve_settings')->nullable()->comment('JSON do evento');

            // padrões Laravel (não renomear)
            $table->timestamps(); // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_eventos');
    }
};
