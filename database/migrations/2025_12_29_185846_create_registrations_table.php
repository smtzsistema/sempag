<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_inscricao', function (Blueprint $table) {
            $table->id('ins_id')->comment('Id da inscrição');

            // FKs (explícitas por causa das PKs customizadas)
            $table->unsignedBigInteger('eve_id')->index()->comment('Id do evento (tbl_eventos.eve_id)');
            $table->foreign('eve_id')
                ->references('eve_id')->on('tbl_eventos')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('cat_id')->index()->comment('Id da categoria (tbl_categoria.cat_id)');
            $table->foreign('cat_id')
                ->references('cat_id')->on('tbl_categoria')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('form_id')->index()->comment('Id do formulário usado (tbl_formularios.form_id)');
            $table->foreign('form_id')
                ->references('form_id')->on('tbl_formularios')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('usu_id')->nullable()->index()->comment('Id do usuário vinculado (tbl_usuarios.usu_id), quando existir');
            $table->foreign('usu_id')
                ->references('usu_id')->on('tbl_usuarios')
                ->nullOnDelete();


            // Dados principais (o que fica fixo no seu preset)
            $table->string('ins_nome')->nullable()->comment('Nome');
            $table->string('ins_sobrenome')->nullable()->comment('Sobrenome');
            $table->string('ins_nomecracha')->nullable()->comment('Nome para o crachá');
            $table->string('ins_email')->nullable()->comment('Email do participante');
            $table->string('ins_senha')->nullable()->comment('Senha/hash para acesso do participante (quando aplicável)');
            $table->string('ins_cpf')->nullable()->comment('CPF do participante');
            $table->string('ins_cnpj', 20)->nullable()->comment('CNPJ');
            $table->string('ins_tel_celular', 30)->nullable()->comment('Telefone/celular do participante');
            $table->string('ins_tel_comercial', 30)->nullable()->comment('Telefone (quando diferente do celular)');
            $table->string('ins_instituicao')->nullable()->comment('Empresa/Instituição');
            $table->string('ins_siglainstituicao')->nullable()->comment('Empresa para credencial');
            $table->string('ins_cargo')->nullable()->comment('Cargo/Função');
            $table->string('ins_cargo_cred')->nullable()->comment('Cargo/Função para credencial');
            $table->string('ins_observacao')->nullable()->comment('Observações gerais');

            // Endereçamento
            $table->string('ins_cep')->nullable()->comment('CEP');
            $table->string('ins_endereco')->nullable()->comment('Endereço (rua/avenida/logradouro)');
            $table->string('ins_numero')->nullable()->comment('Número');
            $table->string('ins_complemento')->nullable()->comment('Complemento');
            $table->string('ins_bairro')->nullable()->comment('Bairro');
            $table->string('ins_cidade')->nullable()->comment('Cidade');
            $table->string('ins_estado')->nullable()->comment('Estado/UF');
            $table->string('ins_pais')->nullable()->comment('País');

            // Adicional (1 até 30)
            for ($i = 1; $i <= 30; $i++) {
                $table->text("ins_adicional{$i}")->nullable()->comment("Campo extra {$i}");
            }

            // Snapshot/armazenamento extra
            $table->json('ins_dados')->nullable()->comment('Dados adicionais em JSON (snapshot/dados dinâmicos quando aplicável)');

            $table->string('ins_token')->unique()->comment('Token único da inscrição (usado em links/QR)');
            $table->string('ins_aprovado')->default('E')->index()
                ->comment("Status da inscrição (ex: S=aprovada, R=reprovada, E=em análise, N=excluída lógica)");

            // Aprovação/Reprovação
            $table->timestamp('ins_aprovado_data')->nullable()->comment('Data/hora da decisão de aprovação/reprovação');
            $table->text('ins_motivo')->nullable()->comment('Motivo da reprovação/observação interna');

            // Acesso do participante + carta
            $table->string('ins_confirmacao_assunto')->nullable()->comment('Assunto do e-mail de confirmação gerado');
            $table->longText('ins_confirmacao_html')->nullable()->comment('HTML do e-mail de confirmação armazenado');

            // padrões Laravel (não renomear)
            $table->timestamps(); // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_inscricao');
    }
};
