<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_usuarios', function (Blueprint $table) {
            $table->id('usu_id')->comment('Id do usuario');
            $table->string('usu_nome')->comment('Nome do usuario');
            $table->string('usu_email')->unique()->comment('Email do usuario');
            $table->timestamp('usu_email_verified_at')->nullable()->comment('Não está em uso');
            $table->string('usu_password')->comment('Senha do usuario criptografada');

            // padrões Laravel (não renomear)
            $table->rememberToken(); // remember_token
            $table->timestamps();    // created_at / updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // PK própria pra permitir histórico
            $table->bigIncrements('prt_id')->comment('Id do reset (historico)');

            // campos de escopo (sem FK aqui ainda)
            $table->unsignedBigInteger('org_id')->nullable()->comment('Id da organizadora (sem FK ainda)');
            $table->unsignedBigInteger('eve_id')->nullable()->comment('Id do evento (sem FK ainda)');

            $table->string('email')->comment('Email ao qual será feito o reset');
            $table->text('token')->comment('Token/querystring do reset (expires, rt, signature, etc)');
            $table->timestamp('created_at')->nullable()->comment('Data do reset');

            // auditoria
            $table->timestamp('clicked_at')->nullable()->comment('Quando abriu o link');
            $table->timestamp('used_at')->nullable()->comment('Quando salvou a nova senha');
            $table->string('ip', 45)->nullable()->comment('IP do acesso');
            $table->text('user_agent')->nullable()->comment('User-Agent do navegador');

            // índices pra consulta ficar esperta
            $table->index(['email', 'created_at'], 'prt_email_created_idx');
            $table->index('org_id', 'prt_org_idx');
            $table->index('eve_id', 'prt_eve_idx');
        });

        Schema::create('tbl_sessions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('Id da sessão');
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('Id do usuario');
            $table->string('ip_address', 45)->nullable()->comment('IP do Acesso');
            $table->text('user_agent')->nullable()->comment('Navegador utilizado');
            $table->longText('payload')->comment('Dados da sessão (serializado/base64 pelo Laravel)');
            $table->integer('last_activity')->index()->comment('Última atividade (timestamp Unix)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('tbl_usuarios');
    }
};
