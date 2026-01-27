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
            $table->string('email')->primary()->comment('Email ao qual será feito o reset');
            $table->string('token')->comment('Token do reset');
            $table->timestamp('created_at')->nullable()->comment('Data do reset');
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
