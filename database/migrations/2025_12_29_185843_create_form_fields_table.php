<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_ficha', function (Blueprint $table) {
            $table->id('fic_id')->comment('Id do campo da ficha');

            $table->unsignedBigInteger('form_id')->index()->comment('Id do formulário (tbl_formularios.form_id)');
            $table->foreign('form_id')
                ->references('form_id')->on('tbl_formularios')
                ->cascadeOnDelete();

            // Identificadores do campo
            $table->string('fic_nome')->comment('Chave técnica única do campo dentro do formulário (ex: ins_cpf, ins_instituicao, ins_cargo)');
            $table->string('fic_label')->comment('Texto exibido ao usuário (label)');
            $table->string('fic_tipo')->comment('Tipo do campo (text, email, select, textarea, cpf, cnpj, cep, etc.)');

            // Comportamento
            $table->boolean('fic_obrigatorio')->default(false)->comment('Obrigatório (1) / Opcional (0)');
            $table->integer('fic_ordem')->default(0)->comment('Ordem de exibição do campo (menor aparece primeiro)');

            // Configurações avançadas
            $table->json('fic_opcoes')->nullable()->comment('Opções do campo (JSON). Ex: lista para select/radio');
            $table->text('fic_validacoes')->nullable()->comment('Regras/validadores adicionais (texto/JSON conforme padrão do sistema)');
            $table->string('fic_placeholder')->nullable()->comment('Placeholder exibido no input');
            $table->text('fic_help_text')->nullable()->comment('Texto de ajuda abaixo do campo');
            $table->json('fic_visible_if')->nullable()->comment('Regras de visibilidade condicional (JSON)');

            $table->char('fic_edita', 1)->default('N')->comment('Permite edição pelo usuário: S/N');

            $table->unsignedBigInteger('ficg_id')->nullable()->index()->comment('Id do campo/preset (tbl_ficha_campos.ficg_id)');
            $table->foreign('ficg_id')
                ->references('ficg_id')->on('tbl_ficha_campos')
                ->nullOnDelete();

            // padrões Laravel (não renomear)
            $table->timestamps(); // created_at / updated_at
            $table->softDeletes(); //deleted_at

            // Garantir unicidade da chave técnica por formulário
            $table->unique(['form_id', 'fic_nome'], 'uniq_ficha_form_nome'); // corrigido: unicidade deve ser da chave técnica (fic_nome)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_ficha');
    }
};
