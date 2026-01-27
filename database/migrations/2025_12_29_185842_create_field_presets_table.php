<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_ficha_campos', function (Blueprint $table) {
            $table->id('ficg_id')->comment('Id do campo/preset (catálogo)');

            $table->string('ficg_group')->default('Geral')
                ->comment('Grupo do campo (ex: Dados pessoais, Empresa, Endereço)');

            $table->string('fic_nome')->unique()
                ->comment('Chave única do campo (ex: full_name, email, zip)');

            $table->string('fic_label')
                ->comment('Label exibido ao usuário (ex: Nome completo)');

            $table->string('fic_tipo')->default('text')
                ->comment('Tipo do campo (text, email, select, textarea, cpf, cnpj, cep, etc.)');

            $table->json('fic_opcoes')->nullable()
                ->comment('Opções do campo em JSON (para select/radio/checkbox). Ex: ["A","B"]');

            $table->string('fic_placeholder')->nullable()
                ->comment('Placeholder do input');

            $table->text('fic_help_text')->nullable()
                ->comment('Texto de ajuda exibido abaixo do campo');

            $table->boolean('fic_obrigatorio')->default(false)
                ->comment('Obrigatório (1) / Opcional (0)');

            // padrões Laravel (não renomear)
            $table->timestamps(); // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_ficha_campos');
    }
};
